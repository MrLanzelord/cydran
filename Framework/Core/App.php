<?php

namespace Cydran\Core;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class App {
    private static ContainerInterface $container;

    public static function boot(): void {
        if (is_admin()) return;

        self::setErrorHandler();
        self::$container = (new Kernel())->init();

        add_action('wp', function () {
            App::get(\Cydran\Core\Router::class)->resolve();
        });
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * 
     * @template T
     * @param class-string<T> $id
     * @return T
     */
    public static function get(string $id): object {
        return self::$container->get($id);
    }

    public static function has(string $id): bool {
        return self::$container->has($id);
    }

    public static function getParameter(string $key): string {
        return self::$container->getParameter($key);
    }

    private static function setErrorHandler(): void {
        ob_start();

        $logger = new \Symfony\Component\ErrorHandler\BufferingLogger();
        $handler = new \Symfony\Component\ErrorHandler\ErrorHandler($logger, true);

        set_exception_handler(function (\Throwable $exception) use ($handler) {
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            http_response_code(500);
            $exception = $handler->enhanceError($exception);

            // Si quieres forzar tu propio renderizado en vez del genérico:
            $renderer = new \Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer(true);
            $content = $renderer->render($exception);

            echo $content->getAsString();
            exit;
        });
    }
}
