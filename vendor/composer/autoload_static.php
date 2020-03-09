<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit954ee4d752f1358901de37917a5c3035
{
    public static $files = array (
        'a5f882d89ab791a139cd2d37e50cdd80' => __DIR__ . '/..' . '/tgmpa/tgm-plugin-activation/class-tgm-plugin-activation.php',
        'c6e8da6a4c2c43028f96fa8eaf89d6be' => __DIR__ . '/../..' . '/src/helper/plugins.php',
        '0812bb316757729740644ec5e29385b3' => __DIR__ . '/../..' . '/src/helper/functions.php',
        '422cbfa39173775d4779988b3daadddf' => __DIR__ . '/..' . '/tgmpa/tgm-plugin-activation/class-tgm-plugin-activation.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Finder\\' => 25,
        ),
        'M' => 
        array (
            'MBWPAI\\' => 7,
        ),
        'L' => 
        array (
            'Light\\Composer\\' => 15,
        ),
        'C' => 
        array (
            'Composer\\Installers\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Finder\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/finder',
        ),
        'MBWPAI\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Light\\Composer\\' => 
        array (
            0 => __DIR__ . '/..' . '/light/composer-ignore-plugin',
        ),
        'Composer\\Installers\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/installers/src/Composer/Installers',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit954ee4d752f1358901de37917a5c3035::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit954ee4d752f1358901de37917a5c3035::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}