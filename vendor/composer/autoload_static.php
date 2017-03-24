<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb2251223e6ad9db6a0862bf09540e8fc
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Symfony\\Component\\Yaml\\' => 23,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Symfony\\Component\\Yaml\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/yaml',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb2251223e6ad9db6a0862bf09540e8fc::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb2251223e6ad9db6a0862bf09540e8fc::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
