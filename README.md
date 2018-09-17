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