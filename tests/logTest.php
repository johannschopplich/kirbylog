<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/helpers.php';

use Kirby\Cms\App;
use Kirby\Data\Json;
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

    private function getLastLogLine(): string
    {
        $data = file($this->logPath);
        return $data[count($data) - 1];
    }

    private function assertLastLogLine($content, string $level = 'info'): void
    {
        $result = str_ends_with(
            rtrim($this->getLastLogLine(), "\n"),
            (string) $content
        );

        $this->assertTrue($result);
    }

    public function testLogExists(): void
    {
        kirbylog('first test');
        $this->assertTrue(F::exists($this->logPath));
    }

    public function testCanLogString(): void
    {
        $content = 'generic message';
        kirbylog($content);
        $this->assertLastLogLine($content);
    }

    public function testCanLogInteger(): void
    {
        $content = 403;
        kirbylog($content);
        $this->assertLastLogLine($content);
    }

    public function testCanLogArray(): void
    {
        $content = ['getkirby', 'kirby', 'cms'];
        kirbylog($content);
        $this->assertLastLogLine(Json::encode($content));
    }

    public function testCustomLogLevel(): void
    {
        $content = 'something went wrong';
        $level = 'error';
        kirbylog($content, $level);
        $this->assertLastLogLine($content, $level);
    }

    public function testCustomLogLevelCase(): void
    {
        $content = 'something went wrong';
        $level = 'ERROR';
        kirbylog($content, $level);
        $this->assertLastLogLine($content, $level);
    }

    public function testLogLevelNotFound(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $content = 'generic message';
        $level = 'NOT_A_LEVEL';
        kirbylog($content, $level);
    }
}
