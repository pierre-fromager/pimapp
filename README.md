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

### Autoloading & deploy

Use autoloader cache level with -o or -a option (PSR-4).

```bash
composer dump-autoload -o
```
Keep in mind autoload dumping must be done after each change on envs.
Prefer -a for prod (hard caching) -o for dev (smart caching).
Check composer hook @src\App1\Commands\Composer\Installer for deploy triggering.
 
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
* Content translation in progress.

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
* use OpenStreetMap.

#### Stations

* use OpenStreetMap.

### Crud

* Complete CRUD from a single generic controller.
* Deep database meta's inspection for agnostic Field(s) usage.
* Sqlite,Mysql,Pgsql supported.

## Links

Security policy allowed for non (CN,UA,TW,RU,VN,HK) countries, sorry for others.

 * [Live version](https://pimgit.pier-infor.fr)
 * [Roadmap version](https://pimapp.pier-infor.fr)

## Tricks

### Front

#### Osm

because of query cache mechanism involved, add symlink as below :

```bash
cd src/public/img/gis
ln -s ../../../App1/cache/img/gis/osm/metro ./osm
```

#### Styles

* Adapt src/public/css/main.css

#### Behaviours

* Adapt src/public/js/main.js

### Back

Configuration is easy, all can be found in src/App1/config.

#### Envs

* dev.php: is the main entry for dev conf, check dev folder for detail.
* prod.php: is the main entry for dev conf, check prod folder for detail.

Env strategy can be set from get_env using .htaccess or .user.ini file and changed in index.php.
