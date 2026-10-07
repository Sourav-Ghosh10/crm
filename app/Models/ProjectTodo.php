<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'parent_id',
        'description',
        'duration_value',
        'duration_type',
        'status',
        'user_id',
        'recurrence_type',
        'recurrence_days',
        'recurrence_dates',
        'recurrence_yearly_dates',
    ];

    protected $casts = [
        'recurrence_days' => 'array',
        'recurrence_dates' => 'array',
        'recurrence_yearly_dates' => 'array',
    ];


    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProjectTodo::class, 'parent_id');
    }

    public function instances()
    {
        return $this->hasMany(ProjectTodo::class, 'parent_id');
    }
}
