<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/helpers/kirbylog.php';

use Kirby\Cms\App;
use Kirby\Toolkit\F;
use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{
    protected $kirby;
    protected $logPath;

    public function setUp(): void
    {
        $this->kirby = new App([
            'options' => [
                'debug' => true,
                'johannschopplich' => [
                    'kirbylog' => [
                        'filename' => 'test.log'
                    ]
                ]
            ]
        ]);

        $this->logPath = $this->kirby->root('logs') . '/' . option('johannschopplich.kirbylog.filename');
    }

    private function assertLogLine($content, string $level = 'info'): void
    {
        $raw = '] ' . strtoupper($level) . ' ' . $content . "\n";
        $this->assertStringContainsString($raw, F::read($this->logPath));
    }

    public function testLogExists(): void
    {
        kirbylog('test log exists');
        $this->assertTrue(F::exists($this->logPath));
    }

    public function testCanLogString(): void
    {
        $content = 'test can log string';
        kirbylog($content);
        $this->assertLogLine($content);
    }

    public function testCanLogInteger(): void
    {
        $content = 403;
        kirbylog($content);
        $this->assertLogLine($content);
    }

    public function testCanLogArray(): void
    {
        $content = ['getkirby', 'cms'];
        kirbylog($content);
        $this->assertStringContainsString(
            "INFO [\n    \"getkirby\",\n    \"cms\"\n]",
            F::read($this->logPath)
        );
    }

    public function testCustomLogLevel(): void
    {
        $content = 'test custom log level';
        $level = 'error';
        kirbylog($content, $level);
        $this->assertLogLine($content, $level);
    }

    public function testCustomLogLevelCase(): void
    {
        $content = 'test custom log level case';
        $level = 'error';
        kirbylog($content, $level);
        $this->assertLogLine($content, $level);
    }

    public function testLogLevelNotFound(): void
    {
        $this->expectException(UnexpectedValueException::class);
        kirbylog('test undefined log level', 'undefined');
    }
}
