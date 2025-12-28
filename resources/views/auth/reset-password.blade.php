<x-guest-layout>
    <h4 class="mb-3">Reset Password</h4>
    <p class="text-muted mb-4">Choose a new password to regain access to your account.</p>

    @if (session('status'))
        <div class="alert alert-success mb-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
                     <input id="email" type="email" name="email" autocomplete="username"
                         value="{{ old('email', $request->email) }}" readonly
                         class="form-control bg-body-secondary text-muted @error('email') is-invalid @enderror" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password"
                   class="form-control @error('password') is-invalid @enderror" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                   class="form-control @error('password_confirmation') is-invalid @enderror" required>
            @error('password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
</x-guest-layout>
