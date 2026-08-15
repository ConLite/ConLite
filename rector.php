<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig::configure()
        ->withParallel(
            timeoutSeconds: 480,
            maxNumberOfProcess: 6,
            jobSize: 40,
        )
        ->withPaths([
            __DIR__ . '/conlite',
            //__DIR__ . '/GeneratedItems',
        ])
        ->withAutoloadPaths([
            __DIR__ . '/conlite',
            __DIR__ . '/conlib',
            __DIR__ . '/setup',
        ])
        ->withBootstrapFiles([
            __DIR__ . '/rector_cl_autoload.php',
        ])
        ->withSkip([
            __DIR__ . DIRECTORY_SEPARATOR . 'data',
            __DIR__ . DIRECTORY_SEPARATOR . 'pear',
            __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            codingStyle: true,
            typeDeclarations: true,
            privatization: true,
            naming: true,
            rectorPreset: true,
        // ...
        )
    ($rectorConfig);
};