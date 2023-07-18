<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->paths([
        __DIR__.'/conlite',
    ]);
    $rectorConfig->skip([
        __DIR__ . DIRECTORY_SEPARATOR . 'node_modules',
        __DIR__ . DIRECTORY_SEPARATOR . 'var',
        __DIR__ . DIRECTORY_SEPARATOR . 'vendor',
    ]);
    $rectorConfig->importNames();
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::NAMING,
    ]);
};
