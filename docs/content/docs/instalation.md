+++
date = "2023-06-26"
draft = false
weight = 9
description = "Installation guidance to install and setup Sphinx."
title = "Installation"
bref= "To install Sphinx, follow the below steps."
toc = false
+++

Install the package via composer

```
composer require hans-thomas/sphinx
```

Then, publish config file

```
php artisan vendor:publish --tag sphinx-config
```

# Setting up

## Model

First, use `Hans\Sphinx\Traits\SphinxTrait` trait on your model and then implement the abstract methods. Next, inside
your model, make sure to call the `hooks` method in your `booted` method.

```
use SphinxTrait, SphinxTrait {
    SphinxTrait::hooks as private sphinxHooks;
}

protected static function booted() {
    self::sphinxHooks();
}
```

## Auth configuration

First of all, define the provider.

```
'providers' => [
    // ...
    'sphinx' => [
        'driver' => 'SphinxProvider',
        'model'  => App\Models\User::class,
    ],
    // ...
],
```

Then, add the guard.

```
'guards' => [
    // ...
    'jwt' => [
        'driver'   => 'SphinxDriver',
        'provider' => 'SphinxProvider',
    ],
    // ...
],
```

And finally, you can set the `jwt` guard as default.

```
'defaults' => [
    // ...
    'guard'     => 'jwt',
    // ...
],
```

All sets. enjoy!