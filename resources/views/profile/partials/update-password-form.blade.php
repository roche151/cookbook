<section>
    <h5 class="fw-semibold mb-3">
        <i class="fa-solid fa-lock me-2 text-primary"></i>Update Password
    </h5>
    <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>

    <form method="post" action="{{ route('password.update') }}" class="mt-3">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold">
                <i class="fa-solid fa-key me-1 text-primary"></i>Current Password
            </label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password">
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-semibold">
                <i class="fa-solid fa-lock me-1 text-primary"></i>New Password
            </label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation" class="form-label fw-semibold">
                <i class="fa-solid fa-lock-open me-1 text-primary"></i>Confirm Password
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fa-solid fa-save me-2"></i>Update Password
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success">
                    <i class="fa-solid fa-check-circle me-1"></i>Password updated
                </span>
            @endif
        </div>
    </form>
</section>
