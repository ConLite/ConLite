<?php

namespace ConLite\Log;

use ConLite\Exceptions\Exception;
use ConLite\Exceptions\FileNotFoundException;

class LogWriterFile extends LogWriter
{
    /**
     * @var resource
     */
    protected $handle = NULL;

    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function __construct(array $options = []) {
        parent::__construct($options);

        $this->createHandle();
    }

    /**
     * @param string $message
     * @param int $priority
     * @return bool
     */
    public function write($message, $priority): bool
    {
        return fwrite($this->handle, $message) != false;
    }

    /**
     * @throws Exception
     * @throws FileNotFoundException
     */
    protected function createHandle(): void
    {
        $destination = $this->getOption('destination');
        if ($destination == '') {
            throw new Exception('No destination was specified.');
        }

        if (($this->handle = fopen($destination, 'a')) === false) {
            throw new FileNotFoundException('Destination handle could not be created.');
        }
    }
}