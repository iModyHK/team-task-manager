<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskLabel extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'label_name',
        'label_color',
    ];

    /**
     * Get the tasks with this label.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'label_id');
    }

    /**
     * Get default task labels.
     */
    public static function getDefaultLabels(): array
    {
        return [
            ['label_name' => 'bug', 'label_color' => '#FF0000'],
            ['label_name' => 'feature', 'label_color' => '#00FF00'],
            ['label_name' => 'enhancement', 'label_color' => '#0000FF'],
            ['label_name' => 'documentation', 'label_color' => '#FFFF00'],
            ['label_name' => 'question', 'label_color' => '#FF00FF'],
            ['label_name' => 'help wanted', 'label_color' => '#00FFFF'],
        ];
    }

    /**
     * Scope a query to find labels by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('label_name', 'like', "%{$name}%");
    }

    /**
     * Get the number of tasks using this label.
     */
    public function getTaskCount(): int
    {
        return $this->tasks()->count();
    }
}
