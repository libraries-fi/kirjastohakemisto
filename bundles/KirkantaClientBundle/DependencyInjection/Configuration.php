<?php

namespace KirjastotFi\KirkantaClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder;
        $root = $builder->root('kirjastot_fi_api');
        $root->children()
            ->scalarNode('url')->defaultValue('https://api.kirjastot.fi')->end()
            ->scalarNode('agent')->defaultValue('Kirjastohakemisto/1.0')->end()
            ->end();

        return $builder;
    }
}
