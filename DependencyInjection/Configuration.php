<?php
namespace ImmediateSolutions\SupportBundle\DependencyInjection;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Igor Vorobiov<igor.vorobioff@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
	/**
	 * Generates the configuration tree builder.
	 *
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
	 */
	public function getConfigTreeBuilder()
	{
		$tree = new TreeBuilder();

		$root = $tree->root('immediate_solutions');

		$root
			->children()->arrayNode('packages')
				->prototype('scalar');

		$root
			->children()->arrayNode('doctrine')
				->children()
					->arrayNode('entities')->prototype('scalar')->end()->end()
					->arrayNode('types')->prototype('scalar');

		$root
			->children()->arrayNode('permissions')
				->children()->arrayNode('protectors')
					->prototype('scalar');

		return $tree;
	}
}