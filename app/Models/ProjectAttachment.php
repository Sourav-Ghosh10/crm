<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAttachment extends Model
{
    use HasFactory;

    protected $table = 'project_attachments';

    protected $fillable = [
        'project_id',
        'user_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get human-readable file size
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '';
        }
        $bytes = (int) $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 1) . ' ' . $units[$i];
    }
}
