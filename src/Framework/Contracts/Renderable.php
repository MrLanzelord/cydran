<?php

namespace Cydran\Framework\Contracts;

interface Renderable {
    public function render(array $context = []): string;
}
