<?php
namespace ImmediateSolutions\SupportBundle\Infrastructure\Doctrine;
use ImmediateSolutions\Support\Infrastructure\Doctrine\Metadata\DescriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class Describer implements DescriberInterface
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
	 * @param string $package
	 * @return string
	 */
	public function getEntityNamespace($package)
	{
		return 'CoreBundle\\' . $package . '\\Entities';
	}

	public function getMetadataNamespace($package)
	{
		return 'InfrastructureBundle\DAL\\' . $package . '\Metadata';
	}

	public function getEntityPath($package)
	{
		return $this->container->getParameter('kernel.root_dir').'/../src/CoreBundle/'.str_replace('\\', '/', $package).'/Entities';

	}
}