<?php

namespace ConLite\Log;

use ConLite\Exceptions\InvalidArgumentException;

abstract class LogWriter
{
    /**
     * @param array $options
     */
    public function __construct(
        protected array $options = []
    )
    {
        $this->setOptions($options);

        $this->setOption('default_priority', Log::INFO, false);
        $this->setOption('line_ending', PHP_EOL, false);
    }

    /**
     * @throws invalidArgumentException
     */
    public static function factory($writerName, array $writerOptions): LogWriter
    {
        $logWriterClassName = 'LogWriter' . ucfirst($writerName);
        if(!class_exists($logWriterClassName)) {
            throw new InvalidArgumentException('Unknown ConLite LogWriter class: ' . $writerName);
        }

        $writer = new $logWriterClassName($writerOptions);
        if(!($writer instanceof LogWriter)) {
            throw new InvalidArgumentException('Provided class is not an instance of ConLite LogWriter');
        }

        return $writer;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption($option) {
        return $this->options[$option];
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption($option, $value, $force = false) {
        if (!$force && isset($this->options[$option])) {
            return;
        }

        $this->options[$option] = $value;
    }
    public function removeOption($option) {
        unset($this->options[$option]);
    }

    abstract function write($message, $priority);
}