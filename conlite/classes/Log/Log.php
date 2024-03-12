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
    public function __construct(LogWriter $writer = null)
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
    public function setShortcutHandler($shortcut, $handler): bool
    {
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

    public function unsetShortcutHandler($shortcut)
    {
        if(!in_array($shortcut, $this->shortcutHandlers)) {
            throw new InvalidArgumentException('The specified shortcut handler does not exist.');
        }

        unset($this->shortcutHandlers[$shortcut]);
        return true;
    }

    public function buffer(string $message, $priority = null): void
    {
        $this->buffer[] = [$message, $priority];
    }

    public function commit(bool $clearBuffer = true)
    {
        if (count($this->buffer) == 0) {
            cWarning(__FILE__, __LINE__, 'There are no buffered messages to commit.');
            return false;
        }

        foreach ($this->buffer as $bufferInfo) {
            $this->log($bufferInfo[0], $bufferInfo[1]);
        }

        if ($clearBuffer) {
            $this->clearBuffer();
        }
    }

    public function clearBuffer(): void
    {
        $this->buffer = [];
    }

    public function log(string $message, $priority = null): void
    {
        if ($priority && !is_int($priority) && in_array($priority, $this->priorities)) {
            $priority = array_search($priority, $this->priorities);
        }

        if ($priority === null || !array_key_exists($priority, $this->priorities)) {
            $priority = $this->getWriter()->getOption('default_priority');
        }

        $logMessage = $this->getWriter()->getOption('log_format');
        $lineEnding = $this->getWriter()->getOption('line_ending');

        foreach ($this->shortcutHandlers as $shortcut => $handler) {
            if (cString::getPartOfString($shortcut, 0, 1) != '%') {
                $shortcut = '%' . $shortcut;
            }

            $info = [
                'message' => $message,
                'priority' => $priority
            ];

            $value = call_user_func($handler, $info);

            $logMessage = str_replace($shortcut, $value, $logMessage);
        }

        $this->getWriter()->write($logMessage . $lineEnding, $priority);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addPriority(string $name, int $value): void
    {
        if ($name == '') {
            throw new InvalidArgumentException('Priority name must not be empty.');
        }

        if (in_array($name, $this->priorities)) {
            throw new InvalidArgumentException('The given priority name already exists.');
        }

        if (array_key_exists($value, $this->priorities)) {
            throw new InvalidArgumentException('The priority value already exists.');
        }

        $this->priorities[$value] = $name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removePriority(string $name): void
    {
        if ($name == '') {
            throw new InvalidArgumentException('Priority name must not be empty.');
        }

        if (!in_array($name, $this->priorities)) {
            throw new InvalidArgumentException('Priority name does not exist.');
        }

        if (in_array($name, $this->defaultPriorities)) {
            throw new InvalidArgumentException('Removing default priorities is not allowed.');
        }

        $priorityIndex = array_search($name, $this->priorities);

        unset($this->priorities[$priorityIndex]);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function __call(string $method, array $arguments) {
        $priorityName = cString::toUpperCase($method);

        if (!in_array($priorityName, $this->priorities)) {
            throw new InvalidArgumentException('The given priority ' . $priorityName . ' is not supported.');
        }

        $priorityIndex = array_search($priorityName, $this->priorities);

        $this->log($arguments[0], $priorityIndex);
    }

    /**
     * Shortcut Handler Date.
     * Returns the current date.
     *
     * @return string
     *     The current date
     */
    public function shDate(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Shortcut Handler Level.
     * Returns the canonical name of the priority.
     * The canonical name is padded to 10 characters to achieve a better
     * formatting.
     *
     * @param array $info
     * @return string
     *         The canonical log level
     */
    public function shLevel(array $info): string
    {
        $logLevel = $info['priority'];
        return str_pad($this->priorities[$logLevel], 10, ' ', STR_PAD_BOTH);
    }

    /**
     * Shortcut Handler Message.
     * Returns the log message.
     *
     * @param array $info
     * @return string
     *         The log message
     */
    public function shMessage(array $info): string
    {
        return $info['message'];
    }
}