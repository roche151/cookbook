<x-app-layout>
    <x-slot name="title">Feedback</x-slot>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Admin</a></li>
                <li class="breadcrumb-item active" aria-current="page">Feedback</li>
            </ol>
        </nav>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h4 mb-1">Feedback</h1>
            </div>
        </div>

        @if($feedback->count())
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Page</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($feedback as $item)
                                    <tr>
                                        <td>
                                            {{ data_get($item, 'user.name') ?? 'Unknown user' }}<br>
                                            <small class="text-muted">{{ data_get($item, 'user.email') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($item->type === 'bug') bg-danger 
                                                @elseif($item->type === 'feature') bg-info text-dark 
                                                @else bg-secondary 
                                                @endif">
                                                {{ ucfirst($item->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $item->page }}</td>
                                        <td>{{ $item->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-body-secondary border-0">{{ $feedback->links() }}</div>
            </div>
        @else
            <div class="alert alert-success d-flex align-items-center" role="status">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div>No feedback available right now.</div>
            </div>
        @endif
    </div>
</x-app-layout>
