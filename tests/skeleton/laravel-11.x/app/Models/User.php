<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Hans\Sphinx\Traits\SphinxTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SphinxTrait, SphinxTrait {
        SphinxTrait::hooks as private handleCaching;
    }
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        self::handleCaching();
    }
    public function getDeviceLimit(): int
    {
        return 2;
    }

    public function extract(): array
    {
        return [
            'name'    => $this->name,
            'email'   => $this->email,
            'version' => $this->getVersion(),
        ];
    }

    public function username(): string
    {
        return 'email';
    }

    public function extractRole(): ?array
    {
        return $this->roles()->first()?->toArray();
    }

    public function extractPermissions(): array
    {
        return $this->getAllPermissions()->toArray();
    }
}
