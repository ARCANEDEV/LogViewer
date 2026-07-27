# Changelog

All notable changes to this fork are documented here.

## [Unreleased]

## [2.0.0] - TBD

### Added

- Bounded-memory streaming index engine for large log files.
- Binary index storage, incremental updates, tail fallback, concurrency controls, and an index warm-up command.
- Extensible format drivers for classic line and JSON log formats.
- Streaming-aware pagination, search, statistics, and log entities.
- Opt-in `console` theme.
- Opt-in `observatory` theme with operational analytics and bounded dynamic pattern discovery.
- Console and Observatory route-rendering, streaming, and PCRE-safety tests.
- `delete` configuration option (default `false`) that hides the delete controls in the `observatory` and `console` themes.

### Changed

- The HTTP viewer uses streaming-aware log retrieval for files above the configured inline threshold.
- Log searches include message, stack, and context data where supported by the selected format.
- Recursive context extraction was replaced with a linear balanced-brace scan.
- The fork version line now follows `2.x`.

### Breaking

- `Arcanedev\LogViewer\Contracts\LogViewer` includes the new `logForViewer($date)` method.
- Custom implementations of the `LogViewer` contract must implement the new method.
- Streaming is enabled by default for files larger than `max-inline-bytes`.
- Applications must ensure the configured index directory is writable.

[Unreleased]: https://github.com/iYogesharma/logviewer-laravel/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/iYogesharma/logviewer-laravel/compare/v1.2.3...v2.0.0
