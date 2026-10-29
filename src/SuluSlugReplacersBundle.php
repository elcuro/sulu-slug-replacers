<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle;

use Elcuro\SuluSlugReplacersBundle\DependencyInjection\Compiler\SlugReplacersPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SuluSlugReplacersBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Negative priority: must run after Sulu's own ReplacersCompilerPass (priority 0),
        // regardless of the bundle registration order.
        $container->addCompilerPass(new SlugReplacersPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
