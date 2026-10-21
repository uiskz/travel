<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\LoggingTrait;

final class LoggingTraitTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/uiskz-travel-logs-' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (!is_dir($this->dir)) {
            return;
        }
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($this->dir);
    }

    private function logger(string $logsPath): object
    {
        return new class($logsPath) {
            use LoggingTrait;

            public function __construct(string $logsPath)
            {
                $this->logsPath = $logsPath;
            }

            public function path(): string
            {
                return $this->getLogsPath();
            }
        };
    }

    public function testDoesNothingWithoutLogsPath(): void
    {
        $this->logger('')->logRequestResponse('search', 'rq', 'rs');

        $this->assertDirectoryDoesNotExist($this->dir);
    }

    public function testLogsPathIsGroupedByDate(): void
    {
        $now = new \DateTime();

        $this->assertSame($this->dir . '/' . $now->format('Y-m') . '/' . $now->format('d'), $this->logger($this->dir)->path());
    }

    public function testWritesRequestAndResponse(): void
    {
        $logger = $this->logger($this->dir);
        $logger->logRequestResponse('search', 'request body', 'response body');

        $files = glob($logger->path() . '/*');
        sort($files);

        $this->assertCount(2, $files);
        $this->assertMatchesRegularExpression('~^' . preg_quote($logger->path(), '~') . '/\d{9}_search_RQ\.log$~', $files[0]);
        $this->assertStringEndsWith('_search_RS.log', $files[1]);
        $this->assertSame('request body', file_get_contents($files[0]));
        $this->assertSame('response body', file_get_contents($files[1]));
    }
}
