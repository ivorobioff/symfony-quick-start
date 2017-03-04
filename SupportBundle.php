<?php
namespace ImmediateSolutions\SupportBundle;

use DoctrineExtensions\Query\Mysql\Month;
use DoctrineExtensions\Query\Mysql\Year;
use ImmediateSolutions\Support\Infrastructure\Doctrine\DefaultRepository;
use ImmediateSolutions\SupportBundle\DependencyInjection\Compiler\DoctrinePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SupportBundle extends Bundle
{
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		$container->addCompilerPass(new DoctrinePass());

		$container->loadFromExtension('doctrine', [
			'orm' => [
				'default_repository_class' => DefaultRepository::class,
				'dql' => [
					'datetime_functions' => [
						'YEAR' => Year::class,
						'MONTH' => Month::class
					]
				]
			],
		]);
	}
}
