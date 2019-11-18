Yii 2 REST application with JWT authorization and Codeception tests: Tutorial
=============================================================================

In this tutorial you will learn how to create simple REST-only application with Yii 2 framework.

Some assumptions are made here:
- PHP is available in version at least 7.3.
- You are familiar with [Composer](https://getcomposer.org) and it's installed globally.
  
Please make proper adjustments if necessary.

> Tip: Finished product of this tutorial is available at https://github.com/bizley/yii2-rest-app but I recommend to
> continue reading and building the app along with the guide.

> Tip: Tutorial is also available in [Polish](https://github.com/bizley/yii2-rest-app/guide/guide-pl.md).

Preparation
-----------

Create a folder where you want your project to be stored - in this tutorial I will use `app` for its name. In the folder 
create new file named `composer.json`. We will use Composer to download the Yii 2 framework and all other required packages.

The content of `composer.json` should be as follows:

```
{
  "require": {
    "yiisoft/yii2": "2.0.29"
  },
  "replace": {
      "bower-asset/jquery": ">=1.11.0",
      "bower-asset/inputmask": ">=3.2.0",
      "bower-asset/punycode": ">=1.3.0",
      "bower-asset/yii2-pjax": ">=2.0.0"
  },
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

command.

> Notice: Yii version in the tutorial is locked as 2.0.29. You can change it in your `composer.json` to `"~2.0.29"` 
> (mind the tilde `~`) to fetch newer version (if available) with `composer update` command.