<x-app-layout>
    <x-slot name="title">Notifications</x-slot>

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="fa-solid fa-bell me-2"></i>Notifications
        </h1>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <form action="{{ route('notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="fa-solid fa-check-double me-1"></i>Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fa-regular fa-bell-slash fa-4x mb-3 text-muted opacity-50"></i>
                <h5>No notifications yet</h5>
                <p class="text-muted mb-0">You'll see notifications here when your recipes are reviewed.</p>
            </div>
        </div>
    @else
        <div class="list-group">
            @foreach($notifications as $notification)
                @php
                    $data = $notification->data;
                    $status = $data['status'] ?? 'unknown';
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="list-group-item {{ $isUnread ? 'border-start border-primary border-4 bg-primary bg-opacity-10' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                        <h5 class="mb-1 d-flex align-items-center gap-2">
                            @if($status === 'approved')
                                <i class="fa-solid fa-circle-check text-success"></i>
                            @else
                                <i class="fa-solid fa-circle-xmark text-danger"></i>
                            @endif
                            {{ $data['recipe_title'] ?? 'Recipe' }}
                        </h5>
                        <div class="d-flex gap-2 align-items-center">
                            @if($isUnread)
                                <span class="badge bg-primary">Unread</span>
                            @endif
                            <small class="text-muted text-nowrap">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>

                    <p class="mb-3">
                        @if($status === 'approved')
                            <span class="badge bg-success">Approved</span>
                            Your recipe has been approved and is now visible to the public!
                            @if($data['is_new_submission'] ?? false)
                                Thank you for your submission.
                            @else
                                Your changes have been applied.
                            @endif
                        @else
                            <span class="badge bg-danger">Changes Requested</span>
                            Your recipe needs some updates before it can be approved.
                        @endif
                    </p>

                    @if(!empty($data['notes']))
                        <div class="alert alert-warning mb-3">
                            <strong><i class="fa-solid fa-comment-dots me-1"></i>Moderator feedback:</strong>
                            <div class="mt-2">{{ $data['notes'] }}</div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <a href="{{ route('recipes.show', $data['recipe_id']) }}" class="btn btn-primary">
                            <i class="fa-solid fa-eye me-1"></i>View Recipe
                        </a>
                        <div class="btn-group">
                            @if($isUnread)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary me-1" title="Mark as read">
                                        <i class="fa-solid fa-check me-1"></i>Mark as Read
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Delete notification">
                                    <i class="fa-solid fa-trash me-1"></i>Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @endif
</x-app-layout>
