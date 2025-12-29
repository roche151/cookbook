<x-app-layout>
    <x-slot name="title">Admin</x-slot>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Admin</li>
            </ol>
        </nav>

        <h1 class="h4 mb-3">Admin</h1>
        <p class="text-muted mb-4">Quick links to admin tools.</p>

        <div class="row g-3">
            {{-- Moderation --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-shield-halved text-primary me-2"></i>
                            <h5 class="mb-0">Moderation</h5>
                            @if($pendingCount > 0)
                                <span class="badge bg-warning text-dark ms-2">{{ $pendingCount }}</span>
                            @else
                                <span class="badge bg-success ms-2">0</span>
                            @endif
                        </div>
                        <p class="text-muted mb-3">Review public recipe submissions before they go live.</p>
                        <a href="{{ route('admin.moderation.recipes.index') }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-eye me-1"></i>View</a>
                    </div>
                </div>
            </div>
            {{-- Users Management --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-users-cog text-primary me-2"></i>
                            <h5 class="mb-0">Users</h5>
                            <span class="badge bg-success ms-2"> {{ $usersCount }}</span>
                        </div>
                        <p class="text-muted mb-3">Manage user accounts and permissions.</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-eye me-1"></i>View</a>
                    </div>
                </div>
            </div>
            {{-- Feedback --}}
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fa-solid fa-comments text-primary me-2"></i>
                            <h5 class="mb-0">Feedback</h5>
                            <span class="badge bg-danger ms-2">{{ $feedbackCount }}</span>
                        </div>
                        <p class="text-muted mb-3">View user feedback and suggestions.</p>
                        <a href="{{ route('admin.feedback.index') }}" class="btn btn-sm btn-primary"><i class="fa-solid fa-eye me-1"></i>View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
