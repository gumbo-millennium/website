# Contribution guide

So, you'd like to help with the Gumbo Millennium website? That's absolutely
AWESOME 🎉!

This guide describes how to get started with developing the website. It should
be pretty hacktoberfest-proof (and, thusly, easy to use), so please create a
documentation issue if you can't figure out some parts of this guide.

## Required software

As we work on this site on a wide range of devices, we've decided to use some
software to ease our work, and to provide a consistent development experience
for all our users.

Therefore, the requirements for this project are a _tiny bit_ bigger when
comared to standard Laravel development (the addition of Docker, mainly). This
is to ensure all our users are able to work in a consistent,
production-mirroring environment.

These following requirements are for Linux, Mac OS and Windows:


- **PHP 7.4 or newer** - [Website][site-php] - We use the new [flexible
  Heredoc][heredoc] and arrow functions on some occasions, which doesn't work in PHP <7.3.
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

### Larvel Homestead and Laravel Valet

This project **does __not__ support** Laravel Homestead and Laravel Valet. We
use Docker in place of these systems, since they represent the server
environment more closely, work across all supported OS'es and since we don't
want to restrict easy development to just one (very expensive) platform.

### Windows users

A small note for Window users: We recommend using the [Windows Subsystem
Linux][wsl] (or at least a Git Bash or something), as our post-install scripts
are written in Bash, and are likely _not_ to work in PowerShell or CMD.

[wsl]: https://docs.microsoft.com/en-us/windows/wsl/install-win10

## Checking your shell

After installing all software, please make sure all commands that are required
for installation work. You can easily test this by running the following on the
command line:

- `composer -v`
- `php -v`
- `yarn --version`
- `docker version` (Windows users an getting errors? [read this][wsl-docker])
- `docker-compose`

<!-- 
ARCHIVED AT https://web.archive.org/web/20200423125549/https://medium.com/@callback.insanity/using-docker-with-windows-subsystem-for-linux-wsl-on-windows-10-d2deacad491f
-->
[wsl-docker]: https://medium.com/@callback.insanity/using-docker-with-windows-subsystem-for-linux-wsl-on-windows-10-d2deacad491f

## Configuring Laravel Nova repository

This project uses Laravel Nova, a proprietary admin panel developed by the
Laravel team. This repo is behind authentication, which means you'll need a
login and password (or token) to login.

If you don't have access via any means, you can poke @roelofr (or mail him),
and you might be able to get a token.

After you got the token, you need to register it in Composer:

```
composer config http-basic.nova.laravel.com <username> <password>
```

### Go live

After all these checks, you can go live with your installation. We've set up a
command that runs all required steps for installation. It may take about 20
mins to complete, and only needs to be run once.

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
