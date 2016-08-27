# Behat TqExtension

The **TqExtension** provide a flexible methods and API for testing websites running on Drupal 7. All
code in this repository extends an integration layer provided by [DrupalExtension](https://github.com/jhedstrom/drupalextension).

[![Build Status](https://img.shields.io/travis/BR0kEN-/TqExtension/master.svg?style=flat-square)](https://travis-ci.org/BR0kEN-/TqExtension)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/BR0kEN-/TqExtension.svg?style=flat-square)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/BR0kEN-/TqExtension.svg?style=flat-square)](https://scrutinizer-ci.com/g/BR0kEN-/TqExtension)
[![Total Downloads](https://img.shields.io/packagist/dt/drupal/tqextension.svg?style=flat-square)](https://packagist.org/packages/drupal/tqextension)
[![Latest Stable Version](https://poser.pugx.org/drupal/tqextension/v/stable?format=flat-square)](https://packagist.org/packages/drupal/tqextension)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://packagist.org/packages/drupal/tqextension)

## Requirements

* composer
* behat

## Installation

To install TqExtension on an existing project:

- `composer require drupal/tqextension`
- Add contexts to Behat, see [Configuration](#configuration) below

## Behat Quick Start Guide

If you've not worked with Behat, there is a quick starter included in this repository:

- `composer require drupal/tqextension`
- `cp -r vendor/drupal/tqextension/behat/ behat`
- `cd behat`
- `vim behat.yml` and change:
  - Replace `<BASE_URL>` with the URL of the Drupal site
  - Replace `<DRUPAL_ROOT>` with the relative or absolute directory path of the Drupal site
- Run behat: `../vendor/bin/behat`

## Configuration

1. Make the TqExtension contexts available to Behat, you can do this in one of two ways:

  A. Make your FeatureContext extend RawTqContext, [like this](behat/features/bootstrap/FeatureContext.php)

  B. Or add any specific contexts to your `behat.yml`:

    ```
    default:
      suites:
        default:
          contexts:
            - Drupal\TqExtension\Context\Drush\DrushContext
            - Drupal\TqExtension\Context\Email\EmailContext
            - Drupal\TqExtension\Context\Form\FormContext
            - Drupal\TqExtension\Context\Message\MessageContext
            - Drupal\TqExtension\Context\Redirect\RedirectContext
            - Drupal\TqExtension\Context\TqContext
            - Drupal\TqExtension\Context\User\UserContext
            - Drupal\TqExtension\Context\Wysiwyg\WysiwygContext
    ```

2. Optionally, configure the TqExtension extension with the following options in the `behat.yml`:

  ```
  default:
    extensions:
      Drupal\TqExtension:
        wait_for_email: 10
        wait_for_redirect: 60
        email_account_strings: get_account_strings_for_email
        email_accounts:
          example1@email.com:
            imap: imap.gmail.com:993/imap/ssl
            username: example1@email.com
            password: p4sswDstr_1
  ```

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
- [Alexei Gorobet](https://github.com/asgorobets)
- [Cristina Eftimita](https://github.com/Eftimitac)
- [Sergiu Teaca](https://github.com/sergiuteaca)

## History

TqExtension is a next stage of development of [Behat context by Propeople](https://github.com/BR0kEN-/behat-drupal-propeople-context) that is currently unsupported.

## Presentations

- [Kyiv Drupal Camp (September 5-6, 2015)](https://docs.google.com/presentation/d/1JPJvLPORbO4vf9fFLgnQ0bEqe7XahqZ7iUjsd75yKmg)
- [Lviv Drupal Camp (October 17-18, 2015)](https://docs.google.com/presentation/d/1b4m8FoUNt0zMz98FFxgZ9chV8I7V8ek2oU5GZmkCriQ)
