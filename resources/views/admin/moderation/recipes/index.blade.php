<x-app-layout>
    <x-slot name="title">Moderate Recipes</x-slot>

    <div class="container py-4">
        <div class="mb-3 no-print">
            <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Admin
                </a>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h4 mb-1">Moderation</h1>
                <p class="text-muted mb-0">Review public recipe submissions before they go live.</p>
            </div>
        </div>

        @if($revisions->count())
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Recipe</th>
                                    <th>Type</th>
                                    <th>Author</th>
                                    <th>Submitted</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revisions as $revision)
                                    <tr>
                                        <td class="fw-semibold">
                                            {{ data_get($revision, 'recipe.title') ?? 'Untitled recipe' }}
                                        </td>
                                        <td>
                                            @php
                                                $allRevisions = $revision->recipe->revisions()->count();
                                                $isNew = $allRevisions === 1;
                                            @endphp
                                            <span class="badge {{ $isNew ? 'bg-success' : 'bg-info text-dark' }}">
                                                {{ $isNew ? 'New' : 'Update' }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ data_get($revision, 'user.name') ?? 'Unknown user' }}<br>
                                            <small class="text-muted">{{ data_get($revision, 'user.email') }}</small>
                                        </td>
                                        <td>
                                            <span
                                                data-bs-toggle="tooltip"
                                                data-bs-title="{{ $revision->created_at->toDayDateTimeString() }}">
                                                {{ $revision->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.moderation.recipes.show', $revision->id) }}" class="btn btn-sm btn-primary">
                                                Review
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-body-secondary border-0">{{ $revisions->links() }}</div>
            </div>
        @else
            <div class="alert alert-success d-flex align-items-center" role="status">
                <i class="fa-solid fa-circle-check me-2"></i>
                <div>No pending recipe submissions right now.</div>
            </div>
        @endif
    </div>
</x-app-layout>
