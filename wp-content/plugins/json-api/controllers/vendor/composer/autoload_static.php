<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita45f94d1b6a86d30142355d386718326
{
    public static $classMap = array (
        'BaseFacebook' => __DIR__ . '/..' . '/facebook/php-sdk/src/base_facebook.php',
        'Facebook' => __DIR__ . '/..' . '/facebook/php-sdk/src/facebook.php',
        'FacebookApiException' => __DIR__ . '/..' . '/facebook/php-sdk/src/base_facebook.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = ComposerStaticInita45f94d1b6a86d30142355d386718326::$classMap;

        }, null, ClassLoader::class);
    }
}
