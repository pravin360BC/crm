<x-layout title="Workspace settings" :workspace="$workspace" :can-manage="true">
    <header class="page-header">
        <h1>Workspace settings</h1>
    </header>
    <section class="card" aria-labelledby="company-title">
        <h2 id="company-title">Company details</h2>
        <dl class="details">
            <dt>Company</dt>
            <dd>{{ $workspace->name }}</dd>
            <dt>Workspace slug</dt>
            <dd>{{ $workspace->slug }}</dd>
        </dl>
    </section>
</x-layout>
