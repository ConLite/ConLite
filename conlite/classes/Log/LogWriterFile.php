<?php

namespace ConLite\Log;

use ConLite\Exceptions\Exception;
use ConLite\Exceptions\FileNotFoundException;
use DirectoryIterator;

class LogWriterFile extends LogWriter
{
    /**
     * @var resource
     */
    protected $handle = NULL;
    /**
     * @var int
     */
    protected int $maxLogFileSize = 1024;
    /**
     * @var int
     */
    protected int $maxRotationFiles = 10;


    /**
     * @throws FileNotFoundException
     * @throws Exception
     */
    public function __construct(array $options = []) {

        parent::__construct($options);

        $logFileSize = (int) getEffectiveSetting('log', 'writer-file-size-' . basename($this->getOption('destination')), $this->getOption('logFileSize') ?? 0);

        if($logFileSize > 0) {
            $this->maxLogFileSize = $logFileSize;
        }

        $this->createHandle();
    }

    /**
     * @param string $message
     * @param int $priority
     * @return bool
     */
    public function write($message, $priority): bool
    {
        $this->rotateLog();
        return fwrite($this->handle, $message) != false;
    }

    public function __destruct()
    {
        $this->closeHandle();
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

    protected function closeHandle(): void
    {
        fclose($this->handle);
    }

    protected function rotateLog()
    {
        $logfile = $this->getOption('destination');

        if(!file_exists($logfile)) {
            cWarning(__FILE__, __LINE__, 'Logfile ' . $logfile . ' not found.');
            return false;
        } elseif (!is_readable($logfile)) {
            cWarning(__FILE__, __LINE__, 'Logfile ' . $logfile . ' not readable.');
            return false;
        }

        if (filesize($logfile) >= $this->maxLogFileSize * 1024) {
            $pathInfo = pathinfo($logfile);
            $baseDirectory = $pathInfo['dirname'];
            $baseName = $pathInfo['basename'];
            $numMap = [];

            foreach (new DirectoryIterator($baseDirectory) as $fileInfo) {
                if ($fileInfo->isDot() || !$fileInfo->isFile()) {
                    continue;
                }
                if (preg_match('/^' . $baseName . '\.?([0-9]*)$/', $fileInfo->getFilename(), $matches)) {
                    $num = $matches[1];
                    $file2move = $fileInfo->getFilename();
                    if ($num == '') {
                        $num = 0;
                    }
                    $numMap[$num] = $file2move;
                }
            }
            krsort($numMap);
            foreach ($numMap as $num => $file2move) {
                $targetN = $num + 1;
                if($targetN > $this->maxRotationFiles) {
                    unlink($baseDirectory . DIRECTORY_SEPARATOR . $file2move);
                    continue;
                }
                rename($baseDirectory . DIRECTORY_SEPARATOR . $file2move, $baseDirectory . DIRECTORY_SEPARATOR .$baseName . '.' . $targetN);
            }

            return true;
        }

        return false;
    }

    public function getMaxLogFileSize(): int
    {
        return $this->maxLogFileSize;
    }

    public function setMaxLogFileSize(int $maxLogFileSize): void
    {
        $this->maxLogFileSize = $maxLogFileSize;
    }
}