<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Task extends Model
{
    use SoftDeletes, Auditable;
    protected $fillable = [
        'user_id',
        'client_id',
        'title',
        'description',
        'location',
        'category',
        'due_at',
        'is_completed',
        'priority',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
