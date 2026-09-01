<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasPermissions;

    /**
     * Role constants (legacy support)
     */
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_MANAGER = 'Manager';
    public const ROLE_TEAM_LEAD = 'Team Lead';
    public const ROLE_AGENT = 'Agent';
    public const ROLE_BEADER = 'Beader';

    /**
     * Available roles (legacy support)
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_MANAGER => 'Manager',
            self::ROLE_TEAM_LEAD => 'Team Lead',
            self::ROLE_AGENT => 'Agent',
            self::ROLE_BEADER => 'Beader',
        ];
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'fcm_token',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'agent_id');
    }

    /**
     * Check if user is Admin / Super Admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('super-admin') || $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Manager
     */
    public function isManager(): bool
    {
        return $this->hasRole('manager') || $this->role === self::ROLE_MANAGER;
    }

    /**
     * Check if user is Agent
     */
    public function isAgent(): bool
    {
        if ($this->isAdmin() || $this->isManager() || $this->isTeamLead() || $this->hasRole('project-manager')) {
            return false;
        }
        return $this->hasRole('agent') || $this->role === self::ROLE_AGENT || $this->role === self::ROLE_BEADER;
    }

    /**
     * Check if user is Team Lead
     */
    public function isTeamLead(): bool
    {
        return $this->hasRole('team-lead') || $this->role === self::ROLE_TEAM_LEAD;
    }

    /**
     * Check if user is Beader
     */
    public function isBeader(): bool
    {
        return $this->role === self::ROLE_BEADER;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        $normalized = match ($role) {
            'Admin', 'Administrator' => 'super-admin',
            'Manager' => 'manager',
            'Team Lead' => 'team-lead',
            'Agent' => 'agent',
            'Beader' => 'agent',
            default => $role
        };
        return $this->roles->contains('name', $normalized) || ($this->role === $role);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $r) {
            if ($this->hasRole($r)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has at least Admin, Manager, Team Lead or Supervisor role
     */
    public function isManagement(): bool
    {
        return $this->isAdmin() || $this->isManager() || $this->isTeamLead() || $this->hasRole('supervisor');
    }

    /**
     * Check if user can access the Projects section.
     */
    public function canAccessProjects(): bool
    {
        return $this->hasPermissionTo('projects.view') || $this->isAdmin() || $this->isManager();
    }

    /**
     * Check if user can access the Users section.
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermissionTo('users.view') || $this->isAdmin() || $this->isManager() || $this->hasRole('project-manager');
    }

    /**
     * Get role label
     */
    public function getRoleLabelAttribute(): string
    {
        $dbRole = $this->roles()->orderBy('hierarchy_level', 'asc')->first();
        if ($dbRole) {
            return $dbRole->display_name;
        }
        return self::getRoles()[$this->role] ?? $this->role;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function chatRooms()
    {
        return $this->belongsToMany(ChatRoom::class, 'chat_room_members', 'user_id', 'chat_room_id')
            ->withPivot('last_read_at');
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }
}
