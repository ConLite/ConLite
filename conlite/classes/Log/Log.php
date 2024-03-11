<?php

namespace ConLite\Log;

use ConLite\Exceptions\InvalidArgumentException;
use cString;
use ReflectionClass;

class Log
{
    /**
     * logging level
     *
     * @var int
     */
    const EMERG = 0;

    /**
     * logging level
     *
     * @var int
     */
    const ALERT = 1;

    /**
     * logging level
     *
     * @var int
     */
    const CRIT = 2;

    /**
     * logging level
     *
     * @var int
     */
    const ERR = 3;

    /**
     * logging level
     *
     * @var int
     */
    const WARN = 4;

    /**
     * logging level
     *
     * @var int
     */
    const NOTICE = 5;

    /**
     * logging level
     *
     * @var int
     */
    const INFO = 6;

    /**
     * logging level
     *
     * @var int
     */
    const DEBUG = 7;

    protected $writer;

    protected $shortcutHandlers = [];

    protected $priorities = [];

    protected $defaultPriorities = [];

    protected $buffer = [];

    /**
     * @throws InvalidArgumentException
     */
    public function __construct($writer)
    {
        $createWriter = false;

        if(!$writer) {
            $createWriter = true;
        } elseif (!is_object($writer) || !($writer instanceof LogWriter)) {
            cWarning(__FILE__, __LINE__, 'The passed class is not a subclass of ConLite LogWriter. Creating new one.');
            $createWriter = true;
        }

        if($createWriter) {
            $options = ['destination' => \cRegistry::getConfigValue('path', 'logs') . 'conlite.log'];
            $writer = LogWriter::factory('File', $options);
        }

        $this->setWriter($writer);
        $this->setShortcutHandler('%date', [$this, 'shDate']);
        $this->setShortcutHandler('%level', [$this, 'shLevel']);
        $this->setShortcutHandler('%message', [$this, 'shMessage']);

        $this->getWriter()->setOption('log_format', '[%date] [%level] %message', false);

        $reflection = new ReflectionClass($this);
        $this->priorities = $this->defaultPriorities = array_flip($reflection->getConstants());
    }

    public function getWriter() {
        return $this->writer;
    }

    public function setWriter(LogWriter $writer): void
    {
        $this->writer = $writer;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setShortcutHandler($shortcut, $handler) {
        if ($shortcut == '') {
            throw new InvalidArgumentException('The shortcut name must not be empty.');
        }

        if (cString::getPartOfString($shortcut, 0, 1) == '%') {
            $shortcut = cString::getPartOfString($shortcut, 1);
        }

        if (!is_callable($handler)) {
            throw new InvalidArgumentException('The specified shortcut handler does not exist.');
        }

        if (array_key_exists($shortcut, $this->shortcutHandlers)) {
            throw new InvalidArgumentException('The shortcut ' . $shortcut . ' is already in use!');
        }

        $this->shortcutHandlers[$shortcut] = $handler;

        return true;
    }

}