<?php

namespace Cydran\Framework\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Dotenv\Dotenv;

final class Kernel {
    public function init(): ContainerBuilder {
        $dotenv = new Dotenv();
        $dotenv->load(__DIR__ . '/../../../.env');

        if (!defined('ENVIRONMENT')) {
            define('ENVIRONMENT', $_ENV['ENVIRONMENT'] ?? 'production');
        }

        $container = new ContainerBuilder();
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/Config'));
        $loader->load('services.php');

        $container->compile();

        return $container;
    }
}
