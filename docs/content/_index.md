---
title: Introducing Sphinx
weight: 1
---

# Introduce

It's a jwt-based authentication system for laravel with features like:

- Easy-to-use
- Based on jwt
- Two layers of encrypting tokens
- Refresh token
- Limiting logged-in users for one account

Sphinx has two layers of encryption. first, creates a wrapper token and puts general data on it and encrypts using a
private static key. next, creates a inner token using user's data such as user's role and permissions. then
encrypts this token using a dynamic private key which is randomly generated and stores on database. finally, puts the
inner token inside the wrapper token and give the final token to the user.

# Installation

Install the package via composer

```shell
composer require hans-thomas/sphinx
```

Then, publish config file

```shell
php artisan vendor:publish --tag sphinx-config
```

# Setting up

## Model

First, use `Hans\Sphinx\Traits\SphinxTrait` trait on your model and then implement the abstract methods. Next, inside
your model, make sure to call the `hooks` method in your `booted` method.

```php
use SphinxTrait, SphinxTrait {
    SphinxTrait::hooks as private sphinxHooks;
}

protected static function booted() {
    self::sphinxHooks();
}
```

## Auth configuration

First of all, define the provider.

```php
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

```php
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

```php
'defaults' => [
    // ...
    'guard'     => 'jwt',
    // ...
],
```

All sets. enjoy!