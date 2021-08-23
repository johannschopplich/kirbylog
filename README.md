# Kirbylog

> The most simple, Kirby-esque way to log content to file.

## Key features

- 🪃 Global `kirbylog` helper usable anywhere
- 💬 Arrays will be converted to JSON
- 🧩 No external library, based solely on Kirby internals
- 🔢 Supports logging levels

Most of the times, I just want to log some string or array to a file. That's what this plugin does. The given input:

```php
// Anywhere in your code
kirbylog('Something happened');
```

… will produce this example output `site/logs/2021-08-23.log`:

```log
[2021-08-23 09:28:04] INFO Something happened
```

## Installation

### Download

Download and copy this repository to `/site/plugins/kirbylog`.

### Git submodule

```bash
git submodule add https://github.com/johannschopplich/kirbylog.git site/plugins/kirbylog
```

### Composer

```bash
composer require johannschopplich/kirbylog
```

## Setup & Usage

This plugin registers a global `kirbylog` function, callable anywhere.

```php
kirbylog('Log this to file');
```

### Logging Level

Pass the logging level of your choice as the second parameter. By default, content will be logged with the `INFO` level. This plugin uses logging levels described by [RFC 5424](http://tools.ietf.org/html/rfc5424). Of course, you can tailor them to your needs.

```php
kirbylog($response->code(), 'ERROR);
```

Generated log file:

```log
[2021-08-23 12:43:56] ERROR 401
```

## Options

> All options have to prefixed with `johannschopplich.kirbylog.` in your `config.php`.

| Option | Default | Type | Description |
| --- | --- | --- | --- |
| `dir` | `fn () => kirby()->root('logs')` | `string` or `function` | Root directory for your logs. Note: Wrap `kirby()` calls in a function, because Kirby hasn't initialized yet. |
| `filename` | `strftime('%Y-%m-%d') . '.log'` | `string` | Filename to write loggable content to.
| `levels` | [See source](https://github.com/johannschopplich/kirbylog/blob/main/index.php#L9) | `array` | List of logging levels. By default, `kirbylog` supports the logging levels described by [RFC 5424](http://tools.ietf.org/html/rfc5424) |

## Alternatives

- [bnomei/kirby3-monolog](https://github.com/bnomei/kirby3-monolog) – Use Monolog to log data to files, databases, create notifications etc.

## License

MIT
