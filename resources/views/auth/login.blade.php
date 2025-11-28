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
        <p class="text-muted small mb-2">Quick Login (Testing):</p>
        @php
            $testUsers = [
                ['name' => 'James', 'email' => 'james@email.com', 'password' => 'Password1'],
                ['name' => 'Test User', 'email' => 'test@example.com', 'password' => 'password'],
            ];
        @endphp
        @foreach($testUsers as $user)
        <form method="POST" action="{{ route('login') }}" class="mb-2">
            @csrf
            <input type="hidden" name="email" value="{{ $user['email'] }}">
            <input type="hidden" name="password" value="{{ $user['password'] }}">
            <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                Login as {{ $user['name'] }} ({{ $user['email'] }})
            </button>
        </form>
        @endforeach
    </div>
    @endif
</x-guest-layout>
