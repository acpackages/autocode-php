<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5174f5c878b84399cfe4791607645b4e
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'AcDataDictionary\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'AcDataDictionary\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit5174f5c878b84399cfe4791607645b4e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5174f5c878b84399cfe4791607645b4e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5174f5c878b84399cfe4791607645b4e::$classMap;

        }, null, ClassLoader::class);
    }
}
