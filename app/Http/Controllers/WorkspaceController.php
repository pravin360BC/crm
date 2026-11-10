<?php

namespace App\Http\Controllers;

use App\Tenancy\Workspaces;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class WorkspaceController
{
    public function index(Request $request, Workspaces $workspaces): View
    {
        return view('workspaces', [
            'workspaces' => $workspaces->activeFor($request->user()->id)->orderBy('tenants.name')->get(),
        ]);
    }

    public function switch(Request $request, Workspaces $workspaces): RedirectResponse
    {
        $data = $request->validate(['workspace' => ['required', 'string', 'max:80']]);
        $workspace = $workspaces->activeFor($request->user()->id)->where('tenants.slug', $data['workspace'])->first();

        abort_if($workspace === null, 404);
        abort_unless($workspaces->permits($workspace->id, $workspace->membership_id, 'workspace.view'), 403);

        $request->session()->put('workspace_id', $workspace->id);

        return redirect()->route('workspace');
    }

    public function show(Request $request, Workspaces $workspaces): View
    {
        $workspace = $request->attributes->get('workspace');

        return view('workspace', [
            'workspace' => $workspace,
            'canManage' => $workspaces->permits($workspace->id, $workspace->membership_id, 'workspace.manage'),
        ]);
    }

    public function settings(Request $request): View
    {
        return view('workspace-settings', ['workspace' => $request->attributes->get('workspace')]);
    }
}
