<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid;

    protected $fillable = [
        'username',
        'email',
        'password_hash',
        'role_id',
        'full_name',
        'avatar',
        'email_verified',
        'two_factor_enabled',
        'two_factor_secret',
        'status',
        'last_login',
    ];

    protected $hidden = [
        'password_hash',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'last_login' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user's sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get the user's password reset requests.
     */
    public function passwordResets(): HasMany
    {
        return $this->hasMany(PasswordReset::class);
    }

    /**
     * Get the teams that the user leads.
     */
    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
    }

    /**
     * Get the teams that the user is a member of.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members');
    }

    /**
     * Get the tasks created by the user.
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    /**
     * Get the tasks assigned to the user.
     */
    public function assignedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_assignees');
    }

    /**
     * Get the user's settings.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the user's activity logs.
     */
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Check if the user has a specific permission through their role.
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->role->permissions()->where('name', $permissionName)->exists();
    }

    /**
     * Check if the user is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the password hash attribute.
     */
    public function getPasswordAttribute(): string
    {
        return $this->password_hash;
    }

    /**
     * Set the password hash attribute.
     */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password_hash'] = bcrypt($value);
    }
}
