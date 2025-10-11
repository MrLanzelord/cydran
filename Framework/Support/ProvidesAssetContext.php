<?php

declare(strict_types=1);

namespace Cydran\Support;

use Cydran\Contracts\AssetContextInterface;

/**
 * Trait to simplify implementation of AssetContextInterface
 * for contexts that want to declare optional assets.
 */
trait ProvidesAssetContext {
    /**
     * Override this method to declare additional assets
     * like global styles or external libraries.
     */
    public function getExtraAssets(): array {
        return [];
    }

    /**
     * Override this method to enable global asset inclusion.
     */
    public function includeGlobalAssets(): bool {
        return false;
    }
}
