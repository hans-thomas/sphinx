+++
date = "2023-06-26"
draft = false
weight = 9
description = "Installation guidance to install and setup Sphinx."
title = "Installation"
bref= "To install Sphinx, follow the below steps."
toc = false
+++

{{< rawhtml >}}
<p><img alt="sphinx banner" src="/img/banner.png"></p>
{{< /rawhtml >}}

[![codecov](https://codecov.io/gh/hans-thomas/sphinx/branch/master/graph/badge.svg?token=X1D6I0JLSZ)](https://codecov.io/gh/hans-thomas/sphinx)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/hans-thomas/sphinx/php.yml)
![GitHub top language](https://img.shields.io/github/languages/top/hans-thomas/sphinx)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/hans-thomas/sphinx)

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
    'sphinxUsers' => [
        'driver' => 'sphinx',
        'model'  => App\Models\User::class,
    ],
    // ...
],
```

Then, add your guard.

```
'guards' => [
    // ...
    'jwt' => [
        'driver'   => 'sphinxJwt',
        'provider' => 'sphinxUsers',
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