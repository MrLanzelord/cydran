<?php

declare(strict_types=1);

namespace Cydran\Support;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AssetResolver {

    public function __construct(
        #[Autowire('%themePath%')]
        private string $themePath
    ) {
    }

    public function resolve(string $type, string $key): array {
        $baseDir = "$type/$key/UI";
        $candidates = [
            "$baseDir/" . strtolower($key) . '.ts',
            "$baseDir/" . strtolower($key) . '.css',
            "$baseDir/main.ts",
            "$baseDir/main.css"
        ];

        $assets = [];

        foreach ($candidates as $relativePath) {
            $fullPath = $this->themePath . '/src/' . $relativePath;
            if (file_exists($fullPath)) {
                $assets[] = $relativePath;
            }
        }

        return $assets;
    }
}
