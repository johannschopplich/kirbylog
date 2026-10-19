<div align="center">
  <img src="./.github/favicon.svg" alt="Kirbylog logo" width="120">

# Kirbylog

Kirbylog is a plugin for [Kirby CMS](https://getkirby.com) with one global `kirbylog()` helper that appends any value to a daily log file – no dependencies beyond Kirby.

[Features](#features) •
[Options](#options)

</div>

## When to Use

| I want to…                                         | Use                              |
| -------------------------------------------------- | -------------------------------- |
| Log a message from a template, controller, or hook | `kirbylog('Something happened')` |
| Inspect an array, an object, or a field value      | `kirbylog($data)`                |
| Mark an entry as an error or a warning             | `kirbylog($message, 'error')`    |
| Write to another folder or into a single file      | `dir` and `filename` options     |
| Log with levels of my own                          | `levels` option                  |

## Features

### 🪃 One Global Helper

Call `kirbylog()` anywhere once Kirby has loaded its plugins – templates, controllers, hooks, routes:

```php
kirbylog('Something happened');
```

Each call appends one entry to `site/logs/<Y-m-d>.log`, so every day gets its own file:

```log
[2026-10-19 09:28:04] INFO Something happened
```

### 🧾 Any Value

Strings are written as they are, and so are objects that turn into a string: a Kirby field logs its value, a page its ID. Everything else is encoded as pretty-printed JSON – arrays, other objects, numbers, booleans, and `null`:

```php
kirbylog(['order' => 1042, 'status' => 'paid']);
```

```log
[2026-10-19 09:30:12] INFO {
    "order": 1042,
    "status": "paid"
}
```

A value JSON can't encode, such as `INF` or a resource, is written with `print_r()` instead.

### 🔢 Logging Levels

Pass a level as the second argument, in any case. Without one, the entry is logged as `INFO`:

```php
kirbylog($response->code(), 'error');
```

```log
[2026-10-19 12:43:56] ERROR 401
```

The default levels are the eight described by [RFC 5424](https://www.rfc-editor.org/rfc/rfc5424): `DEBUG`, `INFO`, `NOTICE`, `WARNING`, `ERROR`, `CRITICAL`, `ALERT`, and `EMERGENCY`. In debug mode, a level outside the configured list throws an `UnexpectedValueException`, so a typo shows up during development. With debug mode off, an unknown level is written in uppercase like any other.

## Requirements

- Kirby 5

## Installation

### Composer (Recommended)

```bash
composer require johannschopplich/kirbylog
```

### Manual Installation

Download and copy this repository to `/site/plugins/kirbylog`.

## Options

All options are namespaced under `johannschopplich.kirbylog`:

| Option         | Default               | Description                                                                                 |
| -------------- | --------------------- | ------------------------------------------------------------------------------------------- |
| `dir`          | `site/logs`           | Folder to write logs to. A closure is called on each log, so it can use `kirby()`.          |
| `filename`     | `<Y-m-d>.log`         | Name of the log file. A fixed name collects every entry in one file instead of one per day. |
| `defaultLevel` | `info`                | Level for calls without one, in any case.                                                   |
| `levels`       | The 8 RFC 5424 levels | Levels allowed in debug mode, in any case. Your list replaces the default one.              |

Example `config.php`:

```php
return [
    'johannschopplich.kirbylog' => [
        'dir' => fn () => kirby()->root('site') . '/logs/app',
        'filename' => 'app.log',
        'levels' => ['info', 'warning', 'error', 'audit']
    ]
];
```

## License

[MIT](./LICENSE) License © 2021-PRESENT [Johann Schopplich](https://github.com/johannschopplich)
