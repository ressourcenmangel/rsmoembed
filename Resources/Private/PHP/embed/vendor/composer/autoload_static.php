<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5589daf84e91f75864359115d22ca906
{
    public static $files = array (
        '09fc349b549513bf7f4291502426f919' => __DIR__ . '/..' . '/embed/embed/src/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Http\\Message\\' => 17,
            'Psr\\Http\\Client\\' => 16,
        ),
        'M' => 
        array (
            'ML\\JsonLD\\' => 10,
        ),
        'H' => 
        array (
            'HtmlParser\\' => 11,
        ),
        'E' => 
        array (
            'Embed\\' => 6,
        ),
        'C' => 
        array (
            'Composer\\CaBundle\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Http\\Message\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-message/src',
            1 => __DIR__ . '/..' . '/psr/http-factory/src',
        ),
        'Psr\\Http\\Client\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/http-client/src',
        ),
        'ML\\JsonLD\\' => 
        array (
            0 => __DIR__ . '/..' . '/ml/json-ld',
        ),
        'HtmlParser\\' => 
        array (
            0 => __DIR__ . '/..' . '/oscarotero/html-parser/src',
        ),
        'Embed\\' => 
        array (
            0 => __DIR__ . '/..' . '/embed/embed/src',
        ),
        'Composer\\CaBundle\\' => 
        array (
            0 => __DIR__ . '/..' . '/composer/ca-bundle/src',
        ),
    );

    public static $prefixesPsr0 = array (
        'M' => 
        array (
            'ML\\IRI' => 
            array (
                0 => __DIR__ . '/..' . '/ml/iri',
            ),
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5589daf84e91f75864359115d22ca906::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5589daf84e91f75864359115d22ca906::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInit5589daf84e91f75864359115d22ca906::$prefixesPsr0;
            $loader->classMap = ComposerStaticInit5589daf84e91f75864359115d22ca906::$classMap;

        }, null, ClassLoader::class);
    }
}