# Gumbo Millennium

[![Build status][shield-build]][link-build]
[![Code Climate maintainability rating][shield-cc-maintainability]][link-cc-maintainability]
[![Code Climate coverage rating][shield-cc-coverage]][link-cc-coverage]

[![Javascript code style: standard][shield-js]][link-js]
[![PHP code style: PSR-12][shield-php]][link-php]
[![License: Mozilla Public License v2][shield-license]][link-license]

This is the website for the Gumbo Millennium student association.

## Used features

- [Laravel Framework][laravel]
- [Laravel Nova][laravel-nova]
- [Mollie Payments API][mollie]
- [Conscribo API][conscribo] (to fetch our members)
- [Google Admin SDK][google-directory] (for syncing our mailing lists easily)

We're using [Tailwind][tailwind], drawing inspiration from the Tailwind Components.

## License

The software is licensed under the [Mozilla Public License v2][link-license].

## Contributing

Please have a look at the [Contribution Guide][contrib] on how to get started.

<!-- Links -->

[shield-build]: https://img.shields.io/github/workflow/status/gumbo-millennium/website/Test%20and%20deploy?logo=github&label=Build
[shield-cc-maintainability]: https://img.shields.io/codeclimate/maintainability/gumbo-millennium/website.svg?label=Maintainability&logo=codeclimate
[shield-cc-coverage]: https://img.shields.io/codeclimate/coverage-letter/gumbo-millennium/website.svg?label=Coverage&logo=codeclimate
[shield-js]: https://img.shields.io/badge/js_style-standard-brightgreen.svg
[shield-php]: https://img.shields.io/badge/PHP_style-PSR--12-8892be.svg
[shield-license]: https://img.shields.io/github/license/gumbo-millennium/website.svg

[link-build]: https://github.com/gumbo-millennium/website/actions/workflows/test-and-deploy.yml
[link-cc-maintainability]: https://codeclimate.com/github/gumbo-millennium/website
[link-cc-coverage]: https://codeclimate.com/github/gumbo-millennium/website
[link-js]: https://standardjs.com/
[link-php]: https://www.php-fig.org/psr/psr-12/
[link-license]: LICENSE.md

[laravel]: https://laravel.com/
[laravel-nova]: https://nova.laravel.com/
[mollie]: https://docs.mollie.com/index
[google-directory]: https://developers.google.com/admin-sdk/directory/v1/guides/manage-groups
[conscribo]: https://www.conscribo.nl/api/
[tailwind]: https://tailwindcss.com
[contrib]: ./CONTRIBUTING.md
