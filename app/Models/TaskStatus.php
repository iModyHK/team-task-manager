<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'status_name',
        'status_color',
    ];

    /**
     * Get the tasks with this status.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }

    /**
     * Get default task statuses.
     */
    public static function getDefaultStatuses(): array
    {
        return [
            ['status_name' => 'pending', 'status_color' => '#FFA500'],
            ['status_name' => 'in_progress', 'status_color' => '#0000FF'],
            ['status_name' => 'completed', 'status_color' => '#008000'],
            ['status_name' => 'blocked', 'status_color' => '#FF0000'],
            ['status_name' => 'on_hold', 'status_color' => '#808080'],
        ];
    }
}
