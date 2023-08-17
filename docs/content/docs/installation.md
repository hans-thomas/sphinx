+++
date = "2023-06-26"
draft = false
weight = 9
description = "Installation guidance to install and setup Sphinx."
title = "Installation"
bref= "To install Sphinx, follow the below steps"
toc = false
+++

{{< rawhtml >}}
<p><img alt="sphinx banner" src="/img/banner.png"></p>
{{< /rawhtml >}}

[![codecov](https://codecov.io/gh/hans-thomas/sphinx/branch/master/graph/badge.svg?token=X1D6I0JLSZ)](https://codecov.io/gh/hans-thomas/sphinx)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/hans-thomas/sphinx/php.yml)
![GitHub top language](https://img.shields.io/github/languages/top/hans-thomas/sphinx)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/hans-thomas/sphinx)
![StyleCi](https://github.styleci.io/repos/464496173/shield?style=plastic)

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

All sets.

## Role model

Sphinx expects the role model of your project, contains requested method
on `Hans\Sphinx\Models\Contracts\RoleMethods` interface class. So, you should implement this contract and for your
convenient, there is the `Hans\Sphinx\Models\Traits\RoleMethods` trait class that contains all the methods that you must
implement.

```
use Hans\Sphinx\Models\Contracts\RoleMethods as RoleContract;
use Hans\Sphinx\Models\Traits\RoleMethods;
use Spatie\Permission\Models\Role;

class RoleDelegate extends Role implements RoleContract {
use RoleMethods;

// ...
}
```

In addition, your role model should have a `version` column.

```
$table->unsignedInteger('version')->default(1);
```

If your using Spatie/laravel-permission package, you can use this migration class.

```
<?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::table(
                $tableName = config('permission.table_names.roles'),
                function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'version')) {
                        $table->unsignedInteger('version')->default(1);
                    }
                }
            );
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table(
                $tableName = config('permission.table_names.roles'),
                function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'version')) {
                        $table->dropColumn('version');
                    }
                }
            );
        }
    };

```