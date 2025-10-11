<?php

declare(strict_types=1);

namespace Cydran\Contracts;

/**
 * Interface for components or contexts that explicitly declare
 * their CSS and JS assets to be enqueued, regardless of folder convention.
 *
 * This is useful for:
 * - External libraries not located in the theme's `src/` folder
 * - Shared assets reused across multiple components
 * - Conditional or dynamic asset inclusion
 *
 * Example implementation:
 * public function getCss(): array {
 *     return ['assets/css/swiper.css'];
 * }
 *
 * public function getJs(): array {
 *     return ['assets/js/swiper.js'];
 * }
 */
interface HasAssets {
    /**
     * Returns a list of relative paths to CSS files to be enqueued.
     * These paths should be relative to the theme's asset root.
     */
    public function getCss(): array;

    /**
     * Returns a list of relative paths to JS files to be enqueued.
     * These paths should be relative to the theme's asset root.
     */
    public function getJs(): array;
}
