<x-layout title="Overview" :workspace="$workspace" :can-manage="$canManage">
    <header class="page-header" data-entrance>
        <p class="eyebrow">{{ $workspace->name }}</p>
        <h1>Overview</h1>
        <p class="muted">Real Estate. Better Managed.</p>
    </header>

    <section class="card" data-section-reveal aria-labelledby="access-title">
        <h2 id="access-title">Workspace access</h2>
        <p class="muted">You are signed in as {{ auth()->user()->name }}.</p>
        <a class="button button-primary" href="{{ route('workspaces') }}">Switch workspace</a>
    </section>
</x-layout>
