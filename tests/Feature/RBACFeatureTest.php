<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RBACFeatureTest extends TestCase
{
    /**
     * Test Administrator / Super Admin access privileges.
     */
    public function test_administrator_has_full_access(): void
    {
        $user = User::where('email', 'test@gmail.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/call-logs');
        $response->assertStatus(200);
    }

    /**
     * Test Manager access privileges.
     */
    public function test_manager_has_access_to_projects_and_users(): void
    {
        $user = User::where('email', 'manager@gmail.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);
    }

    /**
     * Test Team Lead restricted access privileges.
     */
    public function test_teamlead_cannot_access_projects_or_users(): void
    {
        $user = User::where('email', 'teamlead@gmail.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Projects is restricted to Admin|Manager
        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(403);

        // Users is restricted to Admin|Manager
        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);
    }

    /**
     * Test Agent restricted access privileges.
     */
    public function test_agent_cannot_access_projects_or_users(): void
    {
        $user = User::where('email', 'agent@gmail.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Projects is restricted
        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(403);

        // Users is restricted
        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);
    }

    /**
     * Test Beader behaves exactly like Agent.
     */
    public function test_beader_behaves_exactly_like_agent(): void
    {
        $user = User::where('email', 'beader@gmail.com')->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);

        // Projects is restricted
        $response = $this->actingAs($user)->get('/projects');
        $response->assertStatus(403);

        // Users is restricted
        $response = $this->actingAs($user)->get('/users');
        $response->assertStatus(403);

        $response = $this->actingAs($user)->get('/clients');
        $response->assertStatus(200);
    }

    /**
     * Test soft delete and restore cycle.
     */
    public function test_soft_delete_and_restore_cycle(): void
    {
        $admin = User::where('email', 'test@gmail.com')->first();
        $agent = User::where('email', 'agent@gmail.com')->first();

        // Generate unique numbers to avoid conflict
        $phone = '123' . rand(1000000, 9999999);
        $clientName = 'Temporary Test Client ' . rand(1000, 9999) . '-' . uniqid();

        // Create a new client assigned to the agent
        $client = \App\Models\Client::create([
            'customer_number' => 'CLT-' . strtoupper(uniqid()),
            'full_name' => $clientName,
            'phone' => $phone,
            'status' => 'New',
            'agent_id' => $agent->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Soft delete the client as Agent
        $response = $this->actingAs($agent)->delete("/clients/{$client->id}");
        $response->assertRedirect('/clients');

        // Assert client is soft-deleted
        $this->assertSoftDeleted('clients', ['id' => $client->id]);

        // Assert that the client DOES NOT show up in the Agent's active client view
        $response = $this->actingAs($agent)->get('/clients');
        $response->assertDontSee($client->full_name);

        // Assert that the client DOES NOT show up in the Admin's default view
        $response = $this->actingAs($admin)->get('/clients');
        $response->assertDontSee($client->full_name);

        // Assert that the client DOES show up in the Admin's Trash view (filter=deleted)
        $response = $this->actingAs($admin)->get('/clients?filter=deleted');
        $response->assertSee($client->full_name);

        // Restore the client as Admin
        $response = $this->actingAs($admin)->post("/clients/{$client->id}/restore");
        $response->assertRedirect('/clients');

        // Assert client is fully restored in database
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'deleted_at' => null
        ]);
    }

    /**
     * Test force delete cycle as administrator.
     */
    public function test_force_delete_as_admin(): void
    {
        $admin = User::where('email', 'test@gmail.com')->first();
        $agent = User::where('email', 'agent@gmail.com')->first();

        // Generate unique numbers to avoid conflict
        $phone = '123' . rand(1000000, 9999999);
        $clientName = 'Temporary Test Client ' . rand(1000, 9999) . '-' . uniqid();

        // Create a new client
        $client = \App\Models\Client::create([
            'customer_number' => 'CLT-' . strtoupper(uniqid()),
            'full_name' => $clientName,
            'phone' => $phone,
            'status' => 'New',
            'agent_id' => $agent->id,
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        // Soft delete the client
        $client->delete();

        // Verify soft-deleted
        $this->assertSoftDeleted('clients', ['id' => $client->id]);

        // Force delete the client as Agent (should fail 403)
        $response = $this->actingAs($agent)->delete("/clients/{$client->id}/force-delete");
        $response->assertStatus(403);

        // Force delete the client as Admin (should succeed)
        $response = $this->actingAs($admin)->delete("/clients/{$client->id}/force-delete");
        $response->assertRedirect('/clients');

        // Verify permanently deleted from database
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
