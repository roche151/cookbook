<x-app-layout>
    <x-slot name="title">Profile</x-slot>

    <div class="container py-md-5">

        <div class="mb-4">
            <h1 class="h3 mb-2">
                <i class="fa-solid fa-user-circle me-2 text-primary"></i>Profile Settings
            </h1>
            <p class="text-muted mb-0">Manage your account settings and preferences</p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card shadow-sm border-0 border-danger mb-4">
                    <div class="card-body p-4">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
