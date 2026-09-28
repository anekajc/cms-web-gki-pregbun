<?php

namespace App\Models\Concerns;

use App\Support\ActivityRecorder;

/**
 * Feeds this model's created/updated/deleted field changes into the current
 * request's ActivityRecorder, which the RecordActivity middleware turns into a
 * Log Aktivitas entry. Mass updates (e.g. reorders) bypass model events and
 * are logged by the middleware alone, without a field diff.
 */
trait RecordsActivity
{
    public static function bootRecordsActivity(): void
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(fn ($model) => app(ActivityRecorder::class)->record($model, $event));
        }
    }

    /**
     * How this record is named in the log ("Kebaktian Umum", "Budi", ...).
     */
    public function activityLabel(): ?string
    {
        return $this->title ?? $this->name ?? ($this->getKey() ? '#'.$this->getKey() : null);
    }

    /**
     * Extra attributes to leave out of the diff, on top of the recorder's
     * global ignore list.
     *
     * @return list<string>
     */
    public function activityIgnoredFields(): array
    {
        return [];
    }
}
