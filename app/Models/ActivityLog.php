<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'activity',
        'details',
        'timestamp',
    ];

    protected $casts = [
        'details' => 'json',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Format the activity details for display.
     */
    public function getFormattedDetails(): string
    {
        if (empty($this->details)) {
            return '';
        }

        $details = json_decode($this->details, true);
        $output = [];

        foreach ($details as $key => $value) {
            // Format the key
            $key = ucwords(str_replace('_', ' ', $key));
            
            // Format the value based on its type
            if (is_array($value)) {
                $value = json_encode($value, JSON_PRETTY_PRINT);
            } elseif (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            $output[] = "{$key}: {$value}";
        }

        return implode("\n", $output);
    }

    /**
     * Get the time elapsed since the activity occurred.
     */
    public function getTimeElapsed(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Scope a query to only include activities for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include activities within a date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include activities matching a search term.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('activity', 'like', "%{$term}%")
                ->orWhere('details', 'like', "%{$term}%");
        });
    }

    /**
     * Get activity statistics for a given period.
     */
    public static function getStatistics($startDate = null, $endDate = null): array
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total_activities' => $query->count(),
            'activities_by_type' => $query->select('activity')
                ->selectRaw('count(*) as count')
                ->groupBy('activity')
                ->pluck('count', 'activity')
                ->toArray(),
            'most_active_users' => $query->select('user_id')
                ->selectRaw('count(*) as count')
                ->groupBy('user_id')
                ->with('user:id,username')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'username' => $item->user->username,
                        'count' => $item->count,
                    ];
                }),
        ];
    }
}
