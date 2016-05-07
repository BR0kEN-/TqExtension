# Behat TqExtension

The **TqExtension** provide a flexible methods and API for testing websites running on Drupal 7. All
code in this repository extends an integration layer provided by [DrupalExtension](https://github.com/jhedstrom/drupalextension).

[![Build Status](https://img.shields.io/travis/BR0kEN-/TqExtension/master.svg?style=flat)](https://travis-ci.org/BR0kEN-/TqExtension)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/BR0kEN-/TqExtension.svg?style=flat)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/BR0kEN-/TqExtension.svg?style=flat)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension)
[![Total Downloads](https://poser.pugx.org/drupal/tqextension/downloads)](https://packagist.org/packages/drupal/tqextension)
[![Latest Stable Version](https://poser.pugx.org/drupal/tqextension/v/stable)](https://packagist.org/packages/drupal/tqextension)
[![License](https://poser.pugx.org/drupal/tqextension/license)](https://packagist.org/packages/drupal/tqextension)

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
