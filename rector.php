<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/cms/data/modules',
       /* __DIR__ . '/conlib',
        __DIR__ . '/conlite',
        __DIR__ . '/data',
        __DIR__ . '/dievino',
        __DIR__ . '/pear',
        __DIR__ . '/setup',*/
        __DIR__.'/conlite/includes',
    ]);
    
    $rectorConfig->phpVersion(PhpVersion::PHP_80);

    // register a single rule
    //$rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
     //    SetList::DEAD_CODE,
        LevelSetList::UP_TO_PHP_80
    ]);
};
