<!-- Notifications Offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="notificationsOffcanvas" aria-labelledby="notificationsOffcanvasLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="notificationsOffcanvasLabel">
            <i class="fa-solid fa-bell me-2"></i>Notifications
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        @php
            $notifications = collect();
            $unreadCount = 0;
            try {
                $notifications = auth()->user()->notifications()->take(10)->get();
                $unreadCount = auth()->user()->unreadNotifications()->count();
            } catch (\Exception $e) {
                // Notifications table doesn't exist yet
            }
        @endphp

        @if($unreadCount > 0)
            <div class="p-3 border-bottom bg-body-secondary">
                <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fa-solid fa-check-double me-1"></i>Mark all as read
                    </button>
                </form>
            </div>
        @endif

        <div class="list-group list-group-flush">
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $status = $data['status'] ?? 'unknown';
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="list-group-item {{ $isUnread ? 'bg-primary bg-opacity-10' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start mb-2">
                        <h6 class="mb-1 d-flex align-items-center gap-2">
                            @if($status === 'approved')
                                <i class="fa-solid fa-circle-check text-success"></i>
                            @else
                                <i class="fa-solid fa-circle-xmark text-danger"></i>
                            @endif
                            <span class="text-truncate">{{ $data['recipe_title'] ?? 'Recipe' }}</span>
                        </h6>
                        @if($isUnread)
                            <span class="badge bg-primary rounded-pill">New</span>
                        @endif
                    </div>

                    <p class="mb-2 small">
                        @if($status === 'approved')
                            <span class="text-success fw-semibold">Approved</span> - Your recipe has been approved and is now public!
                        @else
                            <span class="text-danger fw-semibold">Changes Requested</span> - Your recipe needs some updates before approval.
                        @endif
                    </p>

                    @if(!empty($data['notes']))
                        <div class="alert alert-warning py-2 px-3 mb-2 small" hidden>
                            <strong>Moderator feedback:</strong><br>
                            {{ Str::limit($data['notes'], 150) }}
                        </div>
                    @endif

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fa-regular fa-clock me-1"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </small>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('recipes.show', $data['recipe_id']) }}" class="btn btn-sm btn-outline-primary" style="border-radius: var(--bs-btn-border-radius)">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if($isUnread)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary ms-1" title="Mark as read">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="fa-regular fa-bell-slash fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">No notifications yet</p>
                </div>
            @endforelse
        </div>

        @if($notifications->count() >= 10)
            <div class="p-3 border-top text-center">
                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-list me-1"></i>View All Notifications
                </a>
            </div>
        @endif
    </div>
</div>
