<?php
namespace ImmediateSolutions\SupportBundle\Permissions;
use ImmediateSolutions\Support\Permissions\AbstractActionsPermissions;
use ImmediateSolutions\Support\Permissions\PermissionsException;
use ImmediateSolutions\Support\Permissions\PermissionsInterface;
use ImmediateSolutions\Support\Permissions\ProtectableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class PermissionsListener
{
	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * @param FilterControllerEvent $event
	 */
	public function onKernelController(FilterControllerEvent $event)
	{
		$controller = $event->getController();

		if (!is_array($controller)){
			return ;
		}

		$protectable = $controller[0];
		$method = $controller[1];

		if (!$protectable instanceof ProtectableInterface){
			return ;
		}

		$class = $this->getClass($protectable);

		if (!class_exists($class)) {
			throw new PermissionsException('The permissions class "' . $class . '" has not been found.');
		}

		$definition = new $class($this->container);

		if (!$definition instanceof AbstractActionsPermissions) {
			throw new PermissionsException('The permissions class "' . $class . '" must be instance of AbstractPermissions.');
		}

		/**
		 * @var PermissionsInterface $permissions
		 */
		$permissions = $this->container->get(PermissionsInterface::class);

		if (!$permissions->has($definition->getProtectors(cut_string_right($method, 'Action')))) {
			throw new AccessDeniedHttpException(ProtectableInterface::ACCESS_DENIED);
		}
	}

	/**
	 * @param ProtectableInterface $protectable
	 * @return string
	 */
	private function getClass(ProtectableInterface $protectable)
	{
		$parts = explode('\\', get_class($protectable));
		$name = array_pop($parts);

		return (implode('\\', $parts).'\Permissions\\'.cut_string_right($name, 'Controller').'Permissions');
	}
}