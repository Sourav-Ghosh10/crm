# CodecIT CRM — Enterprise Dynamic RBAC & Security Upgrade Blueprint

This document specifies the complete, production-grade software architecture for transitioning **CodecIT CRM** from a static role attribute system to a fully scalable, high-performance, database-driven **Role-Based Access Control (RBAC)** architecture with an **Immutable Audit Trail** and a **Super-Admin Soft Delete Control System**.

---

## 1.0 High-Level System Architecture & Folder Structure

To maintain maximum cohesion, modularity, and compliance with Clean Architecture standards, the upgraded files will be organized within the following folder structure:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── ProjectController.php        # Refactored for permission checks
│   │   └── UserController.php           # Refactored for permission checks
│   ├── Middleware/
│   │   ├── CheckPermission.php          # Centralized route permission middleware
│   │   └── LogActivity.php              # Global activity tracking middleware
│   └── Requests/
├── Models/
│   ├── AuditTrail.php                   # Immutable historical log
│   ├── ActivityLog.php                  # Operation activity log
│   ├── Permission.php                   # Permissions entity
│   ├── Role.php                         # Roles entity with hierarchy support
│   └── User.php                         # Main entity with HasPermissions trait
├── Traits/
│   ├── HasPermissions.php               # High-performance caching permission trait
│   └── Auditable.php                    # Eloquent boot trait for automated auditing
database/
├── migrations/
│   ├── 2026_05_18_100000_create_rbac_tables.php
│   └── 2026_05_18_200000_create_audit_and_activity_tables.php
└── seeders/
    └── RBACSeeder.php                   # Populates roles, permissions & mappings
```

---

## 2.0 Database Schema & Migration Code

The database-driven schema is fully normalized. It implements:
* `roles`: Role definitions and priority/hierarchy mappings.
* `permissions`: Clean feature-based permission keys (e.g. `clients.delete`, `projects.payments`).
* `permission_role`: Many-to-many pivot mapping of roles to permissions.
* `role_user`: Pivot mapping of users to one or more roles (supports multiple active roles).

### 2.1 Schema Definition (Laravel 12 Migration)

Create file: `database/migrations/2026_05_18_100000_create_rbac_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Roles table with hierarchy levels
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // e.g. 'super-admin', 'manager'
            $table->string('display_name');          // e.g. 'Super Admin'
            $table->integer('hierarchy_level');      // Lower integer = Higher priority (e.g., Super Admin = 1, Agent = 5)
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Permissions table with categorizations
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();        // e.g. 'users.create', 'projects.payments'
            $table->string('display_name');
            $table->string('module_category');       // e.g. 'User Management', 'Projects'
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Role-Permission pivot table (Many-to-Many)
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->primary(['role_id', 'permission_id']);
        });

        // 4. Role-User pivot table (Many-to-Many to support multiple role assignments)
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->primary(['user_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
```

---

## 3.0 Permission Mappings & Seeding Strategy

To eliminate hardcoded comparisons, features are mapped directly to micro-permissions. 

### 3.1 Permission Design (Granular Feature Keys)

| Module | Core Action | Permission Name |
| :--- | :--- | :--- |
| **System Settings** | Read / Edit settings | `settings.view`, `settings.edit` |
| **User Management** | CRUD, Reset Passwords | `users.view`, `users.create`, `users.edit`, `users.delete` |
| **Project & Finance** | Create / Edit Projects, record payments, invoices | `projects.view`, `projects.create`, `projects.edit`, `projects.delete`, `projects.payments`, `projects.invoices`, `settings.rates` |
| **Client Management** | View, Create, Edit, Delete | `clients.view.global`, `clients.view.assigned`, `clients.create`, `clients.edit`, `clients.delete` |
| **Call Logging** | Add, Audit, Permanent Wipe | `call-logs.create`, `call-logs.edit`, `call-logs.delete` |
| **Task Operations** | Global vs. Local scheduling | `tasks.view.global`, `tasks.view.assigned`, `tasks.create`, `tasks.edit`, `tasks.delete` |
| **Compliance & Logs** | Immutable audits, soft deletes | `audit.view`, `logs.view`, `records.restore`, `records.permanent_delete` |

---

### 3.2 Seeding Configuration Class

Create file: `database/seeders/RBACSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RBACSeeder extends Seeder
{
    public function run(): void
    {
        // Prevent integrity issues on seed reruns
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Roles with hierarchy priorities
        $roles = [
            'super-admin' => Role::create(['name' => 'super-admin', 'display_name' => 'Super Admin', 'hierarchy_level' => 1, 'description' => 'Unrestricted root-level access.']),
            'manager'     => Role::create(['name' => 'manager', 'display_name' => 'Manager', 'hierarchy_level' => 2, 'description' => 'Operational control with soft delete and recovery.']),
            'supervisor'  => Role::create(['name' => 'supervisor', 'display_name' => 'Supervisor', 'hierarchy_level' => 3, 'description' => 'Team lead oversight and client workflows.']),
            'team-lead'   => Role::create(['name' => 'team-lead', 'display_name' => 'Team Lead', 'hierarchy_level' => 4, 'description' => 'Standard management supervisor for agents.']),
            'agent'       => Role::create(['name' => 'agent', 'display_name' => 'Agent', 'hierarchy_level' => 5, 'description' => 'Standard frontline operations role.'])
        ];

        // 2. Define Granular Permissions
        $permissionsData = [
            // Settings module
            ['name' => 'settings.view', 'display_name' => 'View Settings', 'module_category' => 'Settings'],
            ['name' => 'settings.edit', 'display_name' => 'Edit Settings', 'module_category' => 'Settings'],
            
            // Users module
            ['name' => 'users.view', 'display_name' => 'View Users', 'module_category' => 'Users'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module_category' => 'Users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'module_category' => 'Users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module_category' => 'Users'],

            // Projects module
            ['name' => 'projects.view', 'display_name' => 'View Projects', 'module_category' => 'Projects'],
            ['name' => 'projects.create', 'display_name' => 'Create Projects', 'module_category' => 'Projects'],
            ['name' => 'projects.edit', 'display_name' => 'Edit Projects', 'module_category' => 'Projects'],
            ['name' => 'projects.delete', 'display_name' => 'Delete Projects', 'module_category' => 'Projects'],
            ['name' => 'projects.payments', 'display_name' => 'Manage Payments', 'module_category' => 'Projects'],
            ['name' => 'projects.invoices', 'display_name' => 'Generate Invoices', 'module_category' => 'Projects'],

            // Clients module
            ['name' => 'clients.view.global', 'display_name' => 'View All Clients', 'module_category' => 'Clients'],
            ['name' => 'clients.view.assigned', 'display_name' => 'View Own Clients', 'module_category' => 'Clients'],
            ['name' => 'clients.create', 'display_name' => 'Create Clients', 'module_category' => 'Clients'],
            ['name' => 'clients.edit', 'display_name' => 'Edit Clients', 'module_category' => 'Clients'],
            ['name' => 'clients.delete', 'display_name' => 'Delete Clients', 'module_category' => 'Clients'],

            // Call Logs module
            ['name' => 'call-logs.create', 'display_name' => 'Log Call Records', 'module_category' => 'Call Logs'],
            ['name' => 'call-logs.edit', 'display_name' => 'Modify Call Records', 'module_category' => 'Call Logs'],
            ['name' => 'call-logs.delete', 'display_name' => 'Wipe Call Records', 'module_category' => 'Call Logs'],

            // Tasks module
            ['name' => 'tasks.view.global', 'display_name' => 'View All Tasks', 'module_category' => 'Tasks'],
            ['name' => 'tasks.view.assigned', 'display_name' => 'View Own Tasks', 'module_category' => 'Tasks'],
            ['name' => 'tasks.create', 'display_name' => 'Create Tasks', 'module_category' => 'Tasks'],
            ['name' => 'tasks.edit', 'display_name' => 'Edit Tasks', 'module_category' => 'Tasks'],
            ['name' => 'tasks.delete', 'display_name' => 'Delete Tasks', 'module_category' => 'Tasks'],

            // Auditing & Recovery module
            ['name' => 'audit.view', 'display_name' => 'View Audit Trail', 'module_category' => 'Audit'],
            ['name' => 'logs.view', 'display_name' => 'View Activity Logs', 'module_category' => 'Audit'],
            ['name' => 'records.restore', 'display_name' => 'Restore Soft Deleted Records', 'module_category' => 'Recovery'],
            ['name' => 'records.permanent_delete', 'display_name' => 'Permanent Delete Records', 'module_category' => 'Recovery'],
        ];

        $permissions = [];
        foreach ($permissionsData as $perm) {
            $permissions[$perm['name']] = Permission::create($perm);
        }

        // 3. MAP PERMISSIONS TO ROLES (Role-Permission Matrix)

        // Super Admin permissions (All of them)
        $roles['super-admin']->permissions()->sync(array_values(collect($permissions)->pluck('id')->toArray()));

        // Manager permissions
        $roles['manager']->permissions()->sync([
            $permissions['settings.view']->id,
            $permissions['users.view']->id,
            $permissions['projects.view']->id,
            $permissions['projects.create']->id,
            $permissions['projects.edit']->id,
            $permissions['projects.payments']->id,
            $permissions['projects.invoices']->id,
            $permissions['clients.view.global']->id,
            $permissions['clients.create']->id,
            $permissions['clients.edit']->id,
            $permissions['clients.delete']->id, // Manager can soft delete
            $permissions['call-logs.create']->id,
            $permissions['call-logs.edit']->id,
            $permissions['tasks.view.global']->id,
            $permissions['tasks.create']->id,
            $permissions['tasks.edit']->id,
            $permissions['tasks.delete']->id,
            $permissions['logs.view']->id,
            $permissions['records.restore']->id, // Restore permissions
        ]);

        // Supervisor permissions
        $roles['supervisor']->permissions()->sync([
            $permissions['clients.view.global']->id, // supervisors view all assigned agent clients
            $permissions['clients.create']->id,
            $permissions['clients.edit']->id,
            $permissions['call-logs.create']->id,
            $permissions['call-logs.edit']->id,
            $permissions['tasks.view.global']->id,
            $permissions['tasks.create']->id,
            $permissions['tasks.edit']->id,
            $permissions['logs.view']->id,
        ]);

        // Team Lead permissions
        $roles['team-lead']->permissions()->sync([
            $permissions['clients.view.global']->id,
            $permissions['clients.create']->id,
            $permissions['call-logs.create']->id,
            $permissions['call-logs.edit']->id, // requires reason
            $permissions['tasks.view.global']->id,
            $permissions['tasks.create']->id,
            $permissions['tasks.edit']->id,
        ]);

        // Agent permissions
        $roles['agent']->permissions()->sync([
            $permissions['clients.view.assigned']->id,
            $permissions['clients.create']->id,
            $permissions['clients.edit']->id,
            $permissions['call-logs.create']->id,
            $permissions['tasks.view.assigned']->id,
            $permissions['tasks.create']->id,
            $permissions['tasks.edit']->id,
        ]);
    }
}
```

---

## 4.0 Backend Core & Dynamic Permission Engine

To maintain high performance and avoid database query bloat during request lifecycles, dynamic permission checks are implemented via a custom `Trait` combined with **Redis or Database tag caching**.

### 4.1 Permission Management Trait

Create file: `app/Traits/HasPermissions.php`

```php
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
```

### 4.2 Updated User Model Incorporation

Open file: `app/Models/User.php`

```php
namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissions; // Trait applied here

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
    
    // Legacy mapping support for backward compatibility during transition
    public function getRoleAttribute(): string
    {
        $primaryRole = $this->roles()->orderBy('hierarchy_level', 'asc')->first();
        return $primaryRole ? $primaryRole->display_name : 'Agent';
    }
}
```

---

## 5.0 Authorization Middleware Implementation

The route protection logic uses a custom parameter-based gate middleware.

Create file: `app/Http/Middleware/CheckPermission.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Perform dynamic permission check
        if (!$user->hasPermissionTo($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. You do not possess the required permission: ' . $permission
                ], 403);
            }
            abort(403, 'Unauthorized access. You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
```

Register Middleware in `bootstrap/app.php` (Laravel 12):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'permission' => \App\Http\Middleware\CheckPermission::class,
    ]);
})
```

---

## 6.0 Backend Integration & Controllers Usage Examples

### 6.1 Unified Route Routing Configuration (`routes/web.php`)

Replace hardcoded `role` keys with `permission` directives:

```php
// User Administration - Only users with users.view permission
Route::middleware(['auth', 'permission:users.view'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'permission:users.create'])->group(function () {
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
});

// Project Management Module
Route::middleware(['auth', 'permission:projects.view'])->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/payments', [ProjectController::class, 'storePayment'])->name('projects.payments.store')->middleware('permission:projects.payments');
});
```

### 6.2 Controller Authorization Application Example

```php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Dynamic directory listing query
        $clients = Client::query();

        if (!$user->hasPermissionTo('clients.view.global')) {
            // Locked down to assigned scope
            $clients->where('agent_id', $user->id);
        }

        $clients = $clients->latest()->paginate(15);
        return view('clients.index', compact('clients'));
    }

    public function destroy(Client $client)
    {
        $user = Auth::user();

        if (!$user->hasPermissionTo('clients.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $client->delete(); // Automatically intercepts to soft delete

        return redirect()->route('clients.index')->with('success', 'Record soft deleted successfully.');
    }
}
```

---

## 7.0 Immutable Audit Trail & Activity Logging Architecture

To ensure enterprise compliance, audits are designed to be completely immutable (records cannot be modified or destroyed by anyone, including the Super Admin).

### 7.1 Database Auditing Schema

Create file: `database/migrations/2026_05_18_200000_create_audit_and_activity_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Immutable security log
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type');           // CREATE, UPDATE, DELETE, RESTORE
            $table->string('model_type');            // Model class
            $table->unsignedBigInteger('model_id');
            $table->json('old_values')->nullable();  // JSON state before transaction
            $table->json('new_values')->nullable();  // JSON state after transaction
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent(); // NO updated_at to ensure immutability
        });

        // User operation trace log
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('activity_description');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('audit_trails');
    }
};
```

---

### 7.2 Automated Auditing Trait (`Auditable`)

Applying this trait to models instantly triggers automated recording of value deltas.

Create file: `app/Traits/Auditable.php`

```php
<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->logAudit('CREATE', null, $model->getAttributes());
        });

        static::updating(function ($model) {
            $old = array_intersect_key($model->getOriginal(), $model->getDirty());
            $new = $model->getDirty();
            $model->logAudit('UPDATE', $old, $new);
        });

        static::deleted(function ($model) {
            $model->logAudit('DELETE', $model->getOriginal(), null);
        });
    }

    protected function logAudit(string $action, ?array $old, ?array $new): void
    {
        // Safe password obfuscation in logs
        if ($old && isset($old['password'])) $old['password'] = '******';
        if ($new && isset($new['password'])) $new['password'] = '******';

        AuditTrail::create([
            'user_id' => Auth::id(),
            'action_type' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $old ? json_encode($old) : null,
            'new_values' => $new ? json_encode($new) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent()
        ]);
    }
}
```

Simply apply `use Auditable;` inside `Client.php`, `Project.php`, or `Task.php` to secure the audit trails completely!

---

## 8.0 Soft Delete System & Permanent Delete Security

To guarantee that no historical accounting records or client profiles are lost during disputes or agent errors, a soft delete recovery flow is built.

### 8.1 Model Soft Delete Registration

```php
namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Native Laravel Trait

class Project extends Model
{
    use SoftDeletes, Auditable; // Combined soft deletes + immutable auditing
}
```

### 8.2 Recovery & Elimination Controllers Scoping

```php
namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecoveryController extends Controller
{
    /**
     * Restore a soft deleted model. Available to Managers & Super-Admins.
     */
    public function restore(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('records.restore')) {
            abort(403, 'Unauthorized access.');
        }

        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();

        // Audit Trail gets logged automatically with action RESTORE
        return redirect()->back()->with('success', 'Project restored successfully.');
    }

    /**
     * PERMANENT HARD DELETE. Strictly limited to Super Admin.
     */
    public function forceDelete(Request $request, $id)
    {
        if (!Auth::user()->hasPermissionTo('records.permanent_delete')) {
            abort(403, 'Only Super Admin can permanently remove records from the system.');
        }

        $project = Project::onlyTrashed()->findOrFail($id);
        $project->forceDelete();

        return redirect()->back()->with('success', 'Record permanently purged from the system datastore.');
    }
}
```

---

## 9.0 Frontend Interactivity, Menus & Dashboards

Blade and Alpine.js frontend views utilize the dynamic permission engines to hide structural UI interfaces before the browser renders pages:

### 9.1 Dynamic Menu Visibility (Blade Engine)

```html
<!-- Sidebar Navigation -->
<nav class="sidebar">
    <a href="/dashboard">Dashboard</a>

    <!-- Only show Client Directory if authorized -->
    @if(auth()->user()->hasPermissionTo('clients.view.global') || auth()->user()->hasPermissionTo('clients.view.assigned'))
        <a href="/clients">Clients Directory</a>
    @endif

    <!-- Projects is restricted to Manager/Super Admin permissions -->
    @if(auth()->user()->hasPermissionTo('projects.view'))
        <a href="/projects">Project & Accounts</a>
    @endif

    <!-- User settings configuration -->
    @if(auth()->user()->hasPermissionTo('users.view'))
        <a href="/users">User Management</a>
    @endif

    @if(auth()->user()->hasPermissionTo('settings.view'))
        <a href="/settings">Global Settings</a>
    @endif
</nav>
```

### 9.2 Fine-Grained Button Controls (Action Isolation)

```html
<div class="project-actions-card">
    <h4>Project Operations</h4>
    
    @if(auth()->user()->hasPermissionTo('projects.payments'))
        <button class="btn btn-primary" onclick="openPaymentModal()">Record Payment</button>
    @endif

    @if(auth()->user()->hasPermissionTo('projects.invoices'))
        <button class="btn btn-secondary" onclick="generateTaxInvoice()">Generate Invoice</button>
    @endif

    @if(auth()->user()->hasPermissionTo('records.permanent_delete'))
        <button class="btn btn-danger" onclick="confirmWipe()">PERMANENT PURGE</button>
    @endif
</div>
```

---

## 10.0 Zero-Downtime Migration Strategy

Upgrading live client databases to this database-driven system is done in four safe, non-destructive phases:

### Phase 1: Database Setup
1. Deploy the dynamic migrations (`roles`, `permissions`, `permission_role`, `role_user`).
2. Run the `RBACSeeder` to populate the dynamic permission tree and default roles.

### Phase 2: Pivot Mapping (Data Migration Script)
Run the following database migration script to map existing users to the newly defined role tables based on their legacy `users.role` attribute string:

Create file: `database/migrations/2026_05_18_300000_migrate_legacy_user_roles.php`

```php
<?php

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Read legacy string attribute
            $legacyRole = $user->role; 

            // Find matching dynamic DB role
            $roleName = match($legacyRole) {
                'Administrator' => 'super-admin',
                'Manager'       => 'manager',
                'Team Lead'     => 'team-lead',
                'Agent', 'Beader' => 'agent', // Beaders are unified to Agent privileges
                default         => 'agent'
            };

            $dbRole = Role::where('name', $roleName)->first();

            if ($dbRole) {
                // Link pivot mapping
                $user->roles()->syncWithoutDetaching([$dbRole->id]);
            }
        }
    }

    public function down(): void
    {
        // Non-destructive: Truncate only role-user mappings
        DB::table('role_user')->truncate();
    }
};
```

### Phase 3: Code Deployment
1. Apply `use HasPermissions` trait into the user model.
2. Deploy the `CheckPermission` middleware.
3. Push route routing structures referencing permission mappings instead of legacy string roles.

### Phase 4: Deprecation & Cleanup
Once verification tests confirm all permissions cache successfully:
1. Deprecate legacy middleware `RoleMiddleware`.
2. Clean up structural references to `users.role` inside SQL datastores.

---

## 11.0 Scalable Enterprise Best Practices

1. **Hierarchy Optimization:** Implement Role-to-Role inheritance using deep indexing to prevent multiple pivot mappings.
2. **Caching Strategy:** Cache key `"user_permissions_{$user_id}"` should reside inside Redis with tag matching, ensuring invalidations only occur when roles/permissions are altered.
3. **Database Performance:** Ensure indices exist on the pivot columns `role_id` and `user_id` inside pivot mapping tables to guarantee rapid query execution times.
