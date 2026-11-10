<?php

namespace App\Tenancy;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class Workspaces
{
    public function activeFor(int $userId): Builder
    {
        return DB::table('tenant_memberships as memberships')
            ->join('tenants', 'tenants.id', '=', 'memberships.tenant_id')
            ->where('memberships.user_id', $userId)
            ->where('memberships.active', true)
            ->where('tenants.active', true)
            ->select('tenants.id', 'tenants.name', 'tenants.slug', 'memberships.id as membership_id');
    }

    public function permits(int $tenantId, int $membershipId, string $permission): bool
    {
        return DB::table('user_roles as grants')
            ->join('role_permissions as permissions', function ($join) {
                $join->on('permissions.tenant_id', '=', 'grants.tenant_id')
                    ->on('permissions.role_id', '=', 'grants.role_id');
            })
            ->where('grants.tenant_id', $tenantId)
            ->where('grants.membership_id', $membershipId)
            ->where('permissions.permission_key', $permission)
            ->exists();
    }
}
