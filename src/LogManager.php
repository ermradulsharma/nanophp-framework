<?php

namespace Nano\Framework;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Psr\Log\LoggerInterface;

class LogManager implements LoggerInterface
{
    protected array $channels = [];
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config ?: require __DIR__ . '/../../../config/logging.php';
    }

    /**
     * Get a log channel instance.
     */
    public function channel(?string $name = null): Logger
    {
        $name = $name ?: $this->config['default'];

        if (!isset($this->channels[$name])) {
            $this->channels[$name] = $this->createDriver($name);
        }

        return $this->channels[$name];
    }

    protected function createDriver(string $name): Logger
    {
        $config = $this->config['channels'][$name] ?? null;

        if (!$config) {
            throw new \InvalidArgumentException("Log channel [{$name}] is not defined.");
        }

        $logger = new Logger($name);

        switch ($config['driver']) {
            case 'stack':
                foreach ($config['channels'] as $channel) {
                    $childLogger = $this->channel($channel);
                    foreach ($childLogger->getHandlers() as $handler) {
                        $logger->pushHandler($handler);
                    }
                }
                break;

            case 'single':
                $logger->pushHandler(new StreamHandler($config['path'], $config['level'] ?? 'debug'));
                break;

            case 'daily':
                $logger->pushHandler(new RotatingFileHandler(
                    $config['path'],
                    $config['days'] ?? 14,
                    $config['level'] ?? 'debug'
                ));
                break;

            case 'syslog':
                $logger->pushHandler(new SyslogHandler('nanophp', LOG_USER, $config['level'] ?? 'debug'));
                break;

            case 'errorlog':
                $logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, $config['level'] ?? 'debug'));
                break;
        }

        return $logger;
    }

    // PSR-Log Implementation
    public function emergency($message, array $context = []): void
    {
        $this->channel()->emergency($message, $context);
    }
    public function alert($message, array $context = []): void
    {
        $this->channel()->alert($message, $context);
    }
    public function critical($message, array $context = []): void
    {
        $this->channel()->critical($message, $context);
    }
    public function error($message, array $context = []): void
    {
        $this->channel()->error($message, $context);
    }
    public function warning($message, array $context = []): void
    {
        $this->channel()->warning($message, $context);
    }
    public function notice($message, array $context = []): void
    {
        $this->channel()->notice($message, $context);
    }
    public function info($message, array $context = []): void
    {
        $this->channel()->info($message, $context);
    }
    public function debug($message, array $context = []): void
    {
        $this->channel()->debug($message, $context);
    }
    public function log($level, $message, array $context = []): void
    {
        $this->channel()->log($level, $message, $context);
    }
}

