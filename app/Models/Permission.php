<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get all users that have this permission through their roles.
     */
    public function users()
    {
        return User::whereHas('role', function ($query) {
            $query->whereHas('permissions', function ($q) {
                $q->where('permissions.id', $this->id);
            });
        });
    }

    /**
     * Check if any role has this permission.
     */
    public function isInUse(): bool
    {
        return $this->roles()->exists();
    }

    /**
     * Assign this permission to a role.
     */
    public function assignToRole(Role $role): void
    {
        if (!$role->hasPermission($this->name)) {
            $this->roles()->attach($role->id);
        }
    }

    /**
     * Remove this permission from a role.
     */
    public function removeFromRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Get a list of all available permissions.
     * This can be used to seed the database with initial permissions.
     */
    public static function getDefaultPermissions(): array
    {
        return [
            ['name' => 'manage_users', 'description' => 'Can manage users'],
            ['name' => 'manage_roles', 'description' => 'Can manage roles and permissions'],
            ['name' => 'manage_teams', 'description' => 'Can manage teams'],
            ['name' => 'view_tasks', 'description' => 'Can view tasks'],
            ['name' => 'create_tasks', 'description' => 'Can create tasks'],
            ['name' => 'edit_tasks', 'description' => 'Can edit tasks'],
            ['name' => 'delete_tasks', 'description' => 'Can delete tasks'],
            ['name' => 'manage_task_settings', 'description' => 'Can manage task settings'],
            ['name' => 'view_reports', 'description' => 'Can view reports'],
            ['name' => 'manage_system', 'description' => 'Can manage system settings'],
            ['name' => 'view_audit_logs', 'description' => 'Can view audit logs'],
            ['name' => 'manage_templates', 'description' => 'Can manage task templates'],
        ];
    }
}
