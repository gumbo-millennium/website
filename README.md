# Gumbo Millennium website

[![Build status][shield-build]][link-build]
[![Code Climate maintainability rating][shield-cc-maintainability]][link-cc-maintainability]
[![Codacy code rating][shield-cy-rating]][link-cy-rating]
[![Code Climate coverage rating][shield-cc-coverage]][link-cc-coverage]


[![Javascript code style: standard][shield-js]][link-js]
[![PHP code style: PSR-12][shield-php]][link-php]
[![License: Mozilla Public License v2][shield-license]][link-license]

This is the website for the Gumbo Millennium student association.  The website
is powered by [Laravel][laravel], in combination with the [Stripe API][stripe]
for payments ,[MailChimp][mailchimp] for sending out newsletters, the [Google
Directory API][google-directory] for mananging aliases on our domain and lastly
the [Conscribo API][conscribo] for managing permissions on uses from our
student association's central administration.

The theme is powered by [Tailwind][tailwind] and bundled using
[Webpack][webpack].

## License

The software is licensed under the [Mozilla Public License v2][link-license].

## Contributing

Please have a look at the [Contribution Guide][contrib] on how to get started.

<!-- Links -->

[shield-build]: https://img.shields.io/github/workflow/status/gumbo-millennium/website/Build,%20test%20and%20deploy%20Laravel.svg?style=flat
[shield-cc-maintainability]: https://img.shields.io/codeclimate/maintainability/gumbo-millennium/website.svg?label=CodeClimate+Maintainability&style=flat
[shield-cy-rating]: https://img.shields.io/codacy/grade/744b88fb0b9046309aa0571429e0dd7a.svg?label=Codacy+Rating&style=flat
[shield-cc-coverage]: https://img.shields.io/codeclimate/coverage-letter/gumbo-millennium/website.svg?style=flat
[shield-js]: https://img.shields.io/badge/js%20code%20style-standard-brightgreen.svg?style=flat
[shield-php]: https://img.shields.io/badge/php%20code%20style-PSR--2-8892be.svg?style=flat
[shield-license]: https://img.shields.io/github/license/gumbo-millennium/website.svg?style=flat

[link-build]: https://github.com/gumbo-millennium/website/actions
[link-cc-maintainability]: https://codeclimate.com/github/gumbo-millennium/website
[link-cy-rating]: https://app.codacy.com/app/gumbo-millennium/website/dashboard
[link-cc-coverage]: https://codeclimate.com/github/gumbo-millennium/website
[link-js]: https://standardjs.com/
[link-php]: https://www.php-fig.org/psr/psr-2/
[link-license]: LICENSE.md

[laravel]: https://laravel.com/
[stripe]: https://stripe.com/docs/api/
[mailchimp]: https://mailchimp.com/developer/
[google-directory]: https://developers.google.com/admin-sdk/directory/v1/guides/manage-groups
[conscribo]: https://www.conscribo.nl/api/
[tailwind]: https://tailwindcss.com
[webpack]: https://webpack.js.org/
[contrib]: ./CONTRIBUTING.md
