# Changelog

All notable changes to `laravel-zero-package-scaffold` will be documented in this file.

## 1.0.2 - 2026-09-10

Fix: gitignore() now accepts a trackComposerLock flag so project-type consumers (laravel-zero-cli) can commit composer.lock instead of ignoring it, matching bb-cli/repos-cli convention.

## 1.0.1 - 2026-09-10

Fix: drop unused /build/ line from scaffolded .gitignore template.

## 1.0.0 - 2026-09-08

Initial release: the 12 byte-identical Scaffold templates extracted from laravel-package-cli and filament-plugin-cli, plus Namespaces reading slug casing from ~/.package/vendornamespace.json.
