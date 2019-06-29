# Contribution guide

The following guide quickly describes how to get started with the project.

## Required programs

- **PHP 7.3 or newer** - [Website][site-php] - We use the new [flexible Heredoc][heredoc] on some occasions, which doesn't work in PHP 7.2.
- **NodeJS** - [Website][site-nodejs] - We use Webpack for linting, compiling and optimizing the code, which runs in NodeJS
- **Yarn** - [Website][site-yarn] - Yarn has a significant speed gain on npm, and some more predictiable script handling.
- **Composer** - [Website][site-composer] - We need a lot of dependencies (Laravel, to begin with) and Composer handles them.
- **Docker** - [Website][site-docker] - We use Docker to present an environment that matches the production servers.
- **Docker Compose** - [Website][site-docker-compose] - Docker compose provides sandboxed environments to deploy the Docker containers.

[site-php]: https://php.net/
[site-nodejs]: https://nodejs.org/
[site-yarn]: https://yarnpkg.org/
[site-composer]: https://getcomposer.org/
[site-docker]: https://www.docker.com/products/docker-desktop
[site-docker-compose]: https://docs.docker.com/compose/

[heredoc]: https://www.php.net/manual/en/migration73.incompatible.php#migration73.incompatible.core.heredoc-nowdoc

## Required files

Some files could not be packaged along with this repository. They've been uploaded
to Google Drive and are added as a remote dependancy.

- [`library/npm/spacial-theme.tar.gz`](https://drive.google.com/file/d/1-GkTD3XFdLXYKso81JUp021LQDoHKEqA/view?usp=sharing).

Download the files above and put them in the mentioned location (extraction of
archives is not required), otherwise the application *might* not install
correctly.

## Global Git ignore file

We *highly* recommend using a system or user-wide gitignore file, as explained in [this guide][ggi-1]. This
keeps our repository's `.gitignore` file nice and short and platform agnostic. You can easily create an
ignore file for your editor and platform using [gitignore.io][ggi-2].

Or, just ignore all platforms and most common editors and run these two lines in a Bash shell:

```
wget -O ~/.gitignore_global https://www.gitignore.io/api/code,netbeans,intellij,eclipse,linux,windows,macos
git config --global core.excludesfile ~/.gitignore_global
```

If you're missing any rules, or want to add your own, just update the `~/.gitignore_global` file.

**Requests containing IDE-specific files that should *not* be added to git, will be closed!**

[ggi-1]: https://help.github.com/articles/ignoring-files/#create-a-global-gitignore
[ggi-2]: https://www.gitignore.io/

## Quick start

After installing the above dependencies and files, make sure the following commands work
in your console:

- `composer`
- `php`
- `yarn`
- `docker-compose`

When that works, simply run the following commands to quickly configure and
launch the project:

```
composer run contribute
```

## Getting started

Run the following steps to make a hasty getaway.

### Clone repo

Clone this repo somewhere. Make sure to install the submodules as well (`git
clone --recursive` before cloning or `git submodule update --init` after
cloning).

### Build Docker

Build requires the entire project as context, and since `node_modules` and
`vendor` are big maps, we'll build the images first.

```
docker-compose build
```

### Install dependencies

Install Yarn (node) and Composer dependencies

```
yarn install
composer install
```

### Configure env

We need a `.env` file for our development environment. Luckily there's a command
that auto-generates an `.env` file, using the latest `.env.example` file as
reference:

```
php artisan app:env
```

### Build assets

Now that the environment is ready, tiem to create some assets.

```
yarn run development
```

### Copy vendor assets

We use Laravel Horizon for queue management, which also has a bunch of assets that
need to be published, before the application works.

```
php artisan vendor:publish --tag=horizon-assets
```

### Fire up docker envs

Now it's time to launch the docker environments so we have a database.

```
docker-compose up -d
```

### Prepare database

After the database is ready, run the migrations and the seeder.

```
php artisan migrate:fresh --seed
```

### Install and build WordPress theme and plugin

The theme uses Yarn to build a nice admin env. Make sure to run the following,
from the project root.

#### Build theme

```
yarn --cwd=library/wordpress/themes/gumbo-millennium/ install
yarn --cwd=library/wordpress/themes/gumbo-millennium/ build
```

#### Build plugin

```
composer --working-dir=library/wordpress/plugins/gumbo-millennium/ install
yarn --cwd=library/wordpress/plugins/gumbo-millennium/ install
yarn --cwd=library/wordpress/plugins/gumbo-millennium/ build
```

### Get to coding

You are now ready to start developing. If you're working on assets (Javascript or Sass),
use the `yarn start` command to compile for development and watch for changes.

The Docker container endpoints are as follows:

- Website: <http://127.13.37.1>
- PhpMyAdmin: <http://127.13.37.1:8000>
- MailHog: <http://127.13.37.1:8025>
- Wordpress: <http://127.13.37.1:8080/wp-admin>

### (Optional) Connect nginx-proxy

Please install the [`nginx-proxy` for Docker](https://github.com/jwilder/nginx-proxy),
and make sure you add the container to the local network for this project.

```bash
docker network connect \
    gumbo-website \
    "$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )"
```

Now you *should* be able to connect over the following domains:

- Website: <http://gumbo.localhost/>
- PhpMyAdmin: <http://pma.gumbo.localhost/>
- MailHog: <http://mail.gumbo.localhost/>
- Wordpress: <http://wordpress.gumbo.localhost/wp-admin>

#### Restart the proxy

In some occasions, you'll still get timeouts. This is easily solved by restarting the nginx proxy
container:

```
docker restart "$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )"
```
