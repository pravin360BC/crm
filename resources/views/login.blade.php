<x-layout title="Sign in">
    <section class="card auth-card" aria-labelledby="sign-in-title">
        <x-brand />
        <p class="muted">Real Estate. Better Managed.</p>
        <h1 id="sign-in-title">Sign in</h1>

        <form method="post" action="{{ route('login') }}" class="form-stack">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email') <p class="error" id="email-error">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password') <p class="error" id="password-error">{{ $message }}</p> @enderror
            </div>
            <button class="button button-primary" type="submit">Sign in</button>
        </form>
    </section>
</x-layout>
