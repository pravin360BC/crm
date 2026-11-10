@props(['workspace' => null, 'canManage' => false])
<nav class="navigation" aria-label="Workspace">
    @if ($workspace)
        <a href="{{ route('workspace') }}" @if (request()->routeIs('workspace')) aria-current="page" @endif>Overview</a>
        @if ($canManage)
            <a href="{{ route('workspace.settings') }}" @if (request()->routeIs('workspace.settings')) aria-current="page" @endif>Workspace settings</a>
        @endif
    @endif
    <a href="{{ route('workspaces') }}" @if (request()->routeIs('workspaces')) aria-current="page" @endif>Switch workspace</a>
</nav>
