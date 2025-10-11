<?php

namespace App\Page\Inicio;

class InicioPageContext {
    public function __construct(
        public $title = 'Inicio',
    ) {
    }

    public function persons(): array {
        return [
            'Lanze',
            'Cydran',
            'Framework',
            'WordPress',
        ];
    }

    public function getContent(): void {
        if (have_posts()):
            while (have_posts()):
                the_post();
                the_content();
            endwhile;
        endif;
    }
}
