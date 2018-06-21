# Contribution guide

The following guide quickly describes how to get started with the project.

## Required programs

- PHP 7.2 or nightly
- Node
- Yarn
- Composer
- Docker + docker-compose
- Visual Studio Code (recommended)

## Getting started

Run the following steps to make a hasty getaway.

### Clone repo

Clone this repo somewhere.

### Build Docker

Build requires the entire project as context, and since `node_modules` and `vendor` are big maps, we'll
build the images first.

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

We need a `.env` file for our development environment. Just copy the `env.dev` and
generate a key, and you're good to go.

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

### Get cracking

These endpoints are now available:

- [The Website](http://127.13.37.1) at `http://127.13.37.1/`, to see the end result.
- [PHPMyAdmin](http://127.13.37.1::8000) at `http://127.13.37.1:8000/`, to manage the database (if required).
- [MailHog](http://127.13.37.1::8025) at `http://127.13.37.1:8025/`, to test e-mail delivery. Docker will auto-deliver here.
- [WordPress](http://127.13.37.1::8080) at `http://127.13.37.1:8080/`, to manage the website, this is your CMS.

## Optional steps

The following steps are optional, but might be useful if you want to make the most of this thing.

### Connect nginx-proxy

If you're using the [`nginx-proxy` for Docker](https://github.com/jwilder/nginx-proxy), make sure you add the container to the
local network for this project.

```bash
docker network connect \
    gumbo-corcel-laravel \
    "$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )""
```

Now you *should* be able to connect over the following domains:

- Website: [http://gumbo.localhost/](http://gumbo.localhost/)
- PhpMyAdmin: [http://pma.gumbo.localhost/](http://pma.gumbo.localhost/)
- MailHog: [http://mail.gumbo.localhost/](http://mail.gumbo.localhost/)
- Wordpress: [http://wordpress.gumbo.localhost/](http://wordpress.gumbo.localhost/)
