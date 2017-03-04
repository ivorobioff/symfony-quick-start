<?php
namespace ImmediateSolutions\SupportBundle\Infrastructure;
use ImmediateSolutions\Support\Core\Interfaces\ContainerInterface;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionFunction;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class Container implements ContainerInterface
{
	/**
	 * @var \Symfony\Component\DependencyInjection\ContainerInterface
	 */
	private $symfony;

	public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
	{
		$this->symfony = $container;
	}

	/**
	 * @param string $abstract
	 * @return object
	 */
	public function get($abstract)
	{
		return $this->symfony->get($abstract);
	}

	/**
	 * @param string $abstract
	 * @return bool
	 */
	public function has($abstract)
	{
		return $this->symfony->has($abstract);
	}

	/**
	 * @param callable $callback
	 * @param array $arguments
	 * @return mixed
	 */
	public function call($callback, array $arguments = [])
	{
		$arguments = $this->injectCallbackDependencies($callback, $arguments);

		return call_user_func_array($callback, $arguments);
	}

	/**
	 * @param callable $callback
	 * @param array $arguments
	 * @return array
	 */
	private function injectCallbackDependencies($callback, $arguments)
	{
		$reflection = $this->findCallbackReflection($callback);

		$dependencies = [];

		foreach ($reflection->getParameters() as $parameter){

			if (array_key_exists($parameter->name, $arguments)) {
				$dependencies[] = $arguments[$parameter->name];
				unset($arguments[$parameter->name]);
			} elseif ($parameter->getClass()) {

				$dependencies[] = $this->symfony->get($parameter->getClass()->name);

			} elseif ($parameter->isDefaultValueAvailable()) {
				$dependencies[] = $parameter->getDefaultValue();
			}
		}

		return array_merge($dependencies, $arguments);
	}

	/**
	 * @param callable $callback
	 * @return ReflectionFunctionAbstract
	 */
	private function findCallbackReflection($callback)
	{
		if (is_string($callback) && strpos($callback, '::') !== false) {
			$callback = explode('::', $callback);
		}

		if (is_array($callback)){
			return new ReflectionMethod($callback[0], $callback[1]);
		}

		return new ReflectionFunction($callback);
	}
}