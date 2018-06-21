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

### 1. Clone repo

Clone this repo somewhere.

### 2. Build Docker

Build requires the entire project as context, and since `node_modules` and `vendor` are big maps, we'll
build the images first.

```
docker-compose build
```

### 3. Install dependencies

```
yarn install
composer install
```

### 4. Configure env

```
cp .env.dev .env
php artisan key:generate
```

### 5. Fire up docker envs

```
docker-compose up -d
```

### 6. Build assets

```
yarn build
```

### 7. Get cracking

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
NGINX_ID="$( docker ps --filter ancestor=jwilder/nginx-proxy --format '{{.ID}}' | head -n1 )"
docker network connect gumbo-corcel-laravel "${NGINX_ID}"
```
