<?php

namespace Cydran\Core;

use Cydran\Core\App;
use Twig\Environment;
use Twig\TwigFunction;

final class Router {
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    public function resolve(): void {
        $slug = get_post_field('post_name', get_queried_object_id());
        $post_type = get_post_type();

        $pageContext = "App\\" . ucfirst($post_type) . "\\" . ucfirst($slug) . "\\" . ucfirst($slug) . "PageContext";
        $templatePath = ucfirst($post_type) . "/" . ucfirst($slug) . "/UI/{$slug}.ui.twig";

        if (!class_exists($pageContext)) {
            return;
        }

        $context = App::get($pageContext);

        add_action('wp_enqueue_scripts', function () use ($post_type, $slug, $context) {
            App::get(\Cydran\Support\AssetLoader::class)
                ->enqueueGlobal('UI/Shared/variables.css')
                ->enqueueGlobal('UI/Shared/typography.css')
                ->enqueueGlobal('Layouts/base.css')
                ->enqueueGlobal('Shared/mixins.css')
                ->enqueueGlobal('Shared/pico.css')
                ->enqueue(ucfirst($post_type), ucfirst($slug), $context);
        });

        add_filter('template_include', function ($template) use ($context, $templatePath) {
            $this->twig->addFunction(new TwigFunction('wp_head', wp_head(...)));
            $this->twig->addFunction(new TwigFunction('get_header', get_header(...)));
            $this->twig->addFunction(new TwigFunction('get_footer', get_footer(...)));
            $this->twig->addFunction(new TwigFunction('wp_footer', wp_footer(...)));

            echo $this->twig->render($templatePath, [
                'this' => $context,
            ]);
            return $template;
        });
    }
}
