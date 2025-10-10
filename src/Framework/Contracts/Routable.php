<?php

namespace Cydran\Framework\Contracts;

interface Routable {
    public function getRoute(): string;
}
