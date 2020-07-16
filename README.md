wmpathauto
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmpathauto/v/stable)](https://packagist.org/packages/wieni/wmpathauto)
[![Total Downloads](https://poser.pugx.org/wieni/wmpathauto/downloads)](https://packagist.org/packages/wieni/wmpathauto)
[![License](https://poser.pugx.org/wieni/wmpathauto/license)](https://packagist.org/packages/wieni/wmpathauto)

> Dynamic path aliases, pathauto patterns & automatic updating of
> aliases

## Why?
- **Create dynamic path aliases and pathauto patterns**, for the cases
  where tokens are too limited
- **Automatically update an entity alias** when other entities that were
  used to build this alias are updated

## Installation

This package requires PHP 7.1 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmpathauto
```

## How does it work?
### Dynamic path aliases
Sometimes when defining pathauto patterns, you'll notice the token
system is just too limited. This module supports building patterns and
aliases in code using plugins with annotations, respectively
`@AliasBuilder` and `@PatternBuilder`.

### Dependencies
When updating an entity with a path alias, all entities, configs and
other path aliases that were used to build that alias are stored in the
database. In the future, when one of those path aliases, entities or
configs are updated, the path alias that depends on them will be
automatically regenerated.

There are multiple ways to define dependencies:

### Automatic dependencies with tokens
When your pathauto pattern uses supported tokens, dependencies will be
automatically added based on those tokens. For example, if your pattern
contains the `[site:name]` token, the aliases using this pattern will be
regenerated when the site name is changed.

Support for more token types can be added by creating plugins with the
`@PatternTokenDependencyProvider` annotation, defining the token type in the
`type` parameter and implementing the
[`PatternTokenDependencyProviderInterface`](src/PatternTokenDependencyProviderInterface.php) interface.

### Manual dependencies using plugins
Dependencies can also be added manually by creating plugins with the
`@EntityAliasDependencyProvider` annotation, implementing the
[`EntityAliasDependencyProviderInterface`](src/EntityAliasDependencyProviderInterface.php) interface. Plugins with `AliasBuilder` or
`PatternBuilder` annotations implementing the same interface are also
considered.

## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE) file
for more information.
