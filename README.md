# Pimapp

**Sample App with Pimvc**

## Requirements

* php version >= 7.0.0
* composer installed

## Setup

```bash
git clone https://github.com/pierre-fromager/pimapp.git
cd pimapp
composer run install
chmod -R 777 src/App1/cache
chmod -R 777 src/App1/log
```

import sql tables from fixtures/sql into your database.

## Config

Configure your env in src/App1/config

Create a virtual host with DocumentRoot to src.

Add  RewriteEngine to on.

### Dev

Using autoloader first cache level.

```bash
composer dump-autoload -o
```

### Prod

Using autoloader second cache level.

```bash
composer dump-autoload -a
```

## Features

### Http middlewares

* Acl (Access control list by roles).
* Jwt (Auth).
* Tokenizer (Auth).
* Restful (Hook router to match api's routes).

### User

* Login.
* Logout.
* Register.
* Change password.
* Lost password.
* Crud complete.

### Api

* Auth through jwt.
* Acl controlled.
* Restull compliant.

### Home

* Landing page.

### Lang

* Multilang ready (.csv).

### Database

* Model code generator.
* Model Domain code generator.

### Acl

* Acl front manager.

### Metro

#### Lignes

* Path search (graph method weighted or not).
* Crud complete.
* Gis use OpenStreetMap.

#### Stations

* Crud complete.
* Gis use OpenStreetMap.