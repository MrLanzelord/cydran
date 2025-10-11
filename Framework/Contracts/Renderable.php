<?php

namespace Cydran\Contracts;

interface Renderable {
    public function render(array $context = []): string;
}
