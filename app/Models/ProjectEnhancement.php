<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectEnhancement extends Model
{
    protected $fillable = ['project_id', 'user_id', 'description', 'time_estimate', 'attachment_path', 'attachment_name'];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
