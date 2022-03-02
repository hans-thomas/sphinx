<?php

namespace App\Models;

use Hans\Horus\HasRoles;
use Hans\Horus\Models\Traits\HasRelations;
use Hans\Sphinx\Traits\SphinxTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use HasRoles, HasRelations;
    use SphinxTrait, SphinxTrait {
        SphinxTrait::booted as private handleCaching;
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
