# Sulu Slug Replacers Bundle

Configure the character replacements Sulu applies when it generates URL slugs
(resource locators) from page titles.

Sulu ships a hard-coded replacer table that transliterates umlauts the German way
for **every** locale: `ä → ae`, `ö → oe`, `ü → ue`. For languages such as Slovak,
Czech, Finnish or Estonian the expected result is simply `ä → a`. This bundle
lets you override any of those replacements — globally or per locale — via
Symfony configuration, without patching the vendor code.

Works with **Sulu 2.5+** and **Sulu 3.x**.

## Installation

```bash
composer require elcuro/sulu-slug-replacers
```

Register the bundle if Symfony Flex did not do it for you:

```php
// config/bundles.php
return [
    // ...
    Elcuro\SuluSlugReplacersBundle\SuluSlugReplacersBundle::class => ['all' => true],
];
```

## Configuration

```yaml
# config/packages/sulu_slug_replacers.yaml
sulu_slug_replacers:
    replacers:
        # applied to every locale
        default:
            ä: a
            Ä: A
        # per-locale overrides win over "default" (same semantics as Sulu's replacers.xml)
        de:
            ä: ae
            Ä: Ae
```

Only the keys you configure are changed; every other replacement Sulu defines
stays in place. Replacements run **before** the Symfony slugger, so the value
should already be ASCII (it is slugified and lower-cased afterwards anyway).

Only newly generated slugs are affected — existing routes are not rewritten.

## How it works

A compiler pass (priority `-10`, so it runs after Sulu's own
`ReplacersCompilerPass`) merges the configured map into Sulu's path cleanup
service:

| Sulu | Service | Where the replacers come from |
|---|---|---|
| 2.x | `sulu.content.path_cleaner` | `$replacers` argument, loaded from `replacers.xml` |
| 3.x | `sulu_route.path_cleanup` | constructor default (`PathCleanupInterface::DEFAULT_REPLACERS`) |

The pass reads the current value from whichever of the two applies, overrides the
configured keys and sets the argument explicitly. No Sulu classes are referenced,
so the bundle has no hard dependency on a specific Sulu version.

## Development

```bash
composer install
composer test
composer lint   # phpstan + php-cs-fixer (dry run)
composer fix
```

## License

MIT
