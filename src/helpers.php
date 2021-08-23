<?php

use Kirby\Data\Json;
use Kirby\Toolkit\F;

if (!function_exists('kirbylog')) {
    /**
     * Logs content to file
     *
     * @param string|int|array $content Can be a string, integer or an array. Arrays will be converted to JSON.
     * @param string|null $level Case-insensitive logging level.
     * @return void
     */
    function kirbylog($content, ?string $level = null): void
    {
        $level = $level ?? option('johannschopplich.kirbylog.defaultLevel', 'INFO');
        $level = strtoupper($level);

        $defaultLevels = [
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        ];

        if (
            option('debug') &&
            !in_array($level, option('johannschopplich.kirbylog.levels', $defaultLevels))
        ) {
            throw new UnexpectedValueException("Level \"{$level}\" is not part of the logging levels described");
        }

        if (is_array($content) || is_object($content)) {
            $content = Json::encode($content);
        }

        $dir = option('johannschopplich.kirbylog.dir', kirby()->root('logs'));
        $filename = option('johannschopplich.kirbylog.filename', strftime('%Y-%m-%d') . '.log');

        if (is_callable($dir)) {
            $dir = $dir();
        }

        F::append(
            $dir . '/' . $filename,
            '[' . strftime('%Y-%m-%d %H:%M:%S') . '] ' . $level . ' ' . $content . "\n"
        );
    }
}
