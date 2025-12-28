<x-guest-layout>
    <h4 class="mb-4">Login</h4>

    @if (session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check mb-3">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label class="form-check-label" for="remember_me">Remember me</label>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if (Route::has('password.request'))
                <a class="text-decoration-none" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif

            <button type="submit" class="btn btn-primary">Log in</button>
        </div>

        <div class="mt-3 text-center">
            <a href="{{ route('register') }}" class="text-decoration-none">Don't have an account? Register</a>
        </div>
    </form>

    @if(config('app.env') !== 'production')
    <div class="mt-4 pt-3 border-top">
        @php
            $quickUsers = \App\Models\User::orderBy('name')->orderBy('email')->get(['id', 'name', 'email']);
        @endphp
        @if($quickUsers->isNotEmpty())
        <form method="POST" action="{{ route('dev.quick-login') }}" class="d-flex flex-wrap gap-2 align-items-center">
            @csrf
            <label for="quick-login-user" class="form-label mb-0 text-muted">Login as:</label>
            <select id="quick-login-user" name="user_id" class="form-select form-select-sm" style="min-width: 240px;">
                @foreach($quickUsers as $user)
                    <option value="{{ $user->id }}">{{ $user->name ?? 'User #'.$user->id }} ({{ $user->email }})</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Login</button>
        </form>
        @else
            <p class="text-muted small mb-0">No users available.</p>
        @endif
    </div>
    @endif
</x-guest-layout>
