<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Client extends Model
{
    use SoftDeletes, Auditable;
    
    protected $appends = ['status_color'];

    protected $fillable = [
        'customer_number',
        'full_name',
        'phone',
        'alternate_phone',
        'email',
        'company_name',
        'tags',
        'status',
        'agent_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Status constants
     */
    public const STATUS_NEW = 'New';
    public const STATUS_FOLLOW_UP = 'Follow-up';
    public const STATUS_IN_PROGRESS = 'In Progress';
    public const STATUS_CLOSED_WON = 'Closed - Won';
    public const STATUS_CLOSED_LOST = 'Closed - Lost';
    public const STATUS_ON_HOLD = 'On Hold';

    /**
     * Available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NEW => 'New',
            self::STATUS_FOLLOW_UP => 'Follow-up',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_CLOSED_WON => 'Closed - Won',
            self::STATUS_CLOSED_LOST => 'Closed - Lost',
            self::STATUS_ON_HOLD => 'On Hold',
        ];
    }

    /**
     * Get status color class
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_NEW => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            self::STATUS_FOLLOW_UP => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            self::STATUS_IN_PROGRESS => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
            self::STATUS_CLOSED_WON => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            self::STATUS_CLOSED_LOST => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            self::STATUS_ON_HOLD => 'bg-gray-100 text-gray-700 dark:bg-gray-700/30 dark:text-gray-400',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the call logs for this client
     */
    public function callLogs()
    {
        return $this->hasMany(CallLog::class)->orderBy('call_start_time', 'desc');
    }
}
