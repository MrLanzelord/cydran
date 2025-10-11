<?php

declare(strict_types=1);

namespace Cydran\Support\Builders;

/**
 * ScriptConfig encapsulates script loading options like module/classic,
 * async, and defer, using a fluent builder pattern and reusable presets.
 */
class ScriptConfig {
    public readonly string $mode;
    public readonly bool $async;
    public readonly bool $defer;

    private function __construct(string $mode, bool $async = false, bool $defer = false) {
        $this->mode = $mode;
        $this->async = $async;
        $this->defer = $defer;
    }

    // Builder methods
    public static function module(): self {
        return new self('module');
    }

    public static function classic(): self {
        return new self('classic');
    }

    public function async(): self {
        return new self($this->mode, true, $this->defer);
    }

    public function defer(): self {
        return new self($this->mode, $this->async, true);
    }

    // Presets
    public static function legacy(): self {
        return self::classic()->async()->defer();
    }

    public static function modern(): self {
        return self::module();
    }

    public static function deferOnly(): self {
        return self::classic()->defer();
    }

    public static function asyncOnly(): self {
        return self::classic()->async();
    }
}
