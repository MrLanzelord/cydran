<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $config) {
    $config->parameters()->set('themePath', get_template_directory());

    $services = $config->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(FilesystemLoader::class)
        ->args([dirname(__DIR__, 3) . '/src']);

    $services->set(Environment::class)
        ->args([service(FilesystemLoader::class)]);

    $services->load('Cydran\\', dirname(__DIR__, 3) . '/Framework')
        ->public()
        ->exclude([
            dirname(__DIR__, 2) . '/Domain/ValueObject',
            dirname(__DIR__, 2) . '/Contracts',
            dirname(__DIR__, 1) . '/Config',
            dirname(__DIR__, 2) . '/Support/Builders',
        ]);

    $services->load('App\\', dirname(__DIR__, 3) . '/src')
        ->public()
        ->exclude([
            dirname(__DIR__, 2) . '/UI',
        ]);
};
