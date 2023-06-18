<?php

    namespace App\Models;

    // use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Hans\Horus\HasPermissions;
    use Hans\Horus\HasRoles;
    use Hans\Horus\Models\Traits\HasRelations;
    use Hans\Sphinx\Traits\SphinxTrait;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    class User extends Authenticatable {
        use HasFactory, Notifiable;

        use SphinxTrait, SphinxTrait {
            SphinxTrait::booted as private handleCaching;
        }
        use HasRoles, HasPermissions, HasRelations;

        /**
         * The attributes that are mass assignable.
         *
         * @var array<int, string>
         */
        protected $fillable = [
            'name',
            'email',
            'password',
            'version',
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
            'password'          => 'encrypted',
        ];

        protected static function booted() {
            self::handleCaching();
        }


        public function getDeviceLimit(): int {
            return 2;
        }

        public function extract(): array {
            return [
                'name'    => $this->name,
                'email'   => $this->email,
                'version' => $this->getVersion(),
            ];
        }

        public function username(): string {
            return 'email';
        }

        public function extractRole(): ?array {
            return $this->roles()->first()?->toArray();
        }

        public function extractPermissions(): array {
            return $this->getAllPermissions()->toArray();
        }
    }
