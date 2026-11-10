<?php

namespace App\Tenancy;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ProvisionTenant
{
    public function __invoke(string $name, string $slug, User $owner): int
    {
        Validator::make(compact('name', 'slug'), [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:80', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', Rule::unique('tenants')],
        ])->validate();

        return DB::transaction(function () use ($name, $slug, $owner) {
            $now = now();
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => $name, 'slug' => $slug, 'created_at' => $now, 'updated_at' => $now,
            ]);
            $membershipId = DB::table('tenant_memberships')->insertGetId([
                'tenant_id' => $tenantId, 'user_id' => $owner->id, 'created_at' => $now, 'updated_at' => $now,
            ]);

            DB::table('permissions')->upsert([
                ['key' => 'workspace.manage'],
                ['key' => 'workspace.view'],
            ], ['key']);

            foreach ([
                'owner' => 'Tenant Owner',
                'admin' => 'Company Admin',
                'sales-manager' => 'Sales Manager',
                'team-leader' => 'Team Leader',
                'telecaller' => 'Telecaller',
                'sales-executive' => 'Sales Executive',
            ] as $key => $label) {
                $roleId = DB::table('roles')->insertGetId(['tenant_id' => $tenantId, 'key' => $key, 'name' => $label]);
                $grants = [['tenant_id' => $tenantId, 'role_id' => $roleId, 'permission_key' => 'workspace.view']];

                if (in_array($key, ['owner', 'admin'], true)) {
                    $grants[] = ['tenant_id' => $tenantId, 'role_id' => $roleId, 'permission_key' => 'workspace.manage'];
                }

                DB::table('role_permissions')->insert($grants);

                if ($key === 'owner') {
                    DB::table('user_roles')->insert([
                        'tenant_id' => $tenantId, 'membership_id' => $membershipId, 'role_id' => $roleId,
                    ]);
                }
            }

            return $tenantId;
        }, 3);
    }
}
