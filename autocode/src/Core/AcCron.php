<?php

namespace Autocode;

require_once 'AcLogger.php';
require_once 'Autocode.php';

class AcCron {
    private static AcLogger $logger;
    private static array $cronJobs = [];
    private static ?bool $intervalId = null;

    public static function init(): void {
        self::$logger = new AcLogger();
    }

    public static function every(callable $callbackFunction, int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): string {
        $id = Autocode::uniqueId();
        self::$cronJobs[$id] = [
            'execution' => 'every',
            'duration' => compact('days', 'hours', 'minutes', 'seconds'),
            'function' => $callbackFunction,
            'lastExecutionTime' => null,
        ];
        self::$logger->log("Registered cron job with id $id for every $days days, $hours hours, $minutes minutes, $seconds seconds");
        return $id;
    }

    public static function dailyAt(callable $callbackFunction, int $hours = 0, int $minutes = 0, int $seconds = 0): string {
        $id = Autocode::uniqueId();
        self::$cronJobs[$id] = [
            'execution' => 'daily_at',
            'duration' => compact('hours', 'minutes', 'seconds'),
            'function' => $callbackFunction,
            'lastExecutionTime' => null,
        ];
        self::$logger->log("Registered cron job with id $id to execute daily at $hours:$minutes:$seconds");
        return $id;
    }

    private static function executeCronJobs(): void {
        $now = new DateTime();
        foreach (self::$cronJobs as $id => &$job) {
            $executionMode = $job['execution'];
            $duration = $job['duration'];
            $func = $job['function'];
            $lastExecutionTime = $job['lastExecutionTime'];

            if ($executionMode === 'every') {
                $interval = self::getDurationInSeconds(
                    days: $duration['days'],
                    hours: $duration['hours'],
                    minutes: $duration['minutes'],
                    seconds: $duration['seconds']
                );
                $lastTime = $lastExecutionTime ? new DateTime($lastExecutionTime) : null;
                if (!$lastTime || ($now->getTimestamp() - $lastTime->getTimestamp()) >= $interval) {
                    self::$logger->log("Executing cron job with id $id (every). Last execution time is " . ($lastExecutionTime ?? 'never'));
                    $func();
                    $job['lastExecutionTime'] = $now->format('Y-m-d H:i:s');
                }
            } elseif ($executionMode === 'daily_at') {
                if ($now->format('H:i:s') === sprintf('%02d:%02d:%02d', $duration['hours'], $duration['minutes'], $duration['seconds'])) {
                    self::$logger->log("Executing cron job with id $id (daily_at). Last execution time is " . ($lastExecutionTime ?? 'never'));
                    $func();
                    $job['lastExecutionTime'] = $now->format('Y-m-d H:i:s');
                }
            }
        }
    }

    private static function getDurationInSeconds(int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): int {
        return (($days * 24 * 60 * 60) + ($hours * 60 * 60) + ($minutes * 60) + $seconds);
    }

    public static function start(): void {
        self::$logger->log("Cron jobs execution started at " . date('Y-m-d H:i:s'));
        self::$intervalId = true;
        while (self::$intervalId) {
            self::executeCronJobs();
            sleep(1);
        }
    }

    public static function stop(): void {
        if (self::$intervalId) {
            self::$logger->log("Cron jobs execution stopped at " . date('Y-m-d H:i:s'));
            self::$intervalId = null;
        }
    }
}


?>