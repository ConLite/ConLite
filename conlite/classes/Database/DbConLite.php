<?php
/**
 *
 */

namespace ConLite\Database;

use ADORecordSet;
use ADORecordSet_array;
use ConLite\Exceptions\Exception;

/**
 *
 */
class DbConLite
{
    const HALT_YES = 'yes';
    const HALT_NO = 'no';
    const HALT_REPORT = 'report';
    const FETCH_NUMERIC = 'numeric';
    const FETCH_ASSOC = 'assoc';
    const FETCH_BOTH = 'both';
    protected static array $defaultDbConfiguration = [];
    protected static array $profileData = [];

    protected array $dataTypes = [
        0 => 'decimal',
        1 => 'tinyint',
        2 => 'smallint',
        3 => 'int',
        4 => 'float',
        5 => 'double',
        7 => 'timestamp',
        8 => 'bigint',
        9 => 'mediumint',
        10 => 'date',
        11 => 'time',
        12 => 'datetime',
        13 => 'year',
        252 => 'blob', // text, blob, tinyblob,mediumblob, etc...
        253 => 'string', // varchar and char
        254 => 'enum',
    ];

    /**
     * @var array|array[]|null[]|string[]
     */
    protected array $dbConfiguration;
    protected \ADOConnection|false $db;
    protected mixed $record;
    protected \ADOrecordset|\ADORecordSet_empty|false $result;
    protected int $Errno = 0;
    protected string $Error = '';
    protected int $row = 0;
    protected int $numRows = 0;
    protected string $Halt_On_Error;
    protected string $seqTable;
    protected bool $enableProfiling = false;
    protected bool $debug = false;
    protected string $_sHaltMsgPrefix = '';

    /**
     * @param array $options
     * @throws Exception
     */
    public function __construct(array $options = [])
    {
        $this->dbConfiguration = array_merge(self::$defaultDbConfiguration, $options);

        $type = \cRegistry::getConfigValue('database_extension');

        if (empty($type)) {
            $type = 'mysqli';
        }

        $this->dbConfiguration = array_merge($this->dbConfiguration, ['type' => $type]);

        if (isset($this->dbConfiguration['haltBehavior'])) {
            switch ($this->dbConfiguration['haltBehavior']) {
                case self::HALT_YES:
                    $this->Halt_On_Error = self::HALT_YES;
                    break;
                case self::HALT_NO:
                    $this->Halt_On_Error = self::HALT_NO;
                    break;
                case self::HALT_REPORT:
                    $this->Halt_On_Error = self::HALT_REPORT;
                    break;
            }
        }

        if (isset($this->dbConfiguration['sequenceTable']) && is_string($this->dbConfiguration['sequenceTable'])) {
            $this->seqTable = $this->dbConfiguration['sequenceTable'];
        } else {
            $this->seqTable = \cRegistry::getConfigValue('tab', 'sequence');
        }

        if (isset($this->dbConfiguration['enableProfiling']) && is_bool($this->dbConfiguration['enableProfiling'])) {
            $this->enableProfiling = $this->dbConfiguration['enableProfiling'];
        }

        $this->connect();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function connect(): void
    {

        $this->db = newADOConnection($this->dbConfiguration['type']);

        $this->db->setConnectionParameter(MYSQLI_SET_CHARSET_NAME, 'utf8mb4');

        $isConnected = $this->db->connect(
            $this->dbConfiguration['connection']['host'],
            $this->dbConfiguration['connection']['user'],
            $this->dbConfiguration['connection']['password'],
            $this->dbConfiguration['connection']['database']
        );
        if(!$isConnected) {
            throw new Exception("cannot establish db connection");
        }
        // set sql mode hardcoded @Todo make this configurable
        $this->db->Execute("SET sql_mode = ''");
    }

    public static function setDefaultConfiguration($configArray): void
    {
        self::$defaultDbConfiguration = $configArray;
    }

    public function query($query)
    {
        $this->showDebug($query);
        if (!$this->db->IsConnected() || $query == '') {
            $this->showDebug("Returned: ".$query);
            return false;
        }

        $args = func_get_args();
        if (count($args) > 1) {
            array_shift($args);
            $query = $this->prepareQueryf($query, $args);
        }

        $this->showDebug('Debug: query = ' . $query);

        if ($this->enableProfiling) {
            $start = microtime(true);
        }

        $this->result = $this->db->Execute($query);

        if ($this->enableProfiling) {
            $end = microtime(true);
            $this->addProfileData($start, $end, $query);
        }

        if ($this->result === false) {
            return false;
        }

        if($this->db->ErrorNo() > 0) {
            $this->halt('DB error!');
        }

        $this->numRows = $this->result->NumRows();
        $this->row = 0;
        return (bool) $this->result;
    }

    public function nextRecord()
    {
        if (!$this->db->IsConnected() || $this->result === false) {
            return false;
        }

        $this->record = $this->result->FetchRow();
        $this->row += 1;
        $this->Errno = $this->db->ErrorNo();
        $this->Error = $this->db->ErrorMsg();

        return is_array($this->record);
    }

    public function num_rows(): int
    {
        return $this->numRows;
    }

    public function nf(): int
    {
        return $this->num_rows();
    }

    public function f($field)
    {
        return $this->record[$field];
    }

    public function toArray(): bool|array
    {
        return ($this->num_rows() > 0) ? $this->record : [];
    }

    public function escape($string): string
    {
        return (is_null($string))?'':addslashes($string);
    }

    public function nextid($seqName)
    {
        if (!$this->db->IsConnected()) {
            $this->halt('Cannot connect db!');
            return 0;
        }

        $currentId = $this->db->getOne('SELECT nextid FROM '.$this->seqTable.' WHERE seq_name = \''.$seqName.'\'');

        if (is_null($currentId)) {
            $currentId = 0;
            $this->db->Execute(sprintf("INSERT INTO `%s` VALUES('%s', %s)", $this->seqTable, $seqName, $currentId));

            if($this->db->ErrorNo() > 0) {
                $this->halt("DB error!");
            }
        }

        $nextId = $currentId + 1;

        $this->db->Execute(sprintf("UPDATE `%s` set nextid = '%s' WHERE seq_name = '%s'", $this->seqTable, $nextId, $seqName));

        if($this->db->ErrorNo() > 0) {
            $this->halt("DB error!");
        }

        return $nextId;
    }

    public function lock($table, $mode = 'write'): int
    {
        return 1;
    }

    public function unlock($table, $mode = 'write'): int
    {
        return 1;
    }

    /**
     * @return bool|int
     * @uses affectedRows()
     *
     * @deprecated since 3.0.0, please use affectedRows() instead
     */
    public function affected_rows(): bool|int
    {
        return $this->affectedRows();
    }

    /**
     * returns affected rows as int
     *
     * @return bool|int
     */
    public function affectedRows(): bool|int
    {
        return $this->db->Affected_Rows();
    }

    public function metaData($table = '', $full = true): bool|array
    {
        $metaData = [];
        $result = null;
        if (!empty($table) && $this->db->IsConnected()) {
            $result = $this->db->execute('SELECT * FROM ' . $table);
            if ($result === false) {
                $this->halt('Metadata query failed.');
                return false;
            }
        } else {
            if ($this->result !== false) {
                $result = $this->result;
            }
        }

        $cols = $result->fieldCount();
        for ($i = 0; $i < $cols; $i++) {
            $field = $result->FetchField($i);
            //print_r($field);
            $metaData[$i]['table'] = $field->table;
            $metaData[$i]['name'] = $field->name;
            $metaData[$i]['type'] = $this->dataTypes[$field->type];
            $metaData[$i]['len'] = $field->length;
            $metaData[$i]['flags'] = $field->flags;
            if ($full) {
                $metaData['meta'][$metaData[$i]['name']] = $i;
            }
        }

        if ($full) {
            $metaData['num_fields'] = $i;
        }

        return (count($metaData) > 0) ? $metaData : false;
    }

    /**
     * @uses \ADOConnection::ServerInfo()
     * @return string[]
     */
    public function serverInfo()
    {
        return $this->db->ServerInfo();
    }

    /**
     * @deprecated since CL 3.0.0 use serverInfo() instead
     * @return string[]
     */
    public function server_info()
    {
        return $this->db->ServerInfo();
    }

    public function disconnect(): void
    {
        if ($this->db->IsConnected()) {
            $this->db->Close();
        }
    }

    /**
     * @deprecated since CL 3.0.0
     * @uses disconnect()
     * @return void
     */
    public function close(): void
    {
        $this->disconnect();
    }


    /**
     * Error handling
     *
     * Error handler function, delegates passed message to the function haltmsg() if propery
     * $this->Halt_On_Error is not set to self::HALT_REPORT.
     *
     * Terminates further script execution if $this->Halt_On_Error is set to self::HALT_YES
     *
     * @param string $sMsg The message to use for error handling
     * @return  void
     */
    public function halt(string $sMsg): void
    {
        if ($this->Halt_On_Error == self::HALT_REPORT) {
            $this->haltMsg($this->_sHaltMsgPrefix . $sMsg);
        }

        if ($this->Halt_On_Error == self::HALT_YES) {
            die('Session halted.');
        }
    }

    /**
     * Logs passed message, basically the last db error to the error log.
     * Concatenates a detailed error message and invokey PHP's error_log() method.
     *
     * @param string $sMsg
     * @return  void
     */
    public function haltMsg($sMsg)
    {
        $sName = 'ConLite DB';
        if (!$this->Error) {
            $this->Error = $this->db->ErrorMsg();
        }
        if (!$this->Errno) {
            $this->Errno = $this->db->ErrorNo();
        }

        $sMsg = sprintf("%s error: %s (%s) - info: %s\n", $sName, $this->Errno, $this->Error, $sMsg);
        error_log($sMsg);
    }

    protected function prepareQueryf($query, array $args)
    {
        if (count($args) > 0) {
            //$args = array_map(array($this, 'escape'), $args);
            array_unshift($args, $query);
            $query = call_user_func_array('sprintf', $args);
        }
        return $query;
    }

    public function getErrno(): int
    {
        return $this->Errno;
    }

    public function getError(): string
    {
        return $this->Error;
    }

    public function free()
    {
        ;
    }

    public function seek(int $number)
    {
            $this->result->Move($number);
    }

    public function getClientInfo() {
        switch ($this->dbConfiguration['type']) {
            case 'mysql':
            case 'mysqli':
            return mysqli_get_client_info();

            default:
                return '';
        }
    }

    public function getServerInfo() {
        $info = $this->db->ServerInfo();
        return $info['description'];
    }

    public function getClientEncoding() {
        switch ($this->dbConfiguration['type']) {
            case 'mysql':
            case 'mysqli':
                return $this->db->getCharSet();

            default:
                return '';
        }
    }

    /**
     * @return \ADOConnection|false
     */
    public function getDb(): bool|\ADOConnection
    {
        return $this->db;
    }


    public function getProfileData(): array
    {
        return self::$profileData;
    }

    protected function addProfileData($startTime, $endTime, $query): void
    {
        self::$profileData[] = array(
            'time' => $endTime - $startTime,
            'query' => $query
            /*,
        'ErrNo' => static::_getErrorNumber(),
        'ErrMess' => static::_getErrorMessage()*/
        );
    }

    protected function showDebug(string $string): void
    {
        if ($this->debug) {
            printf("<pre>" . $string . "</pre>\n");
        }
    }

    public function getTableNames(): \ADORecordSet_empty|\ADORecordSet|\ADORecordSet_array|bool
    {
        if(!$this->db->IsConnected()) {
            return false;
        }
        print_r($this->db->Execute("Show Tables"));

        return $this->db->Execute("Show Tables");

    }

    public function table_names(): \ADORecordSet_empty|\ADORecordSet|\ADORecordSet_array|bool
    {
        return $this->getTableNames();
    }

    public function getSeqTable(): string
    {
        return $this->seqTable;
    }

    /**
     * Stores the session data in database table.
     *
     * Overwrites parents and uses MySQLs REPLACE statement, to prevent race
     * conditions while executing INSERT statements by multiple frames in backend.
     *
     * - Existing entry will be overwritten
     * - Non existing entry will be added
     *
     * @param   string  $id    The session id (hash)
     * @param   string  $name  Name of the session
     * @param   string  $str   The value to store
     */
    public function ac_store($id, $name, $str): bool {

        $name = addslashes($name);
        $now = date('YmdHis', time());

        $iquery = sprintf(
            "REPLACE INTO %s (sid, name, val, changed) VALUES ('%s', '%s', '%s', '%s')", $this->database_table, $id, $name, $str, $now
        );

        return (bool) $this->db->query($iquery);
    }

}