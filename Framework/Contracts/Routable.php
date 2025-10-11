<?php

namespace Cydran\Contracts;

interface Routable {
    public function getRoute(): string;
}
