<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'hierarchy_level',
        'description',
    ];

    /**
     * Many-to-Many connection with Permissions.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    /**
     * Many-to-Many connection with Users.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }
}
