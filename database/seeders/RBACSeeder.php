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

        // 4. Seed the explicit test users requested by the user
        $testUsers = [
            [
                'name' => 'Test_Administrator',
                'email' => 'test@gmail.com',
                'password' => bcrypt('Admin@321'),
                'role' => 'Administrator',
            ],
            [
                'name' => 'Test_Manager',
                'email' => 'manager@gmail.com',
                'password' => bcrypt('Admin@321'),
                'role' => 'Manager',
            ],
            [
                'name' => 'Test_TeamLead',
                'email' => 'teamlead@gmail.com',
                'password' => bcrypt('Admin@321'),
                'role' => 'Team Lead',
            ],
            [
                'name' => 'Test_Agent',
                'email' => 'agent@gmail.com',
                'password' => bcrypt('Admin@321'),
                'role' => 'Agent',
            ],
            [
                'name' => 'Test_Beader',
                'email' => 'beader@gmail.com',
                'password' => bcrypt('Admin@321'),
                'role' => 'Beader',
            ],
        ];

        foreach ($testUsers as $tu) {
            $user = \App\Models\User::where('email', $tu['email'])->first();
            if (!$user) {
                \App\Models\User::create($tu);
            } else {
                $user->update([
                    'name' => $tu['name'],
                    'password' => $tu['password'],
                    'role' => $tu['role'],
                ]);
            }
        }

        // 5. Map existing users to new roles (Self-healing mapping)
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $legacyRole = $user->role ?? 'Agent';
            $roleName = match($legacyRole) {
                'Administrator' => 'super-admin',
                'Manager'       => 'manager',
                'Team Lead'     => 'team-lead',
                'Agent', 'Beader' => 'agent',
                default         => 'agent'
            };

            $dbRole = Role::where('name', $roleName)->first();
            if ($dbRole) {
                $dbRole->users()->syncWithoutDetaching([$user->id]);
            }
        }
    }
}
