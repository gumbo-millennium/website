# Contribution guide

The following guide quickly describes how to get started with the project.

## Required programs

- **PHP 7.3 or newer** - [Website][site-php] - We use the new [flexible
  Heredoc][heredoc] on some occasions, which doesn't work in PHP 7.2.
- **NodeJS** - [Website][site-nodejs] - We use Webpack for linting, compiling
  and optimizing the code, which runs in NodeJS
- **Yarn** - [Website][site-yarn] - Yarn has a significant speed gain on npm,
  and some more predictiable script handling.
- **Composer** - [Website][site-composer] - We need a lot of dependencies
  (Laravel, to begin with) and Composer handles them.
- **Docker** - [Website][site-docker] - We use Docker to present an environment
  that matches the production servers.
- **Docker Compose** - [Website][site-docker-compose] - Docker compose provides
  sandboxed environments to deploy the Docker containers.

[site-php]: https://php.net/
[site-nodejs]: https://nodejs.org/
[site-yarn]: https://yarnpkg.org/
[site-composer]: https://getcomposer.org/
[site-docker]: https://www.docker.com/products/docker-desktop
[site-docker-compose]: https://docs.docker.com/compose/
[heredoc]: https://www.php.net/manual/en/migration73.incompatible.php#migration73.incompatible.core.heredoc-nowdoc

## Quick start

After installing the above dependencies and files, make sure the following
commands work in your console:

- `composer`
- `php`
- `yarn`
- `docker-compose`

After you've installed these programs, you need to make sure you have access to
the Laravel Nova repository.  Use the command `composer config
http-basic.nova.laravel.com <username> <password>` to configure the access
credentials. Give a shout to @roelofr if you need credentials and are a member.

After that, simply run the following command to quickly configure and
launch the project.

```
composer run contribute
```

After this command completes, go to <http://localhost:13370> to test it out.

Happy developing.

## Issue policy

When opening an issue, please consider the following:

1. Be sure to describe the issue in detail. Include demo code and/or
   screenshots if possible.
2. Mention your platform and relevant versions. As a minumum, mention your OS
   and PHP version (`php -v` is your friend here).
3. Don't be a dick.

## Pull request policy

When creating a policy file, please keep the following in mind:

1. Describe your changes, and if any tests are affected. If there's a relevant
   issue, mention that too.
2. When changing dependencies, also add their lockfiles (composer.lock for
   Composer, yarn.lock for Node)
3. Don't commit IDE-specific files (like the `.idea` folder), they often
   contain absolute paths, which won't work across systems.

If at all possible, please sign the last commit of your PR.
