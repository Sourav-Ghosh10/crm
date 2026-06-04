<?php

namespace App\Traits;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

trait HasPermissions
{
    /**
     * Many-to-Many connection with Roles.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * Checks if the user is attached to a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles->contains('name', $role);
    }

    /**
     * Checks if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return !empty(array_intersect($this->roles->pluck('name')->toArray(), $roles));
    }

    /**
     * High performance permission checking using dynamic memory caching.
     * Checks permissions at O(1) complexity.
     */
    public function hasPermissionTo(string $permission): bool
    {
        $cacheKey = "user_permissions_{$this->id}";

        $cachedPermissions = Cache::remember($cacheKey, now()->addHours(8), function () {
            return $this->roles()
                ->with('permissions')
                ->get()
                ->flatMap(function ($role) {
                    return $role->permissions->pluck('name');
                })
                ->unique()
                ->toArray();
        });

        // Super Admin inherits all rights instantly
        if (in_array('super-admin', $this->roles->pluck('name')->toArray())) {
            return true;
        }

        return in_array($permission, $cachedPermissions);
    }

    /**
     * Safely clear cache when user permissions or roles are mutated.
     */
    public function clearPermissionsCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }
}
