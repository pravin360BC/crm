<?php

namespace Tests\Feature;

use App\Models\User;
use App\Tenancy\ProvisionTenant;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WorkspaceFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);
        $this->withoutVite();
    }

    public function test_guest_must_authenticate_and_invalid_credentials_do_not_sign_in(): void
    {
        $this->get('/workspace')->assertRedirect('/login');
        User::factory()->create(['email' => 'owner@example.test']);

        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post('/login', ['email' => 'OWNER@example.test', 'password' => 'password'])
            ->assertRedirect('/workspaces');
        $this->assertAuthenticated();

        $this->post('/logout')->assertRedirect('/login')->assertSessionMissing('workspace_id');
        $this->assertGuest();
    }

    public function test_console_bootstraps_a_login_and_its_tenant(): void
    {
        config(['hashing.driver' => 'argon2id']);

        $this->artisan('crm:create-user')
            ->expectsQuestion('Name', 'Workspace Owner')
            ->expectsQuestion('Email', 'OWNER@example.test')
            ->expectsQuestion('Password', 'a-long-test-password')
            ->expectsQuestion('Confirm password', 'a-long-test-password')
            ->assertSuccessful();

        $owner = User::where('email', 'owner@example.test')->sole();
        $this->assertTrue(Hash::check('a-long-test-password', $owner->password));
        $this->assertSame('argon2id', password_get_info($owner->password)['algoName']);

        $this->artisan('crm:create-tenant', ['owner' => $owner->id, 'slug' => 'beacon', 'name' => 'Beacon Realty'])
            ->assertSuccessful();

        $this->post('/login', ['email' => $owner->email, 'password' => 'a-long-test-password'])->assertRedirect('/workspaces');
        $this->post('/workspaces/switch', ['workspace' => 'beacon'])->assertRedirect('/workspace');
        $this->get('/workspace/settings')->assertOk()->assertSee('Beacon Realty');
    }

    public function test_login_is_throttled(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])
            ->assertTooManyRequests();
    }

    public function test_provisioning_creates_an_owner_and_switching_only_exposes_the_selected_tenant(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $first = $this->provision('Beacon Realty', 'beacon', $owner);
        $second = $this->provision('Coastal Realty', 'coastal', $owner);
        $other = $this->provision('Private Realty', 'private', $otherUser);

        $this->assertDatabaseCount('permissions', 2);
        $this->assertDatabaseCount('roles', 18);
        $this->actingAs($owner)->get('/workspaces')->assertOk()
            ->assertSee('Beacon Realty')->assertSee('Coastal Realty')->assertDontSee('Private Realty');
        $this->post('/workspaces/switch', ['workspace' => 'beacon', 'tenant_id' => $other])
            ->assertRedirect('/workspace')->assertSessionHas('workspace_id', $first);
        $this->get('/workspace/settings')->assertOk()->assertSee('Beacon Realty')->assertDontSee('Coastal Realty');
        $this->post('/workspaces/switch', ['workspace' => 'private'])->assertNotFound();
        $this->post('/workspaces/switch', ['workspace' => 'coastal'])
            ->assertRedirect('/workspace')->assertSessionHas('workspace_id', $second);
        $this->get('/workspace')->assertOk()->assertSee('Coastal Realty')->assertDontSee('Beacon Realty');

        $this->withSession(['workspace_id' => $other])->get('/workspace/settings')
            ->assertRedirect('/workspaces')->assertSessionMissing('workspace_id');
    }

    public function test_permission_and_membership_are_rechecked_after_workspace_selection(): void
    {
        $owner = User::factory()->create();
        $tenant = $this->provision('Beacon Realty', 'beacon', $owner);
        $membership = DB::table('tenant_memberships')->where('tenant_id', $tenant);
        $sales = DB::table('roles')->where('tenant_id', $tenant)->where('key', 'sales-executive')->value('id');

        $this->actingAs($owner)->post('/workspaces/switch', ['workspace' => 'beacon'])->assertRedirect('/workspace');
        DB::table('user_roles')->where('tenant_id', $tenant)->update(['role_id' => $sales]);

        $this->get('/workspace')->assertOk()->assertDontSee('Workspace settings');
        $this->get('/workspace/settings')->assertForbidden();

        DB::table('role_permissions')->where('tenant_id', $tenant)->where('role_id', $sales)->delete();
        $this->get('/workspace')->assertForbidden();
        $this->post('/workspaces/switch', ['workspace' => 'beacon'])->assertForbidden();

        $membership->update(['active' => false]);
        $this->get('/workspace')->assertRedirect('/workspaces')->assertSessionMissing('workspace_id');
        $this->post('/workspaces/switch', ['workspace' => 'beacon'])->assertNotFound();
    }

    public function test_the_database_rejects_a_cross_tenant_role_grant(): void
    {
        $owner = User::factory()->create();
        $first = $this->provision('Beacon Realty', 'beacon', $owner);
        $second = $this->provision('Coastal Realty', 'coastal', $owner);

        $this->expectException(QueryException::class);
        DB::table('user_roles')->insert([
            'tenant_id' => $first,
            'membership_id' => DB::table('tenant_memberships')->where('tenant_id', $first)->value('id'),
            'role_id' => DB::table('roles')->where('tenant_id', $second)->where('key', 'owner')->value('id'),
        ]);
    }

    public function test_provisioning_rolls_back_if_owner_membership_cannot_be_created(): void
    {
        $owner = User::factory()->create();
        $owner->delete();

        try {
            $this->provision('Beacon Realty', 'beacon', $owner);
            $this->fail('Expected the user foreign key to reject the membership.');
        } catch (QueryException) {
            $this->assertDatabaseCount('tenants', 0);
            $this->assertDatabaseCount('roles', 0);
        }
    }

    private function provision(string $name, string $slug, User $owner): int
    {
        return app(ProvisionTenant::class)($name, $slug, $owner);
    }
}
