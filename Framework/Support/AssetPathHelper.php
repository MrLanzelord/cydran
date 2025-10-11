<?php

declare(strict_types=1);

namespace Cydran\Support;

/**
 * AssetPathHelper provides utilities for normalizing, validating,
 * and sanitizing asset paths used in the theme.
 */
class AssetPathHelper {
    /**
     * Normalizes a relative asset path to include the UI prefix.
     * Example: 'Shared/pico.css' → 'UI/Shared/pico.css'
     */
    public static function normalize(string $relativePath): string {
        return 'UI/' . ltrim($relativePath, '/');
    }

    /**
     * Returns the full filesystem path for a given normalized asset.
     */
    public static function fullPath(string $themePath, string $normalized): string {
        return rtrim($themePath, '/') . '/src/' . $normalized;
    }

    /**
     * Checks whether the asset exists in the filesystem.
     */
    public static function exists(string $themePath, string $relativePath): bool {
        $normalized = self::normalize($relativePath);
        return file_exists(self::fullPath($themePath, $normalized));
    }

    /**
     * Sanitizes the asset path for use as a WordPress handle.
     * Example: 'UI/Shared/pico.css' → 'asset-loader--ui-shared-pico-css'
     */
    public static function sanitize(string $path): string {
        return 'asset-loader--' . strtolower(
            str_replace(['/', '.', '\\'], '-', $path)
        );
    }
}
