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

```
composer install
```

command run from `app` folder.

> Notice: Yii version in the tutorial is locked as 2.0.30. You can change it in your `composer.json` to `"~2.0.30"` 
> (mind the tilde `~`) to fetch newer version (if available) with `composer update` command.

> Tip: For Docker setup prepend all commands with `docker-compose exec yii2-rest-api-php`

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

Run the following console command from `app` folder to create table migration:

```
vendor/bin/yii migrate/create create_table_user --appconfig=config/console.php
```

This starts creating [migration](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations) generating process for 
table named `user` using the configuration from `console.php` file we created step before.  
After confirming the generation with `yes` you should see new folder named `migrations` in the `app` folder and inside 
the file named something like `m191215_165033_create_table_user.php` with code:

```php
<?php

use yii\db\Migration;

/**
 * Class m191215_165033_create_table_user
 */
class m191215_165033_create_table_user extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191215_165033_create_table_user cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191215_165033_create_table_user cannot be reverted.\n";

        return false;
    }
    */
}

```

Update it with the code we need:

```php
<?php

use yii\db\Migration;

class m191215_165033_create_table_user extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(191)->notNull()->unique(),
            'auth_key' => $this->integer()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}

```

Table `user` contains fields:
- `id` - primary key and user identificator,
- `name` - user's name, unique among the table rows - maximum is "only" 191 characters because of the character set we 
  chose - `utf8mb4` - [to support all Unicode characters](https://mathiasbynens.be/notes/mysql-utf8mb4),
- `auth_key` - number that will be stored in JWT payload for user validation,
- `created_at` - date of user's creation,
- `updated_at` - date of user's updating.

Now we can apply that migration with command:

```
vendor/bin/yii migrate --appconfig=config/console.php
```

Basic App Configuration
-----------------------

Now let's create proper application configuration. In the `config` folder create `api.php` file with this code:

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

Let's not forget about connecting the database - add "require" line in `components` array key for component `db` (just 
like it was done for `console.php`):

```php
    'components' => [
        'db' => require 'db.php',
    ],
```

User Model
----------

Our database already stores the `user` table structure. Now it's time to map it in the application through the 
[Active Record](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record) object.

Create folder `models` in the `app` folder. Inside it create `User.php` class file.

```php
<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $name
 * @property int $auth_key
 * @property string $created_at
 * @property string $updated_at
 */
class User extends ActiveRecord
{
}

```

We need to "link" this class with proper database name. Although just by naming the model `User` we can be sure that it 
will automatically map table `user` it's better to make it explicit for future sake. Add this method:

```php
public static function tableName(): string
{
    return '{{%user}}';
}
```

There are two date fields in this model - `created_at` and `updated_at`. Since these can be easily filled automatically 
we can use [behavior](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors) to do it for us every time user 
is created or modified. Add this method:

```php
public function behaviors(): array
{
    return [
        [
            'class' => TimestampBehavior::class,
            'value' => new Expression('NOW()')
        ]
    ];
}
```

with imports:

```php
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
```

[TimestampBehavior](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors#using-timestamp-behavior) will take 
care of the fields mentioned before. To make the dates easier to read in database extra config is used there - value of 
each field will not be the default `TIMESTAMP` integer but rather `DATETIME` string so database expression `NOW()` is 
used to generate the value.

User Identity
-------------

We want the `User` model to represent the signed-in user's identity so we need to add few things. First the class must 
implement `IdentityInterface`.

```php
use use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
```

This interface requires few methods to be present so we need to add them.

```php
public static function findIdentity($id): ?self
{
    return static::findOne($id);
}
```

This method allows to find user's identity based on its `id`.

```php
public static function findIdentityByAccessToken($token, $type = null)
{
    throw new NotSupportedException('Method findIdentityByAccessToken is not supported.');
}
```

This method usually allows to find user's identity based on the given token but in our case we will not use it. Interface 
still requires it so it's usual thing to throw exception inside for anyone that would try to use it anyway (remember to 
import the exception in `use yii\base\NotSupportedException;`).

```php
public function getId(): int
{
    return $this->id;
}
```

This method return user's identificator. In our case it is unique integer value as set in database.

```php
public function getAuthKey(): int
{
    return $this->auth_key;
}
```

This method usually returns authentication key used for automatic cookie login. In our case we will use it to validate 
user's JWT access token.

```php
public function validateAuthKey($authKey): bool
{
    return $this->auth_key === (int)$authKey;
}
```

This is authKey validation itself. Nothing sophisticated, just comparing two numbers.

Methods above are required by the interface but we have got important part to implement as well - password validation. 
[Users passwords](https://www.yiiframework.com/doc/guide/2.0/en/security-passwords) should always be hashed (don't 
believe anyone that tells you otherwise) and Yii 2 gives us easy to use `security` component to hash and validate the 
password.

```php
public function validatePassword($rawPassword): bool
{
    return \Yii::$app->getSecurity()->validatePassword($rawPassword, $this->password);
}
``` 

Now we need to configure `user` component. Go back to the `api.php` file in the `config` folder.

```php
    'components' => [
        'user' => [
            'identityClass' => \app\models\User::class,
            'loginUrl' => null,
            'enableSession' => false,
        ],
    ],
```

As you can see we tell `user` component that our `User` model is the identity class. Also `loginUrl` and `enableSession` 
are set to `false` - we are building stateless REST API so these are not needed.

Limiting Users Fields For API
-----------------------------

By default all the [fields](https://www.yiiframework.com/doc/guide/2.0/en/rest-resources#fields) are returned when calling 
API for a resource. In case of our `User` we don't want to return password's hash and auth_key so we need to list all 
the fields that can be safely viewed. Let's add this method in `User` class:

```php
public function fields(): array
{
    return [
        'id',
        'name',
        'createdAt' => 'created_at',
        'updatedAt' => 'updated_at',
    ];
}
```

Fields `id` and `name` will be returned by their names but we aliased `created_at` and `updated_at` here as `createdAt` 
and `updatedAt` to comply with [camelCase](https://en.wikipedia.org/wiki/Camel_case) notation. No other field (like 
`password`) will be returned.

Base API Controller
-------------------

Create `controllers` folder in the `app`folder. Inside create `UserController.php` class file.

```php
<?php

namespace app\controllers;

use yii\rest\Controller;
use yii\rest\OptionsAction;

class UserController extends Controller
{
    public function actions(): array
    {
        return [
            'options' => OptionsAction::class,
        ];
    }
}

```


















Installing And Configuring JWT Component
----------------------------------------

Run this command from `app` folder:

```
composer require bizley/jwt:^2.0
```

When component is installed we need to configure it - go to `api.php` in `config` folder:

```php
    'components' => [
        'jwt' => [
            'class' => \bizley\jwt\Jwt::class,
            'key' => 'Your Token Key Here'
        ],
    ],
```

Replace `Your Token Key Here` with some random long string. Signature of each token will be checked against it.