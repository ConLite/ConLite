<?php
declare(strict_types=1);

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig::configure()
        ->withParallel(
            timeoutSeconds: 480,
            maxNumberOfProcess: 6,
            jobSize: 40,
        )
        ->withPaths([
            __DIR__ . '/',
            //__DIR__ . '/GeneratedItems',
        ])
        ->autoloadPaths([
            __DIR__ . '/conlite',
            __DIR__ . '/conlib',
            __DIR__ . '/setup',
        ])
        ->bootstrapFiles([
            __DIR__ . '/rector_cl_autoload.php',
        ])
        ->skip([
            __DIR__ . DIRECTORY_SEPARATOR . 'node_modules',
            __DIR__ . DIRECTORY_SEPARATOR . 'var',
            __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            codingStyle: true,
            naming: true,
            privatization: true,
            typeDeclarations: true,
            rectorPreset: true,
        // ...
        );
};
/*
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig::configure()
        ->withParallel(
            timeoutSeconds: 480,
            maxNumberOfProcess: 6,
            jobSize: 40,
        )
        ->withPaths([
            __DIR__ . '/cmssystem',
            __DIR__ . '/GeneratedItems',
        ])
        ->withSkip([
            __DIR__ . '/.cache',
            __DIR__ . '/cache',
            __DIR__ . '/ccm',
            __DIR__ . '/maintenance',
            __DIR__ . '/templates',
            __DIR__ . '/templates_c',
            __DIR__ . '/vendor',
            __DIR__ . '/cmssystem/src/',
        ])
        //->withPhpSets(php53: true) // step1
        //->withPhpSets(php54: true)
        //->withPhpSets(php55: true)
        //->withPhpSets(php56: true)
        //->withPhpSets(php70: true)
        //->withPhpSets(php71: true)
        //->withPhpSets(php72: true)
        //->withPhpSets(php73: true)
        //->withPhpSets(php74: true)
        //->withPhpSets(php81: true)
        //->withPhpSets(php82: true)
        //->withPhpSets(php83: true)
        ->withPhpSets(php84: true)
    ($rectorConfig);
};*/
