<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TaskAttachment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'task_id',
        'filename',
        'file_hash',
        'scanned',
        'scan_result',
    ];

    protected $casts = [
        'scanned' => 'boolean',
    ];

    /**
     * Get the task that owns the attachment.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the full path of the attachment.
     */
    public function getPath(): string
    {
        return Storage::disk('attachments')->path($this->filename);
    }

    /**
     * Get the URL of the attachment.
     */
    public function getUrl(): string
    {
        return Storage::disk('attachments')->url($this->filename);
    }

    /**
     * Calculate file hash.
     */
    public function calculateHash(): string
    {
        return hash_file('sha256', $this->getPath());
    }

    /**
     * Perform virus scan on the file.
     * This is a stub that should be replaced with actual antivirus integration.
     */
    public function performVirusScan(): bool
    {
        try {
            // Simulate virus scanning
            $this->scanned = true;
            
            // In a real implementation, you would integrate with an antivirus API
            // For now, we'll do a simple file existence and mime type check
            if (Storage::disk('attachments')->exists($this->filename)) {
                $mimeType = Storage::disk('attachments')->mimeType($this->filename);
                $fileSize = Storage::disk('attachments')->size($this->filename);
                
                // Basic security checks
                $isSafe = $this->isAllowedMimeType($mimeType) && $fileSize <= config('attachments.max_size', 10485760); // 10MB default
                
                $this->scan_result = $isSafe ? 'clean' : 'suspicious';
                $this->save();
                
                return $isSafe;
            }
            
            $this->scan_result = 'file_not_found';
            $this->save();
            return false;
        } catch (\Exception $e) {
            \Log::error('Virus scan failed: ' . $e->getMessage(), [
                'attachment_id' => $this->id,
                'filename' => $this->filename
            ]);
            
            $this->scan_result = 'scan_failed';
            $this->save();
            return false;
        }
    }

    /**
     * Check if the mime type is allowed.
     */
    protected function isAllowedMimeType(string $mimeType): bool
    {
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'image/jpeg',
            'image/png',
            'image/gif',
        ];

        return in_array($mimeType, $allowedTypes);
    }

    /**
     * Delete the file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            Storage::disk('attachments')->delete($attachment->filename);
        });
    }

    /**
     * Check if the file is safe to download.
     */
    public function isSafeToDownload(): bool
    {
        return $this->scanned && $this->scan_result === 'clean';
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getHumanFileSize(): string
    {
        $bytes = Storage::disk('attachments')->size($this->filename);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
