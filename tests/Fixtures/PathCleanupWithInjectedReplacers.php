<?php

declare(strict_types=1);

namespace Elcuro\SuluSlugReplacersBundle\Tests\Fixtures;

/**
 * Mirrors the constructor shape of Sulu 2.x "Sulu\Component\PHPCR\PathCleanup":
 * the replacers are injected as the named argument "$replacers" by Sulu's ReplacersCompilerPass.
 */
final class PathCleanupWithInjectedReplacers
{
    /**
     * @param array<string, array<string, string>> $replacers
     */
    public function __construct(
        public readonly array $replacers,
        public readonly ?object $slugger = null,
    ) {
    }
}
