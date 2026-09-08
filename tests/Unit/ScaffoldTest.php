<?php

use JeffersonGoncalves\LaravelZero\PackageScaffold\Scaffold;

it('studly-cases kebab, snake and spaced slugs', function () {
    expect(Scaffold::studly('cep-field'))->toBe('CepField')
        ->and(Scaffold::studly('cep_field'))->toBe('CepField')
        ->and(Scaffold::studly('cep field'))->toBe('CepField')
        ->and(Scaffold::studly('cep'))->toBe('Cep')
        ->and(Scaffold::studly(''))->toBe('');
});

it('stamps the author and year into the license', function () {
    $license = Scaffold::license('Jefferson Gonçalves', '2026');

    expect($license)->toContain('MIT License')
        ->and($license)->toContain('Copyright (c) 2026 Jefferson Gonçalves');
});

it('names the phpunit test suite', function () {
    expect(Scaffold::phpunitXml('Cep Test Suite'))->toContain('Cep Test Suite');
});

it('lists every branch in the workflow triggers', function () {
    $pint = Scaffold::workflowPint(['1.x', '2.x', 'main']);

    expect($pint)->toContain('1.x')
        ->and($pint)->toContain('2.x')
        ->and($pint)->toContain('main');
});

it('builds a readme with the title, package and install line', function () {
    $readme = Scaffold::readme('Cep', 'jeffersongoncalves', 'laravel-cep', 'CEP lookup', 'jeffersongoncalves/laravel-cep');

    expect($readme)->toContain('# Cep')
        ->and($readme)->toContain('CEP lookup')
        ->and($readme)->toContain('composer require jeffersongoncalves/laravel-cep');
});

it('emits the remaining templates as non-empty files', function () {
    expect(Scaffold::editorconfig())->toContain('root = true')
        ->and(Scaffold::gitattributes())->not->toBeEmpty()
        ->and(Scaffold::gitignore())->toContain('vendor')
        ->and(Scaffold::changelog())->toContain('Changelog')
        ->and(Scaffold::phpstanNeon())->toContain('level')
        ->and(Scaffold::workflowPhpstan(['main']))->toContain('main')
        ->and(Scaffold::workflowChangelog())->not->toBeEmpty();
});
