# Sphinx

<p align="center"><img alt="sphinx Logo" src="assets/sphinx-banner.png"></p>

[![codecov](https://codecov.io/gh/hans-thomas/sphinx/branch/master/graph/badge.svg?token=X1D6I0JLSZ)](https://codecov.io/gh/hans-thomas/sphinx)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/hans-thomas/sphinx/php.yml)
![GitHub top language](https://img.shields.io/github/languages/top/hans-thomas/sphinx)
![GitHub release (latest by date)](https://img.shields.io/github/v/release/hans-thomas/sphinx)
![StyleCi](https://github.styleci.io/repos/464496173/shield?style=plastic)

Sphinx is a feature reach Jwt-based authentication system that make zero queries to database during authorization.

- Customizable
- Integration support with [Horus](https://github.com/hans-thomas/horus)
- Based on Jwt
- Two layers of encryption
- Refresh token support
- Logged-in users in one account limitation

## Installation

Install the package via composer.

```
composer require hans-thomas/sphinx
```

Then, publish config file.

```
php artisan vendor:publish --tag sphinx-config
```

## Setting up

### Model

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

### Auth configuration

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

Read more about Sphinx in [documentation](https://docs-sphinx.vercel.app/) website.

## Contributing

1. Fork it!
2. Create your feature branch: git checkout -b my-new-feature
3. Commit your changes: git commit -am 'Add some feature'
4. Push to the branch: git push origin my-new-feature
5. Submit a pull request ❤️


Support
-------

- [Documentation](https://docs-sphinx.vercel.app/)
- [Report bugs](https://github.com/hans-thomas/sphinx)
