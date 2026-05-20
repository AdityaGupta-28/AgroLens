<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Traits\HasPermissions;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasPermissions, Notifiable, SoftDeletes;

    public $tempAdminAttributes = [];

    protected static function booted()
    {
        static::addGlobalScope('withAdminDetails', function ($builder) {
            $builder->leftJoin('admins', 'users.id', '=', 'admins.user_id')
                ->select(
                    'users.*',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(admins.role, "public_viewer") as role'),
                    \Illuminate\Support\Facades\DB::raw('COALESCE(admins.is_active, 1) as is_active'),
                    'admins.api_token as api_token',
                    \Illuminate\Support\Facades\DB::raw('COALESCE(admins.api_token_hits, 0) as api_token_hits')
                );
        });

        static::saving(function ($user) {
            foreach (['role', 'is_active', 'api_token', 'api_token_hits'] as $field) {
                if (array_key_exists($field, $user->attributes)) {
                    $user->tempAdminAttributes[$field] = $user->attributes[$field];
                    unset($user->attributes[$field]);
                }
            }
        });

        static::saved(function ($user) {
            if (!empty($user->tempAdminAttributes)) {
                $role = $user->tempAdminAttributes['role'] ?? null;
                $roleVal = $role instanceof \App\Enums\UserRole ? $role->value : $role;

                if ($roleVal === \App\Enums\UserRole::PublicViewer->value || $roleVal === 'public_viewer') {
                    $user->admin()->delete();
                } else {
                    $admin = $user->admin ?: new Admin();
                    $admin->user_id = $user->id;
                    if (isset($user->tempAdminAttributes['role'])) {
                        $admin->role = $roleVal;
                    }
                    if (isset($user->tempAdminAttributes['is_active'])) {
                        $admin->is_active = (bool)$user->tempAdminAttributes['is_active'];
                    }
                    if (isset($user->tempAdminAttributes['api_token'])) {
                        $admin->api_token = $user->tempAdminAttributes['api_token'];
                    }
                    if (isset($user->tempAdminAttributes['api_token_hits'])) {
                        $admin->api_token_hits = (int)$user->tempAdminAttributes['api_token_hits'];
                    }
                    $admin->save();
                }

                // Restore attributes back for in-memory continuity
                foreach ($user->tempAdminAttributes as $field => $val) {
                    $user->attributes[$field] = $val;
                }
                $user->tempAdminAttributes = [];
            }
        });
    }

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'locale',
        'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
        'is_active', 'last_login_at', 'api_token', 'api_token_hits',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'api_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function admin(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Admin::class);
    }

    // Accessors & Mutators
    public function getRoleAttribute()
    {
        if (isset($this->tempAdminAttributes['role'])) {
            $val = $this->tempAdminAttributes['role'];
            return $val instanceof \App\Enums\UserRole ? $val : \App\Enums\UserRole::from($val);
        }

        $attr = $this->attributes['role'] ?? null;
        if ($attr !== null) {
            return $attr instanceof \App\Enums\UserRole ? $attr : \App\Enums\UserRole::from($attr);
        }

        return $this->admin ? \App\Enums\UserRole::from($this->admin->role) : \App\Enums\UserRole::PublicViewer;
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;
    }

    public function getIsActiveAttribute(): bool
    {
        if (isset($this->tempAdminAttributes['is_active'])) {
            return (bool)$this->tempAdminAttributes['is_active'];
        }

        $attr = $this->attributes['is_active'] ?? null;
        if ($attr !== null) {
            return (bool)$attr;
        }

        return $this->admin ? (bool)$this->admin->is_active : true;
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = $value;
    }

    public function getApiTokenAttribute()
    {
        if (isset($this->tempAdminAttributes['api_token'])) {
            return $this->tempAdminAttributes['api_token'];
        }

        $attr = $this->attributes['api_token'] ?? null;
        if ($attr !== null) {
            return $attr;
        }

        return $this->admin ? $this->admin->api_token : null;
    }

    public function setApiTokenAttribute($value)
    {
        $this->attributes['api_token'] = $value;
    }

    public function getApiTokenHitsAttribute(): int
    {
        if (isset($this->tempAdminAttributes['api_token_hits'])) {
            return (int)$this->tempAdminAttributes['api_token_hits'];
        }

        $attr = $this->attributes['api_token_hits'] ?? null;
        if ($attr !== null) {
            return (int)$attr;
        }

        return $this->admin ? (int)$this->admin->api_token_hits : 0;
    }

    public function setApiTokenHitsAttribute($value)
    {
        $this->attributes['api_token_hits'] = $value;
    }

    public function newEloquentBuilder($query): UserBuilder
    {
        return new UserBuilder($query);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
