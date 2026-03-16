<?php

namespace ConLite\Session;

use ConLite\Database\DbConLite;
use ConLite\Exceptions\DatabaseException;
use ConLite\Exceptions\InvalidArgumentException;

class Session implements \SessionHandlerInterface
{

    protected DbConLite $db;

    protected string $dbTable;

    protected array $registeredVariables = [];

    /**
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected string $prefix = 'backend',
        protected string $saveHandler = 'mysql'
    )
    {
        switch ($this->saveHandler) {
            case 'mysql':
                $this->db = new DbConLite();
                $this->dbTable = \cRegistry::getConfigValue('sql', 'sqlprefix') . '_sessions';
                break;
            case 'file':

                break;

            default:
                throw new InvalidArgumentException('unknown session handler', 101);

        }
    }

    public function close()
    {
        // TODO: Implement close() method.
    }

    public function destroy(string $id)
    {
        // TODO: Implement destroy() method.
    }

    public function gc(int $max_lifetime)
    {
        // TODO: Implement gc() method.
    }

    public function open(string $path, string $name)
    {
        // TODO: Implement open() method.
    }

    public function read(string $id)
    {
        // TODO: Implement read() method.
    }

    public function write(string $id, string $data)
    {
        // TODO: Implement write() method.
    }
}