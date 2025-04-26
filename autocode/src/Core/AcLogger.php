<?php

namespace Autocode;

require_once  __DIR__ . '/../enums/AcEnumLogType.php';
require_once 'AcBackgroundFile.php';

use Autocode\enums\AcEnumLogType;

class AcLogger {
    private string $logType;
    private bool $logMessages;
    private string $prefix;
    private string $logDirectory;
    private string $logFileName;
    private string $logFilePath;
    private ?AcBackgroundFile $logFile;
    private bool $logFileCreated = false;

    private array $messageColors = [
        "default" => "Black",
        "debug" => "Green",
        "error" => "Red",
        "info" => "Blue",
        "log" => "Black",
        "warn" => "Yellow",
        "success" => "Green"
    ];

    public function __construct(?bool $logMessages = true, ?string $prefix = "", ?string $logDirectory = "logs", ?string $logFileName = "", ?string $logType = AcEnumLogType::CONSOLE) {
        $this->logMessages = $logMessages;
        $this->prefix = $prefix;
        $this->logDirectory = $logDirectory;
        $this->logFileName = $logFileName;
        $this->logType = $logType;
        $this->logFilePath = "{$logDirectory}/{$logFileName}";

        if (php_sapi_name() !== 'cli') {
            $this->logFile = new AcBackgroundFile($this->logFilePath);
        }
    }

    public function debug(...$args): static {
        $this->loggerMessage($args, "debug");
        return $this;
    }

    public function error(...$args): static {
        $this->loggerMessage($args, "error");
        return $this;
    }

    public function exception($exception): static {
        $this->loggerMessage($exception->getMessage(), "error");
        return $this;
    }

    public function info(...$args): static {
        $this->loggerMessage($args, "info");
        return $this;
    }

    public function log(...$args): static {
        $this->loggerMessage($args, "log");
        return $this;
    }

    public function warn(...$args): static {
        $this->loggerMessage($args, "warn");
        return $this;
    }

    public function success(...$args): static {
        $this->loggerMessage($args, "success");
        return $this;
    }

    public function closeLogFile(): static {
        if ($this->logFileCreated && $this->logFile) {
            if ($this->logType === AcEnumLogType::HTML) {
                $this->logFile->writeAsString("\n\t\t</table>\n\t</body>\n</html>");
            }
            $this->logFile->close();
        }
        return $this;
    }

    private function createLogFile(): static {
        if (php_sapi_name() !== 'cli') {
            $this->logFile = new AcBackgroundFile($this->logFilePath);
            if ($this->logType === AcEnumLogType::HTML) {
                $this->logFile->writeAsString("<html lang=\"eng\">\n\t<body>\n\t\t<table>");
            }
            $this->logFileCreated = true;
        }
        return $this;
    }

    public function newLines($count = 1): static {
        for ($i = 0; $i < $count; $i++) {
            $this->log("");
        }
        return $this;
    }

    private function consoleMessage($message, $type): static {
        $label = $this->prefix ? "{$this->prefix} : " : "";
        echo "{$label}{$message}\n";
        return $this;
    }

    private function printMessage($message, $type): static {
        $color = $this->messageColors[$type];
        $label = $this->prefix ? "{$this->prefix} : " : "";
        echo '<p style="color:'.$color.';">'.$label.$message.'</p>';
        return $this;
    }

    private function loggerMessage($args, $type): static {
        if ($this->logMessages) {
            foreach ($args as $message) {
                if(is_array($message)) {
                    $message = json_encode($message);
                }
                if ($this->logType !== AcEnumLogType::CONSOLE && $this->logType !== AcEnumLogType::PRINT) {
                    $this->writeToFile($message, $type);
                } else if ($this->logType == AcEnumLogType::PRINT) {
                    $this->printMessage($message, $type);
                } else {
                    $this->consoleMessage($message, $type);
                }
            }
        }
        return $this;
    }

    private function writeToFile($message, $type): static {
        if (php_sapi_name() === 'cli') {
            $this->consoleMessage($message, $type);
        } else {
            if (!$this->logFileCreated) {
                $this->createLogFile();
            }
            $timestamp = date("Y-m-d H:i:s");
            $message = is_string($message) ? $message : json_encode($message, JSON_PRETTY_PRINT);
            
            if ($this->logType === AcEnumLogType::HTML) {
                $this->writeHtml($timestamp, $message, $type);
            } else {
                $this->writeText($timestamp, $message, $type);
            }
        }
        return $this;
    }

    private function writeHtml($timestamp, $message, $type): static {
        if ($this->logFile) {
            $this->logFile->writeAsString("\n\t<tr><td>{$timestamp}</td><td>{$message}</td></tr>");
        }
        return $this;
    }

    private function writeText($timestamp, $message, $type): static {
        if ($this->logFile) {
            $this->logFile->writeAsString("\n{$timestamp} => {$message}");
        }
        return $this;
    }
}
?>
