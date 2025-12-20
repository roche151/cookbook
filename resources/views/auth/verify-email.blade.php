<x-guest-layout>
    <h4 class="mb-4">Verify Email Address</h4>

    <div class="mb-4 text-muted">
        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success mb-4">
            A new verification link has been sent to the email address you provided during registration.
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center gap-2">
        <form method="POST" action="{{ route('verification.send') }}" class="flex-grow-1">
            @csrf
            <button type="submit" class="btn btn-primary w-100">
                Resend Verification Email
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">
                Log Out
            </button>
        </form>
    </div>
</x-guest-layout>
