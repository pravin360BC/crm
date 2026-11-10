<?php

namespace App\Http\Middleware;

use App\Tenancy\Workspaces;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequireWorkspacePermission
{
    public function __construct(private Workspaces $workspaces) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $tenantId = $request->session()->get('workspace_id');

        if ($tenantId === null) {
            return redirect()->route('workspaces');
        }

        $workspace = $this->workspaces->activeFor($request->user()->id)->where('tenants.id', $tenantId)->first();

        if ($workspace === null) {
            $request->session()->forget('workspace_id');

            return redirect()->route('workspaces');
        }

        abort_unless($this->workspaces->permits($workspace->id, $workspace->membership_id, $permission), 403);
        $request->attributes->set('workspace', $workspace);

        return $next($request);
    }
}
