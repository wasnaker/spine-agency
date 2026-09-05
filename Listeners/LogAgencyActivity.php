<?php

declare(strict_types=1);

namespace Modules\Agency\Listeners;

use Modules\Agency\Models\Agency;
use Spine\Events\EntityCreated;
use Spine\Events\EntityDeleted;
use Spine\Events\EntityUpdated;
use Spine\Services\ActivityLogService;

class LogAgencyActivity
{
    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function created(EntityCreated $event): void
    {
        if (! $event->entity instanceof Agency) {
            return;
        }

        $this->activityLog->log(
            "Agency created: " . $this->label($event->entity),
            $event->entity,
            $this->user(),
            ['event' => 'created'],
        );
    }

    public function updated(EntityUpdated $event): void
    {
        if (! $event->entity instanceof Agency) {
            return;
        }

        $desc = (new \Modules\Agency\Support\ActivityLogText($event->changes, Agency::class))->render();

        $this->activityLog->log(
            "Agency updated: " . $this->label($event->entity) . " ({$desc})",
            $event->entity,
            $this->user(),
            ['event' => 'updated', 'changes' => $event->changes],
        );

        $status = $event->changes['is_active'] ?? null;
        if ($status && $status['old'] !== $status['new']) {
            $this->activityLog->log(
                "Agency status changed: " . $this->boolLabel($status['old']) . " -> " . $this->boolLabel($status['new']),
                $event->entity,
                $this->user(),
                ['event' => 'agency.status_changed', 'old' => $status['old'], 'new' => $status['new']],
            );
        }
    }

    public function deleted(EntityDeleted $event): void
    {
        if (! $event->entity instanceof Agency) {
            return;
        }

        $this->activityLog->log(
            "Agency deleted: " . $this->label($event->entity),
            null,
            $this->user(),
            ['event' => 'deleted', 'id' => $event->entity->getKey()],
            null,
            $event->entityType,
        );
    }

    private function boolLabel(mixed $value): string
    {
        return $value ? 'Aktif' : 'Nonaktif';
    }

    private function label($entity): string
    {
        return (string) ($entity->name ?? $entity->getKey());
    }

    private function user(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return auth('sanctum')->user() ?? auth()->user();
    }
}