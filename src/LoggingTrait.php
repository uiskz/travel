<?php

namespace Uiskz\Travel;

use Yiisoft\Files\FileHelper;

/**
 * Trait with helper functions to log request/response data. Depends on the `FileHelper` class from YiiSoft for
 * recursive directory creation.
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
trait LoggingTrait
{
    /**
     * Directory to store all request/response logs. If empty, logs will not be saved.
     * @var string
     */
    protected string $logsPath = '';

    public function logRequestResponse(string $tag, string $request, string $response): void
    {
        if (empty($this->logsPath)) {
            return;
        }
        $targetDir = $this->getLogsPath();
        $now = new \DateTime();

        $baseFileName = $targetDir . '/' . $now->format('Hisv') . '_' . $tag;
        $this->logMessage($baseFileName . '_RQ.log', $request);
        $this->logMessage($baseFileName . '_RS.log', $response);
    }

    /**
     * Retrieves the full path to the logs directory for the current date in UTC.
     *
     * The path includes year-month and day subdirectories, ensuring logs are organized by date.
     *
     * @return string The full directory path for storing logs, based on the current date (UTC).
     */
    protected function getLogsPath(): string
    {
        $date = new \DateTime();
        return $this->logsPath . '/' . $date->format('Y-m') . '/'
            . $date->format('d') . '/';
    }

    /**
     * Logs a message to a specified file.
     *
     * @param string $fileName Full absolute file path. The name of the file where the message will be logged.
     * @param string $message The content of the message to be logged.
     *
     * @return void
     */
    protected function logMessage(string $fileName, string $message): void
    {
        if (empty($this->logsPath)) {
            return;
        }
        $parentDir = dirname($fileName);
        if (!file_exists($parentDir)) {
            FileHelper::ensureDirectory($parentDir, 0777);
        }

        file_put_contents($fileName, $message);
    }
}