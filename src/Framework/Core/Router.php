<?php

namespace Cydran\Framework\Core;

use Cydran\Framework\Core\App;
use Twig\Environment;
use Twig\TwigFunction;

final class Router {
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    public function resolve(): void {
        $slug = get_post_field('post_name', get_queried_object_id());

        $pageContext = "Cydran\\Pages\\" . ucfirst($slug) . "\\" . ucfirst($slug) . "PageContext";
        $templatePath = "Pages/$slug/UI/{$slug}.ui.twig";

        if (!class_exists($pageContext)) {
            return;
        }

        $context = App::get($pageContext);
        $this->twig->addFunction(new TwigFunction('wp_head', wp_head(...)));
        $this->twig->addFunction(new TwigFunction('get_header', get_header(...)));
        $this->twig->addFunction(new TwigFunction('get_footer', get_footer(...)));
        $this->twig->addFunction(new TwigFunction('wp_footer', wp_footer(...)));

        echo $this->twig->render($templatePath, [
            'this' => $context,
        ]);
        exit;
    }
}
