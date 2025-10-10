<?php

namespace Cydran\Framework\Contracts;

interface HasAssets {
    public function getCss(): array;
    public function getJs(): array;
}
