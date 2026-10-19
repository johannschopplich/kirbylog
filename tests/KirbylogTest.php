<?php

declare(strict_types = 1);

use Kirby\Cms\App;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class KirbylogTest extends TestCase
{
    private const TMP = __DIR__ . '/tmp';

    protected function tearDown(): void
    {
        App::destroy();
        Dir::remove(self::TMP);
    }

    private function app(array $options = [], array $site = []): App
    {
        return new App([
            'roots' => ['index' => self::TMP],
            'options' => $options,
            'site' => $site
        ]);
    }

    private function log(string|null $filename = null): string
    {
        return F::read(self::TMP . '/site/logs/' . ($filename ?? date('Y-m-d') . '.log'));
    }

    #[Test]
    public function appends_a_timestamped_line_to_the_daily_log(): void
    {
        $this->app();

        kirbylog('first');
        kirbylog('second');

        $this->assertMatchesRegularExpression(
            '/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] INFO first\n\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] INFO second\n$/',
            $this->log()
        );
    }

    #[Test]
    public function logs_an_integer_as_is(): void
    {
        $this->app();

        kirbylog(403);

        $this->assertStringEndsWith("] INFO 403\n", $this->log());
    }

    #[Test]
    public function logs_an_array_as_pretty_printed_json(): void
    {
        $this->app();

        kirbylog(['url' => 'https://getkirby.com/', 'name' => 'Kirby']);

        $this->assertStringEndsWith(
            "] INFO {\n    \"url\": \"https://getkirby.com/\",\n    \"name\": \"Kirby\"\n}\n",
            $this->log()
        );
    }

    /** @return array<string, array{0: mixed, 1: string}> */
    public static function scalarCases(): array
    {
        return [
            'null' => [null, 'null'],
            'true' => [true, 'true'],
            'false' => [false, 'false'],
            'float' => [1.5, '1.5']
        ];
    }

    #[Test]
    #[DataProvider('scalarCases')]
    public function logs_other_scalars_as_json(mixed $content, string $expected): void
    {
        $this->app();

        kirbylog($content);

        $this->assertStringEndsWith("] INFO {$expected}\n", $this->log());
    }

    #[Test]
    public function logs_a_stringable_object_as_its_string(): void
    {
        $app = $this->app([], [
            'children' => [
                ['slug' => 'home', 'content' => ['title' => 'Home']]
            ]
        ]);

        kirbylog($app->page('home')->title());

        $this->assertStringEndsWith("] INFO Home\n", $this->log());
    }

    #[Test]
    public function logs_any_other_object_as_json(): void
    {
        $this->app();

        kirbylog((object)['status' => 'paid']);

        $this->assertStringEndsWith("] INFO {\n    \"status\": \"paid\"\n}\n", $this->log());
    }

    #[Test]
    public function logs_a_value_json_cannot_encode_with_print_r(): void
    {
        $this->app();

        kirbylog(['ratio' => INF]);

        $this->assertStringEndsWith("] INFO Array\n(\n    [ratio] => INF\n)\n", $this->log());
    }

    #[Test]
    public function logs_a_resource_with_print_r(): void
    {
        $this->app();

        kirbylog(fopen('php://memory', 'r'));

        $this->assertMatchesRegularExpression('/\] INFO Resource id #\d+\n$/', $this->log());
    }

    #[Test]
    public function substitutes_invalid_utf8_when_encoding(): void
    {
        $this->app();

        kirbylog(['name' => "Caf\xE9"]);

        $this->assertStringEndsWith("] INFO {\n    \"name\": \"Caf\u{FFFD}\"\n}\n", $this->log());
    }

    #[Test]
    public function uppercases_the_passed_level(): void
    {
        $this->app(['debug' => true]);

        kirbylog('Payment failed', 'ErRoR');

        $this->assertStringEndsWith("] ERROR Payment failed\n", $this->log());
    }

    #[Test]
    public function uses_the_configured_default_level(): void
    {
        $this->app(['johannschopplich.kirbylog' => ['defaultLevel' => 'debug']]);

        kirbylog('Cache warmed');

        $this->assertStringEndsWith("] DEBUG Cache warmed\n", $this->log());
    }

    #[Test]
    public function rejects_an_unknown_level_in_debug_mode(): void
    {
        $this->app(['debug' => true]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Level "UNDEFINED" is not part of the logging levels described');

        kirbylog('Nope', 'undefined');
    }

    /** @return array<string, array{0: array}> */
    public static function levelsConfigCases(): array
    {
        return [
            'plugin namespace' => [['johannschopplich.kirbylog' => ['levels' => ['AUDIT']]]],
            'flat key' => [['johannschopplich.kirbylog.levels' => ['AUDIT']]],
            'nested arrays' => [['johannschopplich' => ['kirbylog' => ['levels' => ['AUDIT']]]]]
        ];
    }

    #[Test]
    #[DataProvider('levelsConfigCases')]
    public function replaces_the_default_levels_with_the_configured_ones(array $options): void
    {
        $this->app(['debug' => true, ...$options]);

        kirbylog('Role changed', 'audit');
        $this->assertStringEndsWith("] AUDIT Role changed\n", $this->log());

        $this->expectException(UnexpectedValueException::class);
        kirbylog('Not configured', 'error');
    }

    #[Test]
    public function matches_configured_levels_in_any_case(): void
    {
        $this->app([
            'debug' => true,
            'johannschopplich.kirbylog' => ['levels' => ['Audit']]
        ]);

        kirbylog('Role changed', 'AUDIT');

        $this->assertStringEndsWith("] AUDIT Role changed\n", $this->log());
    }

    #[Test]
    public function writes_an_unknown_level_outside_debug_mode(): void
    {
        $this->app();

        kirbylog('Still logged', 'undefined');

        $this->assertStringEndsWith("] UNDEFINED Still logged\n", $this->log());
    }

    #[Test]
    public function writes_to_the_configured_filename(): void
    {
        $this->app(['johannschopplich.kirbylog' => ['filename' => 'app.log']]);

        kirbylog('Fixed file');

        $this->assertStringEndsWith("] INFO Fixed file\n", $this->log('app.log'));
    }

    #[Test]
    public function resolves_a_closure_for_the_log_directory(): void
    {
        $this->app(['johannschopplich.kirbylog' => [
            'dir' => fn () => App::instance()->root('index') . '/custom-logs'
        ]]);

        kirbylog('Elsewhere');

        $this->assertStringEndsWith(
            "] INFO Elsewhere\n",
            F::read(self::TMP . '/custom-logs/' . date('Y-m-d') . '.log')
        );
    }
}
