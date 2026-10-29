<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\Tests\Unit\DependencyInjection\Compiler;

use Elcuro\SuluSlugReplacersBundle\DependencyInjection\Compiler\SlugReplacersPass;
use Elcuro\SuluSlugReplacersBundle\DependencyInjection\SuluSlugReplacersExtension;
use Elcuro\SuluSlugReplacersBundle\Tests\Fixtures\PathCleanupWithDefaultReplacers;
use Elcuro\SuluSlugReplacersBundle\Tests\Fixtures\PathCleanupWithInjectedReplacers;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class SlugReplacersPassTest extends TestCase
{
    private const CONFIG = [
        'replacers' => [
            'default' => ['ä' => 'a', 'Ä' => 'A'],
            'sk' => ['&' => 'a'],
        ],
    ];

    public function testOverridesInjectedReplacersOfSulu2(): void
    {
        $container = $this->createContainer(self::CONFIG);
        $container->setDefinition('sulu.content.path_cleaner', (new Definition(PathCleanupWithInjectedReplacers::class))
            ->setPublic(true)
            ->setArguments([
                '$replacers' => [
                    'default' => ['Ä' => 'Ae', 'ä' => 'ae', 'ö' => 'oe'],
                    'de' => ['&' => 'und'],
                ],
                '$slugger' => null,
            ]));

        $container->compile();

        /** @var PathCleanupWithInjectedReplacers $service */
        $service = $container->get('sulu.content.path_cleaner');

        self::assertSame([
            'default' => ['Ä' => 'A', 'ä' => 'a', 'ö' => 'oe'],
            'de' => ['&' => 'und'],
            'sk' => ['&' => 'a'],
        ], $service->replacers);
    }

    public function testOverridesConstructorDefaultReplacersOfSulu3(): void
    {
        $container = $this->createContainer(self::CONFIG);
        $container->register('slugger', \stdClass::class);
        $container->setDefinition('sulu_route.path_cleanup', (new Definition(PathCleanupWithDefaultReplacers::class))
            ->setPublic(true)
            ->setArguments([new Reference('slugger')]));

        $container->compile();

        /** @var PathCleanupWithDefaultReplacers $service */
        $service = $container->get('sulu_route.path_cleanup');

        self::assertSame([
            'default' => ['Ä' => 'A', 'ä' => 'a', 'ö' => 'oe'],
            'de' => ['&' => 'und'],
            'sk' => ['&' => 'a'],
        ], $service->replacers);
        self::assertInstanceOf(\stdClass::class, $service->slugger);
    }

    public function testResolvesParameterizedClassName(): void
    {
        $container = $this->createContainer(self::CONFIG);
        $container->setParameter('path_cleanup.class', PathCleanupWithDefaultReplacers::class);
        $container->register('slugger', \stdClass::class);
        $container->setDefinition('sulu_route.path_cleanup', (new Definition('%path_cleanup.class%'))
            ->setPublic(true)
            ->setArguments([new Reference('slugger')]));

        $container->compile();

        /** @var PathCleanupWithDefaultReplacers $service */
        $service = $container->get('sulu_route.path_cleanup');

        self::assertSame('a', $service->replacers['default']['ä']);
    }

    public function testLeavesServiceUntouchedWithoutConfiguredReplacers(): void
    {
        $container = $this->createContainer([]);
        $container->register('slugger', \stdClass::class);
        $container->setDefinition('sulu_route.path_cleanup', (new Definition(PathCleanupWithDefaultReplacers::class))
            ->setPublic(true)
            ->setArguments([new Reference('slugger')]));

        $container->compile();

        /** @var PathCleanupWithDefaultReplacers $service */
        $service = $container->get('sulu_route.path_cleanup');

        self::assertSame(PathCleanupWithDefaultReplacers::DEFAULT_REPLACERS, $service->replacers);
    }

    public function testDoesNothingWhenNoSuluServiceIsDefined(): void
    {
        $container = $this->createContainer(self::CONFIG);

        $container->compile();

        self::assertFalse($container->hasDefinition('sulu.content.path_cleaner'));
        self::assertFalse($container->hasDefinition('sulu_route.path_cleanup'));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->registerExtension(new SuluSlugReplacersExtension());
        $container->loadFromExtension('sulu_slug_replacers', $config);
        $container->addCompilerPass(new SlugReplacersPass());

        return $container;
    }
}
