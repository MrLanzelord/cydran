<?php

declare(strict_types=1);

namespace Cydran\Contracts;

/**
 * Interface for contexts that may declare additional assets
 * beyond those automatically resolved by folder convention.
 */
interface AssetContextInterface {
    /**
     * Returns a list of relative asset paths (CSS or JS)
     * to be loaded in addition to convention-based assets.
     *
     * Example:
     * [
     *   'UI/Shared/global.css',
     *   'assets/js/swiper.js'
     * ]
     */
    public function getExtraAssets(): array;

    /**
     * Indicates whether global assets (Shared, Layouts) should be included.
     * If not implemented, defaults to false.
     */
    public function includeGlobalAssets(): bool;
}
