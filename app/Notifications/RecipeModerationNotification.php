<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RecipeModerationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $recipeId,
        public string $recipeTitle,
        public int $revisionId,
        public string $status,
        public ?string $notes = null,
        public ?string $reviewedAt = null,
        public bool $isNewSubmission = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'recipe_id' => $this->recipeId,
            'recipe_title' => $this->recipeTitle,
            'revision_id' => $this->revisionId,
            'status' => $this->status,
            'notes' => $this->notes,
            'reviewed_at' => $this->reviewedAt,
            'is_new_submission' => $this->isNewSubmission,
        ];
    }
}
