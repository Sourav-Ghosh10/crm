<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDailyUpdateAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_daily_update_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    public function dailyUpdate()
    {
        return $this->belongsTo(ProjectDailyUpdate::class, 'project_daily_update_id');
    }
}
