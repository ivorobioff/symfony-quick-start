<?php
namespace ImmediateSolutions\SupportBundle\DependencyInjection;

use Doctrine\ORM\EntityManagerInterface;
use ImmediateSolutions\Support\Core\Interfaces\PasswordEncryptorInterface;
use ImmediateSolutions\Support\Core\Interfaces\TokenGeneratorInterface;
use ImmediateSolutions\Support\Infrastructure\PasswordEncryptor;
use ImmediateSolutions\Support\Infrastructure\TokenGenerator;
use ImmediateSolutions\SupportBundle\Api\ExceptionListener;
use ImmediateSolutions\SupportBundle\Api\JsonResponseFactory;
use ImmediateSolutions\SupportBundle\Api\ResponseFactoryInterface;
use ImmediateSolutions\SupportBundle\Api\VerifyListener;
use ImmediateSolutions\SupportBundle\Infrastructure\Container;
use ImmediateSolutions\Support\Permissions\Permissions;
use ImmediateSolutions\Support\Permissions\PermissionsInterface;
use ImmediateSolutions\SupportBundle\Permissions\PermissionsListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class SupportExtension extends Extension
{
	/**
	 * @param array $configs
	 * @param ContainerBuilder $container
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configs = $this->processConfiguration(new Configuration(), $configs);

		$container->register(ResponseFactoryInterface::class, JsonResponseFactory::class);

		$container
			->register(ExceptionListener::class, ExceptionListener::class)
			->setAutowired(true)
			->addTag('kernel.event_listener', ['event' => 'kernel.exception']);

		$container
			->register(VerifyListener::class, VerifyListener::class)
			->setAutowired(true)
			->addTag('kernel.event_listener', ['event' => 'kernel.controller']);

		$container
			->register(PermissionsListener::class, PermissionsListener::class)
			->setAutowired(true)
			->addTag('kernel.event_listener', ['event' => 'kernel.controller']);



		$definition = new Definition(PermissionsInterface::class);

		$definition->setFactory(static::class.'::permissionsFactory')
			->addArgument(new Reference('service_container'))
			->addArgument($configs);

		$container->setDefinition(PermissionsInterface::class, $definition);

		$container->register(ContainerInterface::class, Container::class)
			->addArgument(new Reference('service_container'));


		$container->register(PasswordEncryptorInterface::class, PasswordEncryptor::class);
		$container->register(TokenGeneratorInterface::class, TokenGenerator::class);
		$container->setAlias(EntityManagerInterface::class, 'doctrine.orm.entity_manager');

		$definition = new Definition(Request::class);

		$definition->setFactory(static::class.'::requestFactory')
			->addArgument(new Reference('service_container'))
			->setShared(false);

		$container->setDefinition(Request::class, $definition);
	}

	/**
	 * @param ContainerInterface $container
	 * @return Request
	 */
	public static function requestFactory(ContainerInterface $container)
	{
		return $container->get('request_stack')->getCurrentRequest();
	}

	/**
	 * @param ContainerInterface $container
	 * @param array $config
	 * @return PermissionsInterface
	 */
	public static function permissionsFactory(ContainerInterface $container, array $config)
	{
		$permissions = new Permissions(function($class) use ($container){
			return new $class($container);
		});

		$permissions->globals(array_get($config, 'permissions.protectors', []));

		return $permissions;
	}
}