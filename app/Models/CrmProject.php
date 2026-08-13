<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmProject extends Model
{
    use HasFactory;

    protected $table = 'project_details';

    protected $fillable = [
        'project_id',
        'status',
        'description',
        'daily_updates',
        'start_date',
        'end_date',
        'log_hours',
        'assignee_name'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
