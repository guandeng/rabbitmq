<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit91c1751ddcbe3add2e340e03bf073b12
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Guandeng\\Md5hash\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Guandeng\\Md5hash\\' => 
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
            $loader->prefixLengthsPsr4 = ComposerStaticInit91c1751ddcbe3add2e340e03bf073b12::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit91c1751ddcbe3add2e340e03bf073b12::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit91c1751ddcbe3add2e340e03bf073b12::$classMap;

        }, null, ClassLoader::class);
    }
}