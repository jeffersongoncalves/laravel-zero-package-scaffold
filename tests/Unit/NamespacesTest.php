<?php

use JeffersonGoncalves\LaravelZero\PackageScaffold\Namespaces;

function writeMap(mixed $contents): string
{
    $path = sys_get_temp_dir().'/package-scaffold-'.uniqid().'.json';
    file_put_contents($path, is_string($contents) ? $contents : json_encode($contents));
    putenv('PACKAGE_NAMESPACES_FILE='.$path);

    return $path;
}

afterEach(function () {
    putenv('PACKAGE_NAMESPACES_FILE');
});

it('returns the configured casing for a listed slug', function () {
    writeMap(['jeffersongoncalves' => 'JeffersonGoncalves', 'posthog' => 'PostHog']);

    expect(Namespaces::segment('jeffersongoncalves'))->toBe('JeffersonGoncalves')
        ->and(Namespaces::segment('posthog'))->toBe('PostHog');
});

it('matches the slug case-insensitively', function () {
    writeMap(['JeffersonGoncalves' => 'JeffersonGoncalves']);

    expect(Namespaces::segment('jeffersongoncalves'))->toBe('JeffersonGoncalves');
});

it('falls back to studly for an unlisted slug', function () {
    writeMap(['posthog' => 'PostHog']);

    expect(Namespaces::segment('cep-field'))->toBe('CepField')
        ->and(Namespaces::segment('acme'))->toBe('Acme');
});

it('falls back to studly when the file is absent', function () {
    putenv('PACKAGE_NAMESPACES_FILE='.sys_get_temp_dir().'/package-scaffold-missing-'.uniqid().'.json');

    expect(Namespaces::segment('jeffersongoncalves'))->toBe('Jeffersongoncalves');
});

it('treats a malformed or non-object file as an empty map', function () {
    writeMap('{not json');

    expect(Namespaces::segment('posthog'))->toBe('Posthog');

    writeMap(['a list', 'of values']);

    expect(Namespaces::segment('posthog'))->toBe('Posthog');
});

it('ignores entries that are not string to string', function () {
    writeMap(['posthog' => ['nested'], 'cep' => 42, 'ban' => 'Ban']);

    expect(Namespaces::segment('posthog'))->toBe('Posthog')
        ->and(Namespaces::segment('cep'))->toBe('Cep')
        ->and(Namespaces::segment('ban'))->toBe('Ban');
});

it('defaults to ~/.package/vendornamespace.json', function () {
    putenv('PACKAGE_NAMESPACES_FILE');

    expect(Namespaces::file())
        ->toEndWith(DIRECTORY_SEPARATOR.'.package'.DIRECTORY_SEPARATOR.'vendornamespace.json')
        ->and(Namespaces::file())->not->toStartWith('.package');
});
