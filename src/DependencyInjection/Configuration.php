<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sulu_slug_replacers');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('replacers')
                    ->info('Character replacements per locale, applied before Sulu slugifies a path. "default" applies to every locale.')
                    ->example(['default' => ['ä' => 'a', 'Ä' => 'A'], 'de' => ['ä' => 'ae']])
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('locale')
                    ->arrayPrototype()
                        ->normalizeKeys(false)
                        ->useAttributeAsKey('from')
                        ->scalarPrototype()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
