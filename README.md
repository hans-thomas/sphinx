# Sphinx

it's a jwt-based authentication system for laravel with features like:

- easy-to-use
- integrated with [Horus](https://github.com/hans-thomas/horus)
- based on jwt
- two layers of encrypting tokens
- refresh token
- limiting logged-in users in an account

Sphinx has two layers of encryption. first, creates a token and put general data on it and encrypts using a private
static key. next, creates a token with user's data contains user object, user's role and permissions and encrypts this
token using a dynamic private key which is stores on database.

# Table of contents

- [Configuration](#configuration)
- [Installation](#installation)
- [Usage](#usage)
    - [Setting up the model](#setting-up-the-model)

## Configuration

- `private_key`: tokens will encrypt using this static key in layer one.
- `expired_at`: tokens expiration time.
- `refreshExpired_at`: refresh token expiration time.
- `model`: your authenticatable model class.

## Installation

1. install the package via composer:

```shell
composer require hans-thomas/sphinx
```

2. publish config file

```shell
php artisan vendor:publish --tag sphinx-config
```

## Usage

### Setting up the model

first, use `HasRoles`, `HasRelations` and `SphinxTrait`. then call `handleCaching` method in `booted` model's method.
next, you should implement `getDeviceLimit`, `extract` and `username` abstract methods.

```php
namespace App\Models;

use Hans\Horus\HasRoles;
use Hans\Horus\Models\Traits\HasRelations;
use Hans\Sphinx\Traits\SphinxTrait;

class User extends Authenticatable
{
    use HasRoles, HasRelations;
    use SphinxTrait, SphinxTrait {
        SphinxTrait::booted as private handleCaching;
    }

    protected static function booted() {
        self::handleCaching();
    }

    public function getDeviceLimit(): int {
        return 2;
    }

    public function extract(): array {
        return [
            'name' => $this->name,
        ];
    }

    public static function username(): string {
        return 'email';
    }
}
```

- `getDeviceLimit`: determines that how many devices can log in to an account. for example, if you set the limit 2, when
  the user logged-in using a third device, then the first token will be expired.
- `extract`: you can determine what attributes of users should be in the token.
- `username`: the field which users should authenticate using that.

### Create tokens

when user get logged-in, you should create a session for that.

```php
$session = capture_session();
```

then, you can create tokens.

```php
app(Hans\Sphinx\Contracts\SphinxContract)->session( $session )->create( $user )->accessToken(); // returns access token
app(Hans\Sphinx\Contracts\SphinxContract)->session( $session )->createRefreshToken( $user )->refreshToken(); // returns refresh token
```