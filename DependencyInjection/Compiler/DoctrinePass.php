<?php
namespace ImmediateSolutions\SupportBundle\DependencyInjection\Compiler;
use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use ImmediateSolutions\Support\Infrastructure\Doctrine\Metadata\CompositeDriver;
use ImmediateSolutions\Support\Infrastructure\Doctrine\Metadata\PackageDriver;
use ImmediateSolutions\Support\Infrastructure\Doctrine\Metadata\SimpleDriver;
use ImmediateSolutions\SupportBundle\DependencyInjection\Configuration;
use ImmediateSolutions\SupportBundle\Infrastructure\Doctrine\Describer;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Doctrine\DBAL\Configuration as DBALConfiguration;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class DoctrinePass implements CompilerPassInterface
{
	/**
	 * You can modify the container here before it is dumped to PHP code.
	 *
	 * @param ContainerBuilder $container
	 */
	public function process(ContainerBuilder $container)
	{
		$configs = (new Processor())->processConfiguration(
			new Configuration(), $container->getExtensionConfig('support'));

		$configs = [
			'packages' => array_get($configs, 'packages', []),
			'entities' => array_get($configs, 'doctrine.entities', [])
		];

		$container->removeDefinition('doctrine.orm.default_metadata_driver');

		$definition = new Definition(CompositeDriver::class);
		$definition->setFactory(static::class.'::metadataDriverFactory')
			->addArgument(new Reference('service_container'))
			->addArgument($configs);

		$container->setDefinition('doctrine.orm.default_metadata_driver', $definition);

		$container->getDefinition('doctrine.dbal.default_connection')
			->setFactory(static::class.'::connectionFactory')
			->addArgument(new Reference('service_container'))
			->addArgument($configs);
	}

	public static function connectionFactory(array $params, DBALConfiguration $config, EventManager $eventManager, array $mappingTypes = array(), ContainerInterface $container, array $supportConfig = [])
	{
		$types = [];

		if ($container->hasParameter('doctrine.dbal.connection_factory.types')){
			$types = $container->getParameter('doctrine.dbal.connection_factory.types');
		}

		$factory = new ConnectionFactory($types);

		$connection = $factory->createConnection($params, $config, $eventManager, $mappingTypes);

		static::registerTypes($connection, $supportConfig, $container);

		return $connection;
	}

	private static function registerTypes(Connection $connection, array $config, ContainerInterface $container)
	{
		$packages = array_get($config, 'packages', []);
		$types = array_get($config, 'types', []);

		foreach ($packages as $package) {

			$path = $container->getParameter('kernel.root_dir')
				.'/../src/InfrastructureBundle/DAL/'
				. str_replace('\\', '/', $package) . '/Types';

			$typeNamespace = 'InfrastructureBundle\DAL\\' . $package . '\Types';

			if (!file_exists($path)) {
				continue;
			}

			$finder = new Finder();

			/**
			 *
			 * @var SplFileInfo[] $files
			 */
			$files = $finder->in($path)
				->files()
				->name('*.php');

			foreach ($files as $file) {
				$name = cut_string_right($file->getFilename(), '.php');

				$typeClass = $typeNamespace . '\\' . $name;

				if (! class_exists($typeClass)) {
					continue;
				}

				if (Type::hasType($typeClass)) {
					Type::overrideType($typeClass, $typeClass);
				} else {
					Type::addType($typeClass, $typeClass);
				}

				$connection->getDatabasePlatform()->registerDoctrineTypeMapping($typeClass, $typeClass);
			}
		}

		foreach ($types as $type){
			if (Type::hasType($type)) {
				Type::overrideType($type, $type);
			} else {
				Type::addType($type, $type);
			}
		}
	}

	/**
	 * @param ContainerInterface $container
	 * @param array $config
	 * @return CompositeDriver
	 */
	public static function metadataDriverFactory(ContainerInterface $container, array $config)
	{
		return new CompositeDriver([
			new PackageDriver(array_get($config, 'packages', []), new Describer($container)),
			new SimpleDriver(array_get($config, 'entities', []))
		]);
	}
}