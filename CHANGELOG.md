# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Added
- Add dependencies for all existing entities after module installation
- Add support for entity reference field tokens

### Changed
- Change dependencies to be entities instead of key_value entries
- Rename most classes to be more logical

### Fixed
- Add an entity's own menu link as dependency when it has menu link parent(s) tokens
- Re-resolve dependencies from a menu link's referenced entity after updating this menu link,
since the menu link tree might have changed
- Delete dependencies when the path, config or entity they are referencing are deleted

## [1.3.0] - 2020-02-04
### Added
- Add PatternDependencyStorage service
- Automatically add new menu links as dependencies of their referenced
  entities

### Fixed
- Fix `Call to undefined function
  Drupal\wmpathauto\Plugin\PatternTokenDependencies\_token_menu_link_best_match()`

## [1.2.0] - 2020-01-24
### Added
- Add menu link & url token support

### Changed
- Pass options array to dependency collectors
- Change keyvalue key

### Fixed
- Call parents in SystemDate & SystemSite constructors

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
