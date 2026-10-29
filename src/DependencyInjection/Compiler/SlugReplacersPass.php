<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\DependencyInjection\Compiler;

use Elcuro\SuluSlugReplacersBundle\DependencyInjection\SuluSlugReplacersExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Merges the configured replacers into Sulu's path cleanup service.
 *
 * Sulu 2.x injects the replacers from replacers.xml as the "$replacers" argument of
 * "sulu.content.path_cleaner"; Sulu 3.x does not set the argument at all and relies on the
 * constructor default of "sulu_route.path_cleanup". Both cases are handled by reading the
 * current value (argument or constructor default) and overriding only the configured keys.
 */
final class SlugReplacersPass implements CompilerPassInterface
{
    private const SERVICE_IDS = [
        'sulu.content.path_cleaner', // Sulu 2.x
        'sulu_route.path_cleanup', // Sulu 3.x
    ];

    private const ARGUMENT_NAME = 'replacers';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasParameter(SuluSlugReplacersExtension::REPLACERS_PARAMETER)) {
            return;
        }

        /** @var array<string, array<string, string>> $configured */
        $configured = $container->getParameter(SuluSlugReplacersExtension::REPLACERS_PARAMETER);
        if ([] === $configured) {
            return;
        }

        foreach (self::SERVICE_IDS as $serviceId) {
            if (!$container->hasDefinition($serviceId)) {
                continue;
            }

            $definition = $container->getDefinition($serviceId);
            $replacers = $this->currentReplacers($container, $definition);

            foreach ($configured as $locale => $map) {
                $replacers[$locale] = \array_merge($replacers[$locale] ?? [], $map);
            }

            $definition->setArgument('$' . self::ARGUMENT_NAME, $replacers);
        }
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function currentReplacers(ContainerBuilder $container, Definition $definition): array
    {
        $arguments = $definition->getArguments();
        if (isset($arguments['$' . self::ARGUMENT_NAME])) {
            /** @var array<string, array<string, string>> $replacers */
            $replacers = $arguments['$' . self::ARGUMENT_NAME];

            return $replacers;
        }

        $class = $definition->getClass();
        if (null === $class) {
            return [];
        }

        /** @var class-string $class */
        $class = $container->getParameterBag()->resolveValue($class);
        $constructor = (new \ReflectionClass($class))->getConstructor();
        if (null === $constructor) {
            return [];
        }

        foreach ($constructor->getParameters() as $parameter) {
            if (self::ARGUMENT_NAME !== $parameter->getName()) {
                continue;
            }

            if (isset($arguments[$parameter->getPosition()])) {
                /** @var array<string, array<string, string>> $replacers */
                $replacers = $arguments[$parameter->getPosition()];

                return $replacers;
            }

            if ($parameter->isDefaultValueAvailable()) {
                /** @var array<string, array<string, string>> $replacers */
                $replacers = $parameter->getDefaultValue();

                return $replacers;
            }
        }

        return [];
    }
}
