<?php

namespace JeffersonGoncalves\LaravelZero\PackageScaffold;

/**
 * Resolves a kebab-case slug to its correctly cased namespace segment.
 *
 * `studly()` cannot see camel-case boundaries inside a single lowercase word:
 * `jeffersongoncalves` becomes `Jeffersongoncalves`, `posthog` becomes
 * `Posthog`. That knowledge is per-slug data, not logic, so it lives in a
 * config file the user owns and every generator CLI shares, rather than a
 * table compiled into each binary.
 *
 * Self-contained by design: like its sibling packages, this one declares no
 * dependency on them, so it carries its own home-dir and JSON reading.
 */
class Namespaces
{
    /**
     * `~/.package/vendornamespace.json` — a flat `slug => Casing` object:
     *
     *     {"jeffersongoncalves": "JeffersonGoncalves", "posthog": "PostHog"}
     *
     * Override the location with the PACKAGE_NAMESPACES_FILE env var.
     */
    public static function file(): string
    {
        $explicit = getenv('PACKAGE_NAMESPACES_FILE');

        if (is_string($explicit) && $explicit !== '') {
            return $explicit;
        }

        return self::homeDir().DIRECTORY_SEPARATOR.'.package'.DIRECTORY_SEPARATOR.'vendornamespace.json';
    }

    /**
     * Whole map, lowercased keys. Empty when the file is absent, unreadable or
     * malformed — none of which is worth failing a scaffold over.
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        $path = self::file();

        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);

        $map = [];

        foreach (is_array($decoded) ? $decoded : [] as $slug => $casing) {
            if (is_string($slug) && is_string($casing) && $slug !== '' && $casing !== '') {
                $map[strtolower($slug)] = $casing;
            }
        }

        return $map;
    }

    /**
     * Unlisted slugs fall back to `Scaffold::studly()`. A CLI's explicit
     * `--namespace` replaces the whole namespace and never reaches here.
     */
    public static function segment(string $slug): string
    {
        return self::all()[strtolower($slug)] ?? Scaffold::studly($slug);
    }

    /**
     * HOME (POSIX) before USERPROFILE / HOMEDRIVE+HOMEPATH (Windows), but the
     * first candidate that actually resolves to a directory wins: some Windows
     * shells export HOME as an MSYS path (/c/Users/...) that PHP cannot stat,
     * and silently reading a config file from a bogus home looks exactly like
     * an empty map. Falls back to the working directory.
     */
    private static function homeDir(): string
    {
        $drive = (string) getenv('HOMEDRIVE');
        $path = (string) getenv('HOMEPATH');

        $candidates = [
            (string) getenv('HOME'),
            (string) getenv('USERPROFILE'),
            $drive !== '' && $path !== '' ? $drive.$path : '',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && is_dir($candidate)) {
                return rtrim($candidate, '/\\');
            }
        }

        foreach ($candidates as $candidate) {
            if ($candidate !== '') {
                return rtrim($candidate, '/\\');
            }
        }

        return rtrim(getcwd() ?: '.', '/\\');
    }
}
