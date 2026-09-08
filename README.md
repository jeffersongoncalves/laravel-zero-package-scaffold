<div class="filament-hidden">

![laravel-zero-package-scaffold](https://raw.githubusercontent.com/jeffersongoncalves/laravel-zero-package-scaffold/main/art/jeffersongoncalves-laravel-zero-package-scaffold.png)

</div>

# laravel-zero-package-scaffold

The file templates and namespace-casing rules shared by package generator CLIs
(`laravel-package-cli`, `filament-plugin-cli`). Everything here was
byte-identical in both binaries before extraction — the LICENSE, `.editorconfig`,
`.gitattributes`, `.gitignore`, CHANGELOG, phpstan/phpunit configs, the three
reusable GitHub workflows and the README skeleton.

- No runtime dependencies (only `php: ^8.2`).
- Static templates: no state, no container, nothing to register.
- Namespace casing lives in user config, not in a table compiled into each CLI.

## Installation

```bash
composer require jeffersongoncalves/laravel-zero-package-scaffold
```

## Templates

Every method returns the finished file contents as a string.

| Method | File |
|--------|------|
| `Scaffold::license($author, $year)` | `LICENSE.md` |
| `Scaffold::editorconfig()` | `.editorconfig` |
| `Scaffold::gitattributes()` | `.gitattributes` |
| `Scaffold::gitignore()` | `.gitignore` |
| `Scaffold::changelog()` | `CHANGELOG.md` |
| `Scaffold::phpstanNeon()` | `phpstan.neon.dist` |
| `Scaffold::phpunitXml($suiteName)` | `phpunit.xml.dist` |
| `Scaffold::workflowPint($branches)` | `.github/workflows/pint.yml` |
| `Scaffold::workflowPhpstan($branches)` | `.github/workflows/phpstan.yml` |
| `Scaffold::workflowChangelog()` | `.github/workflows/update-changelog.yml` |
| `Scaffold::readme($title, $vendor, $package, $description, $installRequire)` | `README.md` |

`Scaffold::studly($slug)` is the plain kebab/snake/space → StudlyCase helper the
templates and the namespace resolver share.

Anything that differs between generators — Filament version matrices, per-branch
CI, the shape of the generated `composer.json` — deliberately stays in the CLI
that owns it.

## Namespace casing

`studly()` cannot see camel-case boundaries inside a single lowercase word:
`jeffersongoncalves` becomes `Jeffersongoncalves`, `posthog` becomes `Posthog`.
That is per-slug **data**, not logic, so it lives in one config file the user
owns and every generator CLI reads:

```json
{
    "jeffersongoncalves": "JeffersonGoncalves",
    "jeffersonsimaogoncalves": "JeffersonSimaoGoncalves",
    "posthog": "PostHog"
}
```

```php
use JeffersonGoncalves\LaravelZero\PackageScaffold\Namespaces;

Namespaces::file();                        // ~/.package/vendornamespace.json
Namespaces::segment('jeffersongoncalves'); // JeffersonGoncalves  (from the file)
Namespaces::segment('cep-field');          // CepField            (studly fallback)
Namespaces::all();                         // the whole map, lowercased keys
```

| Behaviour | Result |
|-----------|--------|
| Slug listed in the file | The configured casing (slug matched case-insensitively) |
| Slug not listed | `Scaffold::studly($slug)` |
| File absent, unreadable, malformed, or not an object | Empty map — never an error |
| Entry that isn't `string => string` | Ignored |

Point it somewhere else with the `PACKAGE_NAMESPACES_FILE` environment
variable — which is also how the tests avoid touching a real home directory.

Home resolution reads `HOME`, then `USERPROFILE`, then `HOMEDRIVE`+`HOMEPATH`,
and prefers the first candidate that is an existing directory: some Windows
shells export `HOME` as an MSYS path (`/c/Users/...`) that PHP cannot stat, and
reading config from a bogus home is indistinguishable from an empty map.

Like its sibling `laravel-zero-*` packages, this one declares no dependency on
them, so it carries its own home-dir and JSON reading.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please see [SECURITY](.github/SECURITY.md).

## Credits

- [Jefferson Gonçalves](https://github.com/jeffersongoncalves)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
