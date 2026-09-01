<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTodo extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'description',
        'duration_value',
        'duration_type',
        'status',
        'user_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
