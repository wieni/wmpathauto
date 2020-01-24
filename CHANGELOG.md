# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2020-01-24
### Added
- Add support for entity & config dependencies
- Add support for chained tokens
- Add PatternDependencyCollection, a wrapper object for pattern
  dependencies
- Add node, system date & system site token dependency handlers

### Changed
- PatternTokenDependencies now handle arrays of tokens instead of
  individual tokens

### Fixed
- Add missing hook_event_dispatcher dependency
- Add missing pathauto Composer dependency
- Add Drupal 8.8 requirement
- Lowered PHP dependency to 7.1

### Removed
- Remove composer.lock

## [1.0.0] - 2020-01-23
Initial release
