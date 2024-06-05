# Gumbo Millennium

This is the website for the Gumbo Millennium student association. It boasts information about
the association, with support for activities, a shop and member-only areas for documents and photos.

## Used integrations

- [Laravel Framework][laravel] as foundation
- [Laravel Nova][laravel-nova] for administration
- [Mollie Payments API][mollie] for payments
- [Conscribo API][conscribo] for user information retreival
- [Google Admin SDK][google-directory] for syncing our mailing lists

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


## testing the gumbot
Via [@BotFather](https://t.me/BotFather) you can create a new telegram bot
Next, replace in the .env file the TELEGRAM_BOT_NAME and TELEGRAM_BOT_TOKEN varbiables with your botname and token.
The command `php artisan bot:listen` will start polling the bot for updates.
