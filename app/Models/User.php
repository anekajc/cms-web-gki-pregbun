<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\RecordsActivity;
use App\Support\Access;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, RecordsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'generated_password',
        'must_change_password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        // Hidden by default so it never leaks into the shared auth.user prop;
        // the user-management list opts in via makeVisible().
        'generated_password',
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
            'generated_password' => 'encrypted',
            'must_change_password' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /** Resolved once per request; see accessKeys(). */
    private ?array $resolvedAccessKeys = null;

    /**
     * Leaf access keys this user holds. Admins implicitly hold every key.
     *
     * @return list<string>
     */
    public function accessKeys(): array
    {
        return $this->resolvedAccessKeys ??= $this->isAdmin()
            ? Access::allKeys()
            : $this->permissions()->pluck('permission')->all();
    }

    public function canAccess(string $key): bool
    {
        return $this->isAdmin() || in_array($key, $this->accessKeys(), true);
    }

    /**
     * True when the user holds $prefix itself or any key beneath it, so
     * "dashboard" matches "dashboard.warta" and "ibadah.kebaktian" matches
     * "ibadah.kebaktian.3" — but "pelayanan.1" does not match "pelayanan.12".
     */
    public function canAccessAny(string $prefix): bool
    {
        // Admins pass even when a page has no sections yet (e.g. no pelayanan rows).
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($this->accessKeys() as $key) {
            if ($key === $prefix || str_starts_with($key, $prefix.'.')) {
                return true;
            }
        }

        return false;
    }
}
