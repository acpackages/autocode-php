<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit88dc432c059b1de6c904be528adab84e
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AcWeb\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AcWeb\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit88dc432c059b1de6c904be528adab84e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit88dc432c059b1de6c904be528adab84e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit88dc432c059b1de6c904be528adab84e::$classMap;

        }, null, ClassLoader::class);
    }
}
