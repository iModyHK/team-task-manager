<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'leader_id',
    ];

    /**
     * Get the team leader.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the team members.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withTimestamps();
    }

    /**
     * Get the tasks associated with this team.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(User $user): void
    {
        if (!$this->hasMember($user)) {
            $this->members()->attach($user->id);
        }
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(User $user): void
    {
        $this->members()->detach($user->id);
    }

    /**
     * Check if a user is a member of the team.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if a user is the team leader.
     */
    public function isLeader(User $user): bool
    {
        return $this->leader_id === $user->id;
    }

    /**
     * Get the total number of active tasks for the team.
     */
    public function getActiveTasksCount(): int
    {
        return $this->tasks()
            ->whereNull('archived_at')
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get the total number of completed tasks for the team.
     */
    public function getCompletedTasksCount(): int
    {
        return $this->tasks()
            ->whereHas('status', function ($query) {
                $query->where('status_name', 'completed');
            })
            ->count();
    }

    /**
     * Get team statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_members' => $this->members()->count(),
            'active_tasks' => $this->getActiveTasksCount(),
            'completed_tasks' => $this->getCompletedTasksCount(),
            'total_tasks' => $this->tasks()->count(),
        ];
    }

    /**
     * Scope a query to only include teams where the user is a member or leader.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('leader_id', $user->id)
            ->orWhereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
    }
}
