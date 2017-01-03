# Running tests

**Note**: Drupal will be (re)installed and bootstrapped (and downloaded, if not exists) programmatically for every tests execution. This lead to an additional overhead.

## Dependencies

To run the tests you should have properly configured environment with **PHP** equal or above of `5.5`, **Selenium** standalone server `2.53` or **PhantomJS** `2` and **MySQL** `5.5` or greater.

## Installation

In a root directory of this repository do the `composer install` and choose one of testing strategies below after all dependencies will be installed.

### Drupal 7

```shell
DRUPAL_CORE=7 ./bin/phpunit --coverage-text
```

### Drupal 8

```shell
DRUPAL_CORE=8 ./bin/phpunit --coverage-text
```
