<?php

namespace App\Page\Contacto;

use Cydran\Contracts\HasAssets;
use Cydran\Contracts\Renderable;

class ContactoPageContext implements Renderable, HasAssets {
    public function render(array $context = []): string {
        ob_start();
        include get_template_directory() . '/src/Pages/Contacto/UI/contacto.ui.twig';
        return ob_get_clean();
    }

    public function getCss(): array {
        return ['contacto.css'];
    }

    public function getJs(): array {
        return ['contacto.ts'];
    }
}
