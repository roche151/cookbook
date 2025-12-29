<x-app-layout>
    <x-slot name="title">User Management</x-slot>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
            </ol>
        </nav>
        <table class="table">
            <tr>
                <th>Name</th>
                <td>{{ $user->name }}</td>
                <td></td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $user->email }}</td>
                <td></td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ $user->created_at }}</td>
                <td></td>
            </tr>
            <tr>
                <th>Email Verified At</th>
                <td>{{ $user->email_verified_at }}</td>
                <td>
                    {{-- verify/unverify form --}}
                    <form action="{{ route('admin.users.toggle-verified', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $user->email_verified_at ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                            {{ $user->email_verified_at ? 'Unverify Email' : 'Verify Email' }}
                        </button>
                    </form>
                </td>
            </tr>
            <tr>
                <th>Role</th>
                <td>{{ $user->is_admin ? 'Admin' : 'User' }}</td>
                <td>
                    {{-- toggle admin form --}}
                    <form action="{{ route('admin.users.toggle-admin', $user) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-sm {{ $user->is_admin ? 'btn-outline-secondary' : 'btn-outline-primary' }}">
                            {{ $user->is_admin ? 'Revoke Admin' : 'Make Admin' }}
                        </button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
</x-app-layout>

