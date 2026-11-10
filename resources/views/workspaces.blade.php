<x-layout title="Choose a workspace">
    <header class="page-header">
        <h1>Choose a workspace</h1>
        <p class="muted">Select a company to open its CRM workspace.</p>
    </header>

    <section class="card" aria-label="Available workspaces">
        @error('workspace') <p class="error" role="alert">{{ $message }}</p> @enderror
        @forelse ($workspaces as $workspace)
            <form method="post" action="{{ route('workspaces.switch') }}" class="workspace-row">
                @csrf
                <input type="hidden" name="workspace" value="{{ $workspace->slug }}">
                <h2>{{ $workspace->name }}</h2>
                <button class="button button-primary" aria-label="Open {{ $workspace->name }}" type="submit">Open workspace</button>
            </form>
        @empty
            <p>You do not have any active workspaces. Contact your company administrator for access.</p>
        @endforelse
    </section>
</x-layout>
