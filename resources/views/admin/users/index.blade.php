<x-app-layout>
    <x-slot name="title">User Management</x-slot>

    <div class="container py-4">
        <div class="mb-3 no-print">
            <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Admin
            </a>
        </div>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h4 mb-1">Users</h1>
                <p class="text-muted mb-0">Manage user roles and permissions.</p>
            </div>
        </div>
        @if ($users->count())
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Created</th>
                                    <th>Email Verified</th>
                                    <th>Role</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->created_at }}</td>
                                        <td>{{ $user->email_verified_at }}</td>
                                        <td>
                                            @if ($user->is_admin)
                                                <span class="badge bg-primary">Admin</span>
                                            @else
                                                <span class="badge bg-secondary">User</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye me-1"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

