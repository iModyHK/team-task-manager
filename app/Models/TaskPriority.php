<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskPriority extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the tasks with this priority.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'priority_id');
    }

    /**
     * Get default task priorities.
     */
    public static function getDefaultPriorities(): array
    {
        return [
            ['name' => 'low'],
            ['name' => 'medium'],
            ['name' => 'high'],
            ['name' => 'urgent'],
            ['name' => 'critical'],
        ];
    }
}
