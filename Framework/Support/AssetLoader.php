<?php

declare(strict_types=1);

namespace Cydran\Support;

use Cydran\Contracts\HasAssets;
use Cydran\Contracts\AssetContextInterface;
use Cydran\Support\Builders\ScriptConfig;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * AssetLoader handles automatic and explicit asset registration
 * for WordPress, supporting both development (Vite) and production (manifest.json).
 */
class AssetLoader {
    private Environment $env;
    private AssetResolver $resolver;
    private string $manifestPath;
    private string $distUri;
    private string $themePath;
    private array $manifest = [];
    private array $globalAssets = [];

    /** @var array<string, ScriptConfig> */
    private array $scriptConfigs = [];

    public function __construct(
        Environment $env,
        #[Autowire('%themePath%')] string $themePath,
        AssetResolver $resolver,
    ) {
        $this->env = $env;
        $this->themePath = rtrim($themePath, '/');
        $this->resolver = $resolver;
        $this->manifestPath = $themePath . '/public/dist/.vite/manifest.json';
        $this->distUri = get_template_directory_uri() . '/public/dist/';
        $this->loadManifest();
    }

    private function loadManifest(): void {
        if (file_exists($this->manifestPath)) {
            $this->manifest = json_decode(file_get_contents($this->manifestPath), true);
        }
    }

    public function enqueue(string $type, string $key, object $context): void {
        $assets = $this->resolver->resolve($type, $key);
        $assets = array_merge($assets, $this->globalAssets);

        if ($context instanceof AssetContextInterface) {
            $assets = array_merge($assets, $context->getExtraAssets());
        }

        if ($context instanceof HasAssets) {
            $assets = array_merge($assets, $context->getCss(), $context->getJs());
        }

        $assets = array_unique($assets);

        if ($this->env->isDev()) {
            $this->enqueueDevAssets($assets);
        } else {
            $this->enqueueManifestAssets($assets);
        }
    }

    public function enqueueGlobal(string $relativePath): self {
        $normalized = AssetPathHelper::normalize($relativePath);

        if (AssetPathHelper::exists($this->themePath, $relativePath)) {
            $this->globalAssets[] = $normalized;
        }

        return $this;
    }

    public function enqueueGlobals(array $paths): self {
        foreach ($paths as $path) {
            $this->enqueueGlobal($path);
        }

        return $this;
    }

    public function enqueueScript(string $relativePath, ScriptConfig $config): self {
        $normalized = AssetPathHelper::normalize($relativePath);

        if (AssetPathHelper::exists($this->themePath, $relativePath)) {
            $this->globalAssets[] = $normalized;
            $this->scriptConfigs[$normalized] = $config;
        }

        return $this;
    }

    private function enqueueDevAssets(array $assets): void {
        $host = $this->env->get('VITE_DEV_HOST', 'http://localhost:5173');

        wp_enqueue_script_module('vite-client', $host . '/@vite/client', [], null);

        foreach ($assets as $key) {
            $handle = AssetPathHelper::sanitize($key);
            $url = $host . '/' . $key;

            if (str_ends_with($key, '.css')) {
                wp_enqueue_style($handle, $url, [], null);
            }

            if (str_ends_with($key, '.ts') || str_ends_with($key, '.js')) {
                $config = $this->scriptConfigs[$key] ?? ScriptConfig::module();

                if ($config->mode === 'module') {
                    wp_enqueue_script_module($handle, $url, [], null, true);
                } else {
                    wp_enqueue_script($handle, $url, [], null, true);

                    if ($config->async) {
                        add_filter(
                            hook_name: 'script_loader_tag',
                            callback: fn($tag, $loadedHandle) => $loadedHandle === $handle
                                ? str_replace('<script ', '<script async ', $tag) : $tag,
                            priority: 10,
                            accepted_args: 2
                        );
                    }

                    if ($config->defer) {
                        add_filter(
                            hook_name: 'script_loader_tag',
                            callback: fn($tag, $loadedHandle) => $loadedHandle === $handle
                                ? str_replace('<script ', '<script defer ', $tag) : $tag,
                            priority: 10,
                            accepted_args: 2
                        );
                    }
                }
            }
        }
    }

    private function enqueueManifestAssets(array $assets): void {
        foreach ($assets as $key) {
            if (!isset($this->manifest[$key])) {
                continue;
            }

            $entry = $this->manifest[$key];
            $handle = AssetPathHelper::sanitize($key);
            $url = $this->distUri . $entry['file'];
            $config = $this->scriptConfigs[$key] ?? ScriptConfig::module();

            if (str_ends_with($url, '.css')) {
                wp_enqueue_style($handle, $url, [], null);
            }

            if (!str_ends_with($url, '.js')) continue;

            if ($config->mode === 'module') {
                wp_enqueue_script_module($handle, $url, [], null);
            } else {
                wp_enqueue_script($handle, $url, [], null, true);

                if ($config->async) {
                    add_filter(
                        hook_name: 'script_loader_tag',
                        callback: fn($tag, $loadedHandle) => $loadedHandle === $handle
                            ? str_replace('<script ', '<script async ', $tag) : $tag,
                        priority: 10,
                        accepted_args: 2
                    );
                }

                if ($config->defer) {
                    add_filter(
                        hook_name: 'script_loader_tag',
                        callback: fn($tag, $loadedHandle) => $loadedHandle === $handle
                            ? str_replace('<script ', '<script defer ', $tag) : $tag,
                        priority: 10,
                        accepted_args: 2
                    );
                }
            }
        }
    }
}
