<?php

namespace Autocode;

use DateTime;
use Autocode\Models\AcCronJob;

require_once 'AcLogger.php';
require_once 'Autocode.php';
require_once 'Models/AcCronJob.php';

class AcCron {
    private static AcLogger $logger;
    /** @var AcCronJob[] */
    private static array $cronJobs = [];
    private static ?bool $intervalId = null;

    public static function init(): void {
        self::$logger = new AcLogger();
    }

    public static function every(callable $callbackFunction, int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): string {
        $id = Autocode::uniqueId();
        $job = new AcCronJob($id, 'every', compact('days', 'hours', 'minutes', 'seconds'), $callbackFunction);
        self::$cronJobs[$id] = $job;
        self::$logger->log("Registered cron job with id $id for every $days days, $hours hours, $minutes minutes, $seconds seconds");
        return $id;
    }

    public static function dailyAt(callable $callbackFunction, int $hours = 0, int $minutes = 0, int $seconds = 0): string {
        $id = Autocode::uniqueId();
        $job = new AcCronJob($id, 'daily_at', compact('hours', 'minutes', 'seconds'), $callbackFunction);
        self::$cronJobs[$id] = $job;
        self::$logger->log("Registered cron job with id $id to execute daily at $hours:$minutes:$seconds");
        return $id;
    }

    private static function executeCronJobs(): void {
        $now = new DateTime();
        foreach (self::$cronJobs as $job) {
            $executionMode = $job->execution;
            $duration = $job->duration;
            $func = $job->callback;
            $lastExecutionTime = $job->lastExecutionTime;

            if ($executionMode === 'every') {
                $interval = self::getDurationInSeconds(
                    $duration['days'] ?? 0,
                    $duration['hours'] ?? 0,
                    $duration['minutes'] ?? 0,
                    $duration['seconds'] ?? 0
                );
                $lastTime =  $job->lastExecutionTime;
                if (!$lastTime || ($now->getTimestamp() - $lastTime->getTimestamp()) >= $interval) {
                    self::$logger->log("Executing cron job with id {$job->id} (every). Last execution time is " . ($lastExecutionTime ?? 'never'));
                    $func();
                    $job->lastExecutionTime = $now;
                }
            } elseif ($executionMode === 'daily_at') {
                if ($now->format('H:i:s') === sprintf('%02d:%02d:%02d', $duration['hours'], $duration['minutes'], $duration['seconds'])) {
                    self::$logger->log("Executing cron job with id {$job->id} (daily_at). Last execution time is " . ($lastExecutionTime ?? 'never'));
                    $func();
                    $job->lastExecutionTime = $now;
                }
            }
        }
    }

    private static function getDurationInSeconds(int $days = 0, int $hours = 0, int $minutes = 0, int $seconds = 0): int {
        return ($days * 86400) + ($hours * 3600) + ($minutes * 60) + $seconds;
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
