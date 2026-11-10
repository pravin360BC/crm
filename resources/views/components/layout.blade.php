@props(['title', 'workspace' => null, 'canManage' => false])
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · 360BC CRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    @auth
        <div class="app-shell" x-data>
            <aside class="sidebar">
                <x-brand />
                <x-workspace-nav :workspace="$workspace" :can-manage="$canManage" />
            </aside>

            <div class="shell-content">
                <header class="topbar">
                    <a class="button mobile-menu-link" href="{{ route('workspaces') }}"
                        x-on:click.prevent="$refs.navigation.showModal()">Menu</a>
                    <span class="topbar-title">{{ $workspace?->name ?? 'Workspaces' }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="button" type="submit">Sign out</button>
                    </form>
                </header>
                <main class="content" id="main" tabindex="-1">{{ $slot }}</main>
            </div>

            <dialog class="drawer" x-ref="navigation" aria-labelledby="navigation-title">
                <div class="drawer-header">
                    <h2 id="navigation-title">Menu</h2>
                    <form method="dialog">
                        <button class="button" type="submit" autofocus>Close</button>
                    </form>
                </div>
                <x-workspace-nav :workspace="$workspace" :can-manage="$canManage" />
            </dialog>
        </div>
    @else
        <main class="auth-content" id="main" tabindex="-1">{{ $slot }}</main>
    @endauth
</body>
</html>
