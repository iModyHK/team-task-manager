<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'action',
        'details',
        'timestamp',
        'ip_address',
    ];

    protected $casts = [
        'details' => 'json',
        'timestamp' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Format the audit details for display.
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
                if (isset($value['old']) && isset($value['new'])) {
                    $value = "Changed from '{$value['old']}' to '{$value['new']}'";
                } else {
                    $value = json_encode($value, JSON_PRETTY_PRINT);
                }
            } elseif (is_bool($value)) {
                $value = $value ? 'Yes' : 'No';
            }

            $output[] = "{$key}: {$value}";
        }

        return implode("\n", $output);
    }

    /**
     * Get the time elapsed since the audit event occurred.
     */
    public function getTimeElapsed(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the severity level of the audit event.
     */
    public function getSeverityLevel(): string
    {
        $criticalActions = [
            'login_failed',
            'password_reset',
            'role_changed',
            'permissions_changed',
            'user_deleted',
            'security_setting_changed',
        ];

        $warningActions = [
            'login_success',
            'logout',
            'user_created',
            'user_updated',
            'task_deleted',
        ];

        if (in_array($this->action, $criticalActions)) {
            return 'critical';
        } elseif (in_array($this->action, $warningActions)) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Scope a query to only include audit logs for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include audit logs within a date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include audit logs matching a search term.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('action', 'like', "%{$term}%")
                ->orWhere('details', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    /**
     * Scope a query to only include critical events.
     */
    public function scopeCritical($query)
    {
        return $query->whereIn('action', [
            'login_failed',
            'password_reset',
            'role_changed',
            'permissions_changed',
            'user_deleted',
            'security_setting_changed',
        ]);
    }

    /**
     * Get security statistics for a given period.
     */
    public static function getSecurityStats($startDate = null, $endDate = null): array
    {
        $query = self::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $failedLogins = $query->where('action', 'login_failed')->count();
        $successfulLogins = $query->where('action', 'login_success')->count();
        $passwordResets = $query->where('action', 'password_reset')->count();
        $permissionChanges = $query->where('action', 'permissions_changed')->count();

        return [
            'failed_logins' => $failedLogins,
            'successful_logins' => $successfulLogins,
            'login_success_rate' => $successfulLogins + $failedLogins > 0 
                ? round(($successfulLogins / ($successfulLogins + $failedLogins)) * 100, 2)
                : 0,
            'password_resets' => $passwordResets,
            'permission_changes' => $permissionChanges,
            'suspicious_ips' => $query->where('action', 'login_failed')
                ->select('ip_address')
                ->selectRaw('count(*) as attempts')
                ->groupBy('ip_address')
                ->having('attempts', '>', 5)
                ->get(),
        ];
    }
}
