# Pimapp

**Sample App with Pimvc**

## Requirements

* php version >= 5.6.3
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

### Lang (i18n)

* Multilang ready (.csv).
* Autoselect language from request.
* Whole app translation in progress.

### Database

* Model code generator.
* Model Domain code generator.
* Auto create tables from imported .csv files.
* Sqlite,Mysql,Pgsql ready.

### Acl

* Acl front manager.

### File

* Remote file browser.

### Metro

#### Lignes

* Path search (graph method weighted or not).
* Gis use OpenStreetMap.

#### Stations

* Gis use OpenStreetMap.

### Crud

* Complete CRUD from a single generic controller.
* Deep database meta's inspection for agnostic Field(s) usage.
* Sqlite,Mysql,Pgsql supported.