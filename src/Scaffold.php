<?php

namespace JeffersonGoncalves\LaravelZero\PackageScaffold;

/**
 * File templates shared by package generator CLIs (laravel-package-cli,
 * filament-plugin-cli). Every method here was byte-identical in both binaries
 * before extraction; anything that differs between them (Filament version
 * matrices, per-branch CI, composer.json shapes) stays in the CLI that owns it.
 */
class Scaffold
{
    public static function studly(string $value): string
    {
        return str_replace(['-', '_', ' '], '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    public static function license(string $author, string $year): string
    {
        return <<<TXT
        MIT License

        Copyright (c) {$year} {$author}

        Permission is hereby granted, free of charge, to any person obtaining a copy
        of this software and associated documentation files (the "Software"), to deal
        in the Software without restriction, including without limitation the rights
        to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
        copies of the Software, and to permit persons to whom the Software is
        furnished to do so, subject to the following conditions:

        The above copyright notice and this permission notice shall be included in all
        copies or substantial portions of the Software.

        THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
        IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
        FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
        AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
        LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
        OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
        SOFTWARE.

        TXT;
    }

    public static function editorconfig(): string
    {
        return <<<'TXT'
        root = true

        [*]
        charset = utf-8
        end_of_line = lf
        insert_final_newline = true
        indent_style = space
        indent_size = 4
        trim_trailing_whitespace = true

        [*.md]
        trim_trailing_whitespace = false

        TXT;
    }

    public static function gitattributes(): string
    {
        return <<<'TXT'
        * text=auto eol=lf

        /.github export-ignore
        /art export-ignore
        /tests export-ignore
        /.editorconfig export-ignore
        /.gitattributes export-ignore
        /.gitignore export-ignore
        /CHANGELOG.md export-ignore
        /phpstan.neon.dist export-ignore
        /phpunit.xml.dist export-ignore

        TXT;
    }

    public static function gitignore(): string
    {
        return <<<'TXT'
        /vendor/
        /node_modules/
        /.phpunit.cache/
        /build/
        composer.lock
        .phpunit.result.cache
        .DS_Store
        .idea/
        .vscode/
        .env

        TXT;
    }

    public static function changelog(): string
    {
        return <<<'TXT'
        # Changelog

        All notable changes to this project will be documented in this file.

        ## [Unreleased]

        TXT;
    }

    public static function phpstanNeon(): string
    {
        return <<<'TXT'
        parameters:
            level: 5
            paths:
                - src

        TXT;
    }

    public static function phpunitXml(string $suiteName): string
    {
        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd" backupGlobals="false" bootstrap="vendor/autoload.php" colors="true" processIsolation="false" stopOnFailure="false" executionOrder="random" failOnWarning="true" failOnRisky="true" failOnEmptyTestSuite="true" beStrictAboutOutputDuringTests="true" cacheDirectory=".phpunit.cache" backupStaticProperties="false">
          <testsuites>
            <testsuite name="{$suiteName}">
              <directory>tests</directory>
            </testsuite>
          </testsuites>
          <source>
            <include>
              <directory suffix=".php">./src</directory>
            </include>
          </source>
        </phpunit>

        XML;
    }

    public static function workflowPint(array $branches): string
    {
        $branchList = '['.implode(', ', $branches).']';

        return <<<YAML
        name: Fix PHP code styling

        on:
          push:
            branches: {$branchList}

        permissions:
          contents: write

        jobs:
          style:
            runs-on: ubuntu-latest
            steps:
              - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262 # v4
                with:
                  ref: \${{ github.head_ref }}
              - name: Setup PHP
                uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45 # v2
                with:
                  php-version: 8.4
                  coverage: none
              - name: Install dependencies
                run: composer install --no-interaction
              - name: Run Pint
                run: vendor/bin/pint
              - name: Commit changes
                uses: stefanzweifel/git-auto-commit-action@4a55954c782fc1ea30b9056cd3e7a2b40ca8887d # v7
                with:
                  commit_message: "style: fix code styling"

        YAML;
    }

    public static function workflowPhpstan(array $branches): string
    {
        $branchList = '['.implode(', ', $branches).']';

        return <<<YAML
        name: PHPStan

        on:
          push:
            branches: {$branchList}
          pull_request:
            branches: {$branchList}

        jobs:
          analyse:
            runs-on: ubuntu-latest
            steps:
              - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262 # v4
              - name: Setup PHP
                uses: shivammathur/setup-php@b604ade2a87db23f8871b7182e69ec5e75effb45 # v2
                with:
                  php-version: 8.4
                  coverage: none
              - name: Install dependencies
                run: composer install --no-interaction
              - name: Run PHPStan
                run: vendor/bin/phpstan analyse

        YAML;
    }

    public static function workflowChangelog(): string
    {
        return <<<'YAML'
        name: Update Changelog

        on:
          release:
            types: [created]

        permissions:
          contents: write

        jobs:
          update:
            runs-on: ubuntu-latest
            steps:
              - uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262 # v4
                with:
                  ref: main
                  fetch-depth: 0
              - name: Update Changelog
                uses: stefanzweifel/changelog-updater-action@a938690fad7edf25368f37e43a1ed1b34303eb36 # v1
                with:
                  latest-version: ${{ github.event.release.tag_name }}
                  release-notes: ${{ github.event.release.body }}
              - name: Commit changes
                uses: stefanzweifel/git-auto-commit-action@4a55954c782fc1ea30b9056cd3e7a2b40ca8887d # v7
                with:
                  commit_message: "docs: update CHANGELOG for ${{ github.event.release.tag_name }}"

        YAML;
    }

    public static function readme(string $title, string $vendor, string $package, string $description, string $installRequire): string
    {
        return <<<MD
        <div class="filament-hidden">

        <!-- banner: art/{$vendor}-{$package}.png (generate via portfolio-banner skill) -->

        </div>

        # {$title}

        {$description}

        ## Installation

        You can install the package via composer:

        ```bash
        composer require {$installRequire}
        ```

        ## Usage

        ```php
        // TODO
        ```

        ## Testing

        ```bash
        composer test
        ```

        ## Changelog

        Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

        ## Contributing

        Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

        ## Security

        If you discover any security related issues, please email the author instead of using the issue tracker.

        ## Credits

        - [{$vendor}](https://github.com/{$vendor})
        - [All Contributors](../../contributors)

        ## License

        The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

        MD;
    }
}
