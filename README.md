# Behat TqExtension

The **TqExtension** provide a flexible methods and API for testing websites running on Drupal 7. All
code in this repository extends an integration layer provided by [DrupalExtension](https://github.com/jhedstrom/drupalextension).

[![Build Status](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/badges/build.png?b=master)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/drupal/tqextension/v/stable.svg)](https://packagist.org/packages/drupal/tqextension)
[![Total Downloads](https://poser.pugx.org/drupal/tqextension/downloads.svg)](https://packagist.org/packages/drupal/tqextension)
[![Latest Unstable Version](https://poser.pugx.org/drupal/tqextension/v/unstable.svg)](https://packagist.org/packages/drupal/tqextension)
[![License](https://poser.pugx.org/drupal/tqextension/license.svg)](https://packagist.org/packages/drupal/tqextension)

## Installation

- `curl -sS https://getcomposer.org/installer | php`
- `vim composer.json`
```json
{
  "require": {
    "drupal/tqextension": "~1.0"
  },
  "config": {
    "bin-dir": "bin"
  }
}
```
- `composer install`
- `cp -r vendor/drupal/tqextension/behat/ behat`
- Configure `behat.yml`

## Documentation

- [For developers](docs/developers)
- [For all](docs)

## Author

- [Sergii Bondarenko (BR0kEN)](https://github.com/BR0kEN-)

## Contributors

- [Alexander Petrov](https://github.com/aapetrov)
- [Anastasia Guba](https://github.com/Naastya)
- [Artyom Miroshnik](https://github.com/M1r1k)
- [Andrei Perciun](https://github.com/andreiperciun)

## History

TqExtension is a next stage of development of [Behat context by Propeople](https://github.com/BR0kEN-/behat-drupal-propeople-context) that is currently unsupported.

## Presentations

- [Kyiv Drupal Camp (September 5-6, 2015)](https://docs.google.com/presentation/d/1JPJvLPORbO4vf9fFLgnQ0bEqe7XahqZ7iUjsd75yKmg)
- [Lviv Drupal Camp (October 17-18, 2015)](https://docs.google.com/presentation/d/1b4m8FoUNt0zMz98FFxgZ9chV8I7V8ek2oU5GZmkCriQ)
