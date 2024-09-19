# Automating Laravel Queue Worker Restarts
![Alt text](art/queue-watch-1.webp?raw=true "Title")
[![Latest Version on Packagist](https://img.shields.io/packagist/v/rajentrivedi/queue-watch.svg?style=flat-square)](https://packagist.org/packages/rajentrivedi/queue-watch)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/rajentrivedi/queue-watch/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/rajentrivedi/queue-watch/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/rajentrivedi/queue-watch/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/rajentrivedi/queue-watch/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/rajentrivedi/queue-watch.svg?style=flat-square)](https://packagist.org/packages/rajentrivedi/queue-watch)

## Supported Versions
| Version| Supported          |
| -------| ------------------ |
| 10.x   | :white_check_mark: |
| 11.x   | :white_check_mark: |

Managing queue workers in a Laravel application can sometimes be tedious, especially when dealing with long-running processes. A common challenge is ensuring that workers are restarted whenever there are changes in the jobs, events, or listeners folders. Restarting workers manually can be inefficient and prone to oversight specially during development, potentially leading to application inconsistencies or stale queue processing.

To solve this problem, I’ve developed a Laravel package that automates this process. This package detects file changes within your Laravel application’s jobs, events, and listeners folders and automatically restarts the queue worker when changes are detected.

## Support us

<!-- [<img src="https://github-ads.s3.eu-central-1.amazonaws.com/queue-watch.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/queue-watch)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards). -->

## Installation

You can install the package via composer:

```bash
composer require rajentrivedi/queue-watch --dev
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="queue-watch-config"
```

This is the contents of the published config file:

```php
return [
    'directories' => [
        app_path('Jobs'),
        app_path('Events'),
        app_path('Listeners'),
    ],
```
## Usage

```php
php artisan queue:work:watch
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Rajen Trivedi](https://github.com/69707769+rajentrivedi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
