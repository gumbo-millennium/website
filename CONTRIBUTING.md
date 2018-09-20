# Contribution guide

The following guide quickly describes how to get started with the project.

## Required programs

- PHP 7.2 or nightly
- Node
- Yarn
- Composer
- Docker + docker-compose
- Visual Studio Code (recommended)

## Required files

Some files could not be packaged along with this repository. They've been uploaded
to Google Drive and are added as a remote dependancy.

- [`library/npm/spacial-theme.tar.gz`](https://drive.google.com/file/d/1-GkTD3XFdLXYKso81JUp021LQDoHKEqA/view?usp=sharing).

Download the files above and put them in the mentioned location, otherwise the application
*might* not install correctly.

## Global Git ignore file

We *highly* recommend using a system or user-wide gitignore file, as explained
in [this guide][ggi-1]. This keeps our repository's `.gitignore` file nice and
short and platform agnostic. You can easily create an ignore file for your
editor and platform using [gitignore.io][ggi-2].

Or, just ignore all platforms and most common editors by running these two lines
in a Bash shell:

```
wget -O ~/.gitignore_global https://www.gitignore.io/api/code,netbeans,intellij,eclipse,linux,windows,macos
git config --global core.excludesfile ~/.gitignore_global
```

If you're missing any rules, or want to add your own, just update the
`~/.gitignore_global` file.

**Merge requests containing IDE-specific files that should *not* be added to
git, will be closed!**

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

We need a `.env` file for our development environment. Just copy the `env.dev`
and generate a key, and you're good to go.

```
cp .env.dev .env
php artisan key:generate
```

### Build assets

Now that the environment is ready, tiem to create some assets.

```
yarn build
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

### Install and build WordPress theme

The theme uses Yarn to build a nice admin env. Make sure to run the following,
from the project root.

```
yarn --cwd=library/wordpress/themes/gumbo-millennium/ install
yarn --cwd=library/wordpress/themes/gumbo-millennium/ build
```

### Connect nginx-proxy

Please install the [`nginx-proxy` for Docker](https://github.com/jwilder/nginx-proxy),
and make sure you add the container to the local network for this project.

```bash
docker network connect \
    gumbo-corcel-laravel \
    "$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )"
```

Now you *should* be able to connect over the following domains:

- Website: [http://gumbo.localhost/](http://gumbo.localhost/)
- PhpMyAdmin: [http://pma.gumbo.localhost/](http://pma.gumbo.localhost/)
- MailHog: [http://mail.gumbo.localhost/](http://mail.gumbo.localhost/)
- Wordpress: [http://wordpress.gumbo.localhost/](http://wordpress.gumbo.localhost/)

#### Restart the proxy

In some occasions, you'll still get timeouts. This is easily solved by restarting the nginx proxy
container:

```
docker restart "$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )"
```
