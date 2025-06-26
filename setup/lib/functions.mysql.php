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
 * @version    0.3.1
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
 *   modified 2011-02-07, Dominik Ziegler, changed "TYPE" attribute to "ENGINE" for MySQL 5.5+ support
 *   modified 2011-03-21, Murat Purc, new method getSetupMySQLDBConnection() and usage of new db connection
 *   modified 2011-05-17, Ortwin Pinke, bug fixed in getSetupMySQLDBConnection(), sequenceTable has to be set in conf-array
 *
 *   $Id$:
 * }}
 * 
 */

use ConLite\Database\DbConLite;

if (!defined('CON_FRAMEWORK')) {
    die('Illegal call');
}

/**
 * @return false always returns falls since PHP 8 mysql is removed
 */
function hasMySQLExtension(): bool
{
        return false;
}

function hasMySQLiExtension(): bool
{
    if (isPHPExtensionLoaded("mysqli") == E_EXTENSION_AVAILABLE) {
        return true;
    } else {
        return false;
    }
}

function doMySQLConnect($host, $username, $password): array
{
    $aOptions = ['connection' => ['host' => $host, 'user' => $username, 'password' => $password]];
    $db = new DB_ConLite($aOptions);
    if($db->getDb()->IsConnected()) {
        return [$db, true];
    } else {
        return [$db, false];
    }
}

function getSetupMySQLDBConnection($full = true): DB_ConLite
{
    if ($full === false) {
        // host, user and password
        $aOptions = ['connection' => ['host' => $_SESSION["dbhost"], 'user' => $_SESSION["dbuser"], 'password' => $_SESSION["dbpass"]], 'sequenceTable' => $_SESSION['dbprefix'] . '_sequence'];
    } else {
        // host, database, user and password
        $aOptions = ['connection' => ['host' => $_SESSION["dbhost"], 'database' => $_SESSION["dbname"], 'user' => $_SESSION["dbuser"], 'password' => $_SESSION["dbpass"]], 'sequenceTable' => $_SESSION['dbprefix'] . '_sequence'];
    }

    return new DB_ConLite($aOptions);
}

/**
 * @param DbConLite $db
 * @return false|mixed
 */
function fetchMySQLVersion(DbConLite $db): mixed
{
    $db->query("SELECT VERSION()");

    if ($db->nextRecord()) {
        return $db->f(0);
    } else {
        return false;
    }
}

/**
 * @param DbConLite $db
 * @return false|mixed
 */
function fetchMySQLUser(DbConLite $db): mixed
{
    $db->query("SELECT USER()");

    if ($db->nextRecord()) {
        return ($db->f(0));
    } else {
        return false;
    }
}

function checkMySQLDatabaseCreation(DbConLite $db, string $database): bool
{
    if (checkMySQLDatabaseExists($db, $database)) {
        return true;
    } else {

        $db->query("CREATE DATABASE $database");

        if ($db->getErrno() != 0) {
            return false;
        } else {
            return true;
        }
    }
}

/**
 * @param $db DB_ConLite
 * @param $database string
 * @return bool
 */
function checkMySQLDatabaseExists(DB_ConLite $db, string $database): bool
{
    if (hasMySQLiExtension()) {
        if ($db->getDb()->SelectDB($database)) {
            return true;
        } else {
            $db->query("SHOW DATABASES LIKE '$database'");

            if ($db->nextRecord()) {
                return true;
            } else {
                return false;
            }
        }
    }
    return false;
}

/**
 * @deprecated remove if no usage left
 * @param $db
 * @param $database
 * @return bool
 */
function checkMySQLDatabaseUse($db, $database): bool
{
    return checkMySQLDatabaseExists($db, $database);
}

/**
 * @param $db DB_ConLite
 * @param $database string
 * @param $table string
 * @return bool
 */
function checkMySQLTableCreation($db, $database, $table): bool
{

    if (!checkMySQLDatabaseUse($db, $database)) {
        return false;
    }

    $db->query("CREATE TABLE $table (test INT( 1 ) NOT NULL) ENGINE = MYISAM ;");

    if ($db->getErrno() == 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $db DB_ConLite
 * @param $database string
 * @param $table string
 * @return bool
 */
function checkMySQLLockTable($db, $database, $table) {
    if (!checkMySQLDatabaseUse($db, $database)) {
        return false;
    }

    $db->query("LOCK TABLES $table WRITE");

    if ($db->getErrno() == 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $db DB_ConLite
 * @param $database string
 * @return bool
 */
function checkMySQLUnlockTables($db, $database) {
    if (!checkMySQLDatabaseUse($db, $database)) {
        return false;
    }

    $db->query("UNLOCK TABLES");

    if ($db->getErrno() == 0) {
        return true;
    } else {
        return false;
    }
}

/**
 * @param $db DB_ConLite
 * @param $database string
 * @param $table string
 * @return bool
 */
function checkMySQLDropTable($db, $database, $table) {
    if (!checkMySQLDatabaseUse($db, $database)) {
        return false;
    }

    $db->query("DROP TABLE $table");

    if ($db->getErrno() == 0) {
        return true;
    } else {
        return false;
    }
}

function checkMySQLDropDatabase(DbConLite $db, string $database): bool
{
    $db->query("DROP DATABASE $database");

    if ($db->getErrno() == 0) {
        return true;
    } else {
        return false;
    }
}

function fetchMySQLStorageEngines(DbConLite $db): array
{
    $db->query("SHOW ENGINES");

    $engines = [];

    while ($db->nextRecord()) {
        $engines[] = $db->f(0);
    }

    return ($engines);
}