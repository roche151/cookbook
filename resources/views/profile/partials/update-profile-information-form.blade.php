<section>
    <h5 class="fw-semibold mb-3">
        <i class="fa-solid fa-id-card me-2 text-primary"></i>Profile Information
    </h5>
    <p class="text-muted mb-4">Update your account's profile information and email address.</p>

    <form method="post" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">
                <i class="fa-solid fa-user me-1 text-primary"></i>Name
            </label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">
                <i class="fa-solid fa-envelope me-1 text-primary"></i>Email
            </label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted small">
                        Your email address is unverified.
                        <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-decoration-underline">
                                Click here to re-send the verification email.
                            </button>
                        </form>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-success small mt-1">
                            A new verification link has been sent to your email address.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary btn-lg px-4">
                <i class="fa-solid fa-save me-2"></i>Save Changes
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success">
                    <i class="fa-solid fa-check-circle me-1"></i>Saved successfully
                </span>
            @endif
        </div>
    </form>
</section>
