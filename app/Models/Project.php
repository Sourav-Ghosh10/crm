<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Project extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'codec_projects';

    protected $fillable = [
        'customer_id',
        'project_name',
        'client_name',
        'client_email',
        'client_phone',
        'customer_alt_phone',
        'customer_company',
        'client_address',
        'client_gst',
        'base_amount',
        'tax_rate',
        'total_amount',
        'project_currency',
        'status',
    ];

    public $timestamps = false; // Using custom created_at with default current_timestamp()

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class, 'project_id');
    }

    public function projectAmounts()
    {
        return $this->hasMany(ProjectAmount::class, 'project_id');
    }

    public function crmDetails()
    {
        return $this->hasOne(CrmProject::class, 'project_id');
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'project_assignments', 'project_id', 'user_id')->withTimestamps();
    }

    public function dailyUpdates()
    {
        return $this->hasMany(ProjectDailyUpdate::class, 'project_id');
    }

    public function activities()
    {
        return $this->hasMany(ProjectActivity::class, 'project_id');
    }

    public function enhancements()
    {
        return $this->hasMany(ProjectEnhancement::class, 'project_id');
    }

    public function messages()
    {
        return $this->hasMany(ProjectMessage::class, 'project_id');
    }

    protected static function booted()
    {
        static::creating(function ($project) {
            $lastProject = static::orderBy('id', 'desc')->first();
            $nextId = $lastProject ? $lastProject->id + 1 : 1;
            $project->customer_id = 'CUST-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        });
    }
}
