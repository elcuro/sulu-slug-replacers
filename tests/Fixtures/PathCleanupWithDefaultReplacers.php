<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\Tests\Fixtures;

/**
 * Mirrors the constructor shape of Sulu 3.x "Sulu\Route\Application\ResourceLocator\PathCleanup\PathCleanup":
 * the replacers are a constructor default and the service definition never sets the argument.
 */
final class PathCleanupWithDefaultReplacers
{
    public const DEFAULT_REPLACERS = [
        'default' => ['Ä' => 'AE', 'ä' => 'ae', 'ö' => 'oe'],
        'de' => ['&' => 'und'],
    ];

    /**
     * @param array<string, array<string, string>> $replacers
     */
    public function __construct(
        public readonly object $slugger,
        public readonly array $replacers = self::DEFAULT_REPLACERS,
    ) {
    }
}
