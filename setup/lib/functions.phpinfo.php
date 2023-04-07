<?php

/**
 * Project: 
 * Contenido Content Management System
 * 
 * Description: 
 * 
 * Requirements: 
 * @con_php_req 5
 *
 * @package    ContenidoBackendArea
 * @version    0.3
 * @author     unknown
 * @copyright  four for business AG <www.4fb.de>
 * @license    http://www.contenido.org/license/LIZENZ.txt
 * @link       http://www.4fb.de
 * @link       http://www.contenido.org
 * 
 * 
 * 
 * {@internal 
 *   created  unknown
 *   modified 2008-07-07, bilal arslan, added security fix
 *   modified 2011-02-08, Dominik Ziegler, removed old PHP compatibility stuff as contenido now requires at least PHP 5
 *
 *   $Id$:
 * }}
 * 
 */
if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}


define("E_EXTENSION_AVAILABLE", 1);
define("E_EXTENSION_UNAVAILABLE", 2);
define("E_EXTENSION_CANTCHECK", 3);

/**
 * canPHPurlfopen: Checks if PHP is able to use
 * allow_url_fopen.
 */
function canPHPurlfopen(): bool|string
{
    return ini_get("allow_url_fopen");
}

function getPHPDisplayErrorSetting(): bool|string
{
    return ini_get("display_errors");
}

function getPHPFileUploadSetting(): bool|string
{
    return ini_get("file_uploads");
}

function getPHPGPCOrder(): bool|string
{
    return ini_get("gpc_order");
}

function getPHPMagicQuotesRuntime(): bool|string
{
    return ini_get("magic_quotes_runtime");
}

function getPHPMagicQuotesSybase(): bool|string
{
    return ini_get("magic_quotes_sybase");
}

function getPHPMaxExecutionTime(): bool|string
{
    return ini_get("max_execution_time");
}

function getPHPOpenBasedirSetting(): bool|string
{
    return ini_get("open_basedir");
}

function checkPHPSQLSafeMode(): bool|string
{
    return ini_get("sql.safe_mode");
}

function return_bytes($val): float|int|string
{
    if (strlen($val) == 0) {
        return 0;
    }
    $val = trim($val);
    $last = $val[strlen($val) - 1];
    return match ($last) {
        'k', 'K' => (int) $val * 1024,
        'm', 'M' => (int) $val * 1_048_576,
        default => $val,
    };
}

function isPHPExtensionLoaded($extension) {
    $value = extension_loaded($extension);

    if ($value === true) {
        return E_EXTENSION_AVAILABLE;
    }

    if ($value === false) {
        return E_EXTENSION_UNAVAILABLE;
    }

    if ($value === NULL) {
        return E_EXTENSION_CANTCHECK;
    }
}

/**
 * Test for PHP compatibility
 * 
 * @param string $sVersion phpversion to test
 * @return boolean
 */
function isPHPCompatible($sVersion = "8.0.0"): bool
{
    return version_compare(phpversion(), $sVersion, ">=");
}