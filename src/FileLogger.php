<?php

namespace ScoobyFileLogger;

use DateTime;
use Psr\Log\AbstractLogger;
use ScoobyConsoleLogger\ConsoleLogger;
use ScoobyConsoleLogger\LogLevel;

class FileLogger extends AbstractLogger
{
    /** @var string */
    private $filePath;

    /** @var string */
    private $dateFormat;

    /** @var bool */
    private $showAllInConsole;

    /** @var ConsoleLogger */
    private $consoleLogger;

    public function __construct(
        ?string $fileName = null,
        string $filePath = 'var/log/',
        string $dateFormat = 'd-m-Y H:i:s',
        bool $showAllInConsole = false
    ) {
        if (null === $fileName) {
            $fileName = (new DateTime())->format('Y_m_d');
        }

        $this->filePath = __DIR__ . '/../' . $filePath . $fileName . '.log';
        $this->dateFormat = $dateFormat;
        $this->showAllInConsole = $showAllInConsole;
        $this->consoleLogger = new ConsoleLogger($dateFormat);

        if (!is_dir(__DIR__ . '/../' . $filePath)) {
            mkdir(__DIR__ . '/../' . $filePath, 0777, true);
        }

        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }
    }

    public function divider(string $char = '=', int $number = 65, ?bool $showInConsole = null): self
    {
        $divider = '';

        for ($i = 0; $i < $number; ++$i) {
            $divider .= $char;
        }

        $this->log(LogLevel::DIVIDER, $divider, ['showTitle' => false, 'showDate' => false], $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function alert($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::ALERT, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function critical($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function debug($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::DEBUG, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function emergency($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function error($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::ERROR, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function info($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::INFO, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function notice($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::NOTICE, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function success($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::SUCCESS, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function title($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::TITLE, $message, $context, $showInConsole);

        return $this;
    }

    /**
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function warning($message, array $context = [], ?bool $showInConsole = null): self
    {
        $this->log(LogLevel::WARNING, $message, $context, $showInConsole);

        return $this;
    }

    /** @param (string|bool)[] $context */
    public function echo(string $message, array $context = [], ?bool $showInConsole = null): self
    {
        if (!isset($context['showTitle'])) {
            $context['showTitle'] = false;
        }

        $this->log(LogLevel::ECHO, $message, $context, $showInConsole);

        return $this;
    }

    public function setDateFormat(string $dateFormat): self
    {
        $this->dateFormat = $dateFormat;

        return $this;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param (string|bool)[] $context
     */
    public function log($level, $message, array $context = [], ?bool $showInConsole = null): self
    {
        $title = strtoupper($level);

        file_put_contents($this->filePath, $this->setMessageDisplay($message, $title, $context) . PHP_EOL, FILE_APPEND | LOCK_EX);

        if ((true === $this->showAllInConsole && false !== $showInConsole) || true === $showInConsole) {
            if (LogLevel::DIVIDER === $level) {
                $this->consoleLogger->log(LogLevel::DIVIDER, $message, $context);
            } else {
                $this->consoleLogger->$level($message, $context);
            }
        }

        return $this;
    }

    /** @param (string|bool)[] $context */
    private function setMessageDisplay(
        string $message,
        string $title,
        array $context
    ): string {
        $infos = '';

        if (!isset($context['showDate']) || (is_bool($context['showDate']) && true === $context['showDate'])) {
            $infos .= (new DateTime())->format($this->dateFormat) . ' - ';
        }

        if (!isset($context['showTitle']) || (is_bool($context['showTitle']) && true === $context['showTitle'])) {
            $infos .= $title . ' - ';
        }

        return $infos . $message;
    }
}
