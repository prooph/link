# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased][unreleased]

## [0.4.0] - 2015-05-12
### Added
- riot: upgraded to version 2.0.15 - BC break!
  - router can be stopped and started
  - Scope of listeners did change, better to use always self reference
  - riot-if behaviour did change, elements are now removed from the dom instead of hiding them
    - Initialization logic needs to be aligned or even better: put child tags in if conditions
- riot: app.router.stop/restart like riot.router.stop/start but attaches the routeListener again
- new global js function: format_iso_datetime

## [0.3.0] - 2015-05-10

### Changed
- link-monitor is now part of link-process-manager, see prooph/link-process-manager#7

## [0.2.0] - 2015-05-10
### Added
- Integrate message-queue module
  - Add prooph/link-message-queue : dev-master to composer.json
  - Add bernard schema to migrations and the sqlite db
  - Add Prooph\Link\MessageQueue module to application.config.php
  - Add observable support to riot App

### Changed
- Support prooph/proophessor and new new processing version
  - Changed migrations to use proophessor EventStoreSchema tool for process manager
  - Changed migrations to use processing EventStoreDoctrineSchema tool for the prooph_processing_stream generation
  - Aligned SystemCommand according to prooph/common
  - Aligned configuration
- Clean up composer.json and align dependencies versions

## 0.1.0 - 2015-03-29
### Added
- Provide skeleton for a prooph LINK application
  - default layout
  - load all available modules
  - link globally required js like jQuery, Riot, etc.
  - link globally required CSS like Bootstrap and custom style.css
- Call javascriptTicker view helper in the default layout
- Include a "#js_ticker_status" icon in the header navbar

[unreleased]: https://github.com/prooph/link/compare/v0.4.0...HEAD
[0.4.0]: https://github.com/prooph/link/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/prooph/link/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/prooph/link/compare/v0.1.0...v0.2.0
