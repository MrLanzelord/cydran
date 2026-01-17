<?php

declare(strict_types=1);

namespace App\Single\Post;

readonly class PostSingleContext
{
    public function __construct(
        public string $title = "Articulo"
    ) {}
}
