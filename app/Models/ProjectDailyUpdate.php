<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDailyUpdate extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'log_date',
        'log_time',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function attachments()
    {
        return $this->hasMany(ProjectDailyUpdateAttachment::class, 'project_daily_update_id');
    }
}
