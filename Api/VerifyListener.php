<?php
namespace ImmediateSolutions\SupportBundle\Api;
use ImmediateSolutions\Support\Api\Verify\Action;
use ImmediateSolutions\Support\Api\Verify\VerifiableInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use RuntimeException;
use ReflectionMethod;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class VerifyListener
{
	/**
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if (!is_array($controller)){
			 return ;
		}

		$verifiable = $controller[0];
		$method = $controller[1];
		$request = $event->getRequest();
		$arguments = $request->attributes->get('_route_params');

		if (!$arguments){
			return ;
		}

		if (!$verifiable instanceof VerifiableInterface){
			return ;
		}

		if ($verifiable->shouldVerify() === false) {
			return;
		}

		if (!method_exists($verifiable, 'verify')) {
			throw new RuntimeException('The "verify" method is missing even though the controller is verifiable.');
		}

		$verify = new ReflectionMethod($verifiable, 'verify');

		foreach ($verify->getParameters() as $index => $argument) {
			$class = $argument->getClass();

			if (!$class) {
				continue;
			}

			$class = $class->getName();

			if ($class === Action::class || is_subclass_of($class, Action::class)) {
				array_splice($arguments, $index, 0, [new $class(cut_string_right($method, 'Action'))]);
			}
		}

		$result = call_user_func_array([$verifiable, 'verify'], $arguments);

		if (!$result) {
			throw new NotFoundHttpException(VerifiableInterface::NOT_FOUND);
		}
	}
}