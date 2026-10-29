<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

final class SuluSlugReplacersExtension extends Extension
{
    public const REPLACERS_PARAMETER = 'sulu_slug_replacers.replacers';

    public function load(array $configs, ContainerBuilder $container): void
    {
        /** @var array{replacers: array<string, array<string, string>>} $config */
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(self::REPLACERS_PARAMETER, $config['replacers']);
    }

    /**
     * @param array<mixed> $config
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return new Configuration();
    }
}
