<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit4710875e1096bb659e0da9fbf88400bb
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Endroid\\QrCode\\' => 15,
        ),
        'D' => 
        array (
            'DASPRiD\\Enum\\' => 13,
        ),
        'C' => 
        array (
            'Conlite\\External\\' => 17,
        ),
        'B' => 
        array (
            'BaconQrCode\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Endroid\\QrCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/endroid/qr-code/src',
        ),
        'DASPRiD\\Enum\\' => 
        array (
            0 => __DIR__ . '/..' . '/dasprid/enum/src',
        ),
        'Conlite\\External\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/conlite/external',
        ),
        'BaconQrCode\\' => 
        array (
            0 => __DIR__ . '/..' . '/bacon/bacon-qr-code/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit4710875e1096bb659e0da9fbf88400bb::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit4710875e1096bb659e0da9fbf88400bb::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit4710875e1096bb659e0da9fbf88400bb::$classMap;

        }, null, ClassLoader::class);
    }
}
