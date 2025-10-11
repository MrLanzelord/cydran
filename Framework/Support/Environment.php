<?php

namespace Cydran\Support;

class Environment {
    public function get(string $key, mixed $default = null): mixed {
        return $_ENV[$key] ?? $default;
    }

    public function isDev(): bool {
        return $this->get('ENVIRONMENT') === 'development';
    }

    public function isProd(): bool {
        return $this->get('ENVIRONMENT') === 'production';
    }

    public function isStaging(): bool {
        return $this->get('ENVIRONMENT') === 'staging';
    }
}
