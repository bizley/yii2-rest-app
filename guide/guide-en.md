Yii 2 REST application with JWT authorization and Codeception tests: Tutorial
=============================================================================

In this tutorial you will learn how to create simple REST-only application with Yii 2 framework.

Some assumptions are made here:
- PHP is available in version at least 7.3.
- You are familiar with [Composer](https://getcomposer.org) and it's installed globally.
- MySQL is available in version at least 5.7.
  
Please make proper adjustments if necessary.

> Tip: Finished product of this tutorial is available at https://github.com/bizley/yii2-rest-app but I recommend to
> continue reading and building the app along with the guide.

> Tip: Tutorial is also available in [Polish](https://github.com/bizley/yii2-rest-app/guide/guide-pl.md).

> Tip: You can use [Docker](https://docs.docker.com/) with [Docker Compose](https://docs.docker.com/compose/) for 
> setting your environment if you have got these available at your machine.
> Folder `docker` is [available](https://github.com/bizley/yii2-rest-app/docker/) together with this tutorial.

Preparation
-----------

Create a folder where you want your project to be stored - in this tutorial I will use `app` for its name. In the folder 
create new file named `composer.json`. We will use Composer to download the Yii 2 framework and all other required packages.

The content of `composer.json` should be as follows:

```json
{
  "require": {
    "yiisoft/yii2": "2.0.30"
  },
  "replace": {
    "bower-asset/jquery": ">=1.11.0",
    "bower-asset/inputmask": ">=3.2.0",
    "bower-asset/punycode": ">=1.3.0",
    "bower-asset/yii2-pjax": ">=2.0.0"
  }
}
```

Why it looks like that? For sure there is no need to explain `require` section - it's simply the list of packages that 
are required for our project ("yiisoft/yii2" is the Yii 2 framework itself). But what about the rest?

Problem with Yii 2 is that it was never designed to build REST-only application with it. Framework forces us to 
download not only the core but all assets related packages (like jQuery) as well. And while these packages are great to 
build web applications for clients' browsers we don't want to use them for our REST app. Soon there will be Yii 3 
released - new version of this amazing framework - where the problem is gone, you will be able to cherry-pick only 
needed elements of the framework without the hassle. As for now - Yii provides the way to bypass it in the 
[Installation Guide](https://www.yiiframework.com/doc/guide/2.0/en/start-installation#installing-assets). Simply put we 
claim the libraries listed in the `replace` section are already provided by our project where in reality it's not true 
but it's enough for Composer to not download them.

Install the framework with

    composer install

command run from `app` folder.

> Notice: Yii version in the tutorial is locked as 2.0.30. You can change it in your `composer.json` to `"~2.0.30"` 
> (mind the tilde `~`) to fetch newer version (if available) with `composer update` command.

Creating Database
-----------------

Create new MySQL database named `yii2-rest-api` with user of the same name and password `password` to make it simpler. 
Of course remember to use more sophisticated passwords in production environment.

> Tip: Provided Docker network already covers this part.

Console Configuration
---------------------

Create `config` folder inside the `app` folder. This is the place for app configuration. Inside `config` folder create 
`console.php` file with this code:

```php
<?php

return [
    'id' => 'yii2-console',
    'basePath' => dirname(__DIR__),
];

```

This is most basic [app configuration](https://www.yiiframework.com/doc/guide/2.0/en/concept-configurations#application-configurations). 
We set ID for it and `app` folder to be a root folder of the app.

Now create file `db.php` next to the `console.php` file. There will be configuration for `db` component only.

```php
<?php

return [
    'class' => \yii\db\Connection::class,
    'dsn' => 'mysql:host=localhost;dbname=yii2-rest-api',
    'username' => 'yii2-rest-api',
    'password' => 'password',
    'charset' => 'utf8mb4',
];

```

Then include this file in `console.php`:

```php
<?php

return [
    'id' => 'yii2-console',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => require 'db.php',
    ]
];

```

We have started to add [components](https://www.yiiframework.com/doc/guide/2.0/en/concept-components) to our app - first 
one is to connect to the database. We store it in a separate file and "require" it in `console.php` to be able to keep it 
in one place and use it later on in another config file without the need to write it all again.

> Tip: For Docker setup you must change host from `localhost` to `yii2-rest-api-db`.

`Db` component now will be able to connect to the database with provided credentials.

Creating First Migration
------------------------







Basic App Configuration
-----------------------

In the `config` folder create `api.php` file with this code:

```php
<?php

return [
    'id' => 'yii2-rest-api',
    'basePath' => dirname(__DIR__),
];

```

As you can see it's almost the same as in `console.php` file. Now for some more interesting bits - create `runtime` 
folder inside `app`, make it write-able and go back to `api.php`.

```php
<?php

return [
    'id' => 'yii2-rest-api',
    'basePath' => dirname(__DIR__),
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
    ],
];

```

This is cache component config - using file storage, but you can change it later to a 
[different one](https://www.yiiframework.com/doc/guide/2.0/en/caching-data#supported-cache-storage) if you want. 
FileCache by default stores files in `runtime/cache` folder.

We need now some logs to see what is going on under the hood of application.

```php
<?php

return [
    'id' => 'yii2-rest-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
];

```

As you can see `log` component is added here and it will log 3 call stack if 
[DEBUG](https://www.yiiframework.com/doc/guide/2.0/en/structure-entry-scripts#defining-constants) flag is set to `true` 
or none stack otherwise. Logger will store the information inside the files (FileTarget) by default and only the 
information marked as `error` or `warning`. You can change these settings later on.  
Additionally `log` component is added in `bootstrap` section to run it during the 
[bootstraping process](https://www.yiiframework.com/doc/guide/2.0/en/structure-applications#bootstrap).