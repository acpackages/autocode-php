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

    public function debug(...$args) {
        $this->loggerMessage($args, "debug");
    }

    public function error(...$args) {
        $this->loggerMessage($args, "error");
    }

    public function exception($exception) {
        $this->loggerMessage($exception->getMessage(), "error");
    }

    public function info(...$args) {
        $this->loggerMessage($args, "info");
    }

    public function log(...$args) {
        $this->loggerMessage($args, "log");
    }

    public function warn(...$args) {
        $this->loggerMessage($args, "warn");
    }

    public function success(...$args) {
        $this->loggerMessage($args, "success");
    }

    public function closeLogFile() {
        if ($this->logFileCreated && $this->logFile) {
            if ($this->logType === AcEnumLogType::HTML) {
                $this->logFile->writeAsString("\n\t\t</table>\n\t</body>\n</html>");
            }
            $this->logFile->close();
        }
    }

    private function createLogFile() {
        if (php_sapi_name() !== 'cli') {
            $this->logFile = new AcBackgroundFile($this->logFilePath);
            if ($this->logType === AcEnumLogType::HTML) {
                $this->logFile->writeAsString("<html lang=\"eng\">\n\t<body>\n\t\t<table>");
            }
            $this->logFileCreated = true;
        }
    }

    public function newLines($count = 1) {
        for ($i = 0; $i < $count; $i++) {
            $this->log("");
        }
    }

    private function consoleMessage($message, $type) {
        $label = $this->prefix ? "{$this->prefix} : " : "";
        echo "{$label}{$message}\n";
    }

    private function printMessage($message, $type) {
        $color = $this->messageColors[$type];
        $label = $this->prefix ? "{$this->prefix} : " : "";
        echo '<p style="color:'.$color.';">'.$label.$message.'</p>';
    }

    private function loggerMessage($args, $type) {
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
    }

    private function writeToFile($message, $type) {
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
    }

    private function writeHtml($timestamp, $message, $type) {
        if ($this->logFile) {
            $this->logFile->writeAsString("\n\t<tr><td>{$timestamp}</td><td>{$message}</td></tr>");
        }
    }

    private function writeText($timestamp, $message, $type) {
        if ($this->logFile) {
            $this->logFile->writeAsString("\n{$timestamp} => {$message}");
        }
    }
}
?>
