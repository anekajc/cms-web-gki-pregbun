<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Collects the field-level changes made during one request so the
 * RecordActivity middleware can attach them to that request's log entry.
 *
 * Models feed it through the RecordsActivity trait; controllers call detail()
 * for changes model events can't see (mass deletes, permission syncs).
 */
class ActivityRecorder
{
    /** Never shown: bookkeeping, Cloudinary ids, derived values, secrets. */
    private const IGNORED = [
        'id', 'order', 'slug', 'created_at', 'updated_at', 'public_id',
        'password', 'generated_password', 'remember_token', 'must_change_password', 'email_verified_at',
        'youtube_embed_url',
    ];

    private const LABELS = [
        'title' => 'Judul',
        'name' => 'Nama',
        'email' => 'Email',
        'role' => 'Peran',
        'description' => 'Deskripsi',
        'subtitle' => 'Subjudul',
        'schedules' => 'Jadwal',
        'location' => 'Lokasi',
        'audience' => 'Peserta',
        'cadence' => 'Frekuensi',
        'section' => 'Bagian',
        'youtube_url' => 'Link YouTube',
        'url' => 'Gambar',
        'image_url' => 'Gambar',
        'home_image_url' => 'Gambar Home',
        'home_subtitle' => 'Keterangan Home',
        'service_date' => 'Tanggal Ibadah',
        'source_url' => 'Link Warta',
        'video_16x9_url' => 'Video 16:9',
        'video_4x3_url' => 'Video 4:3',
        'type' => 'Jenis',
        'event_date' => 'Tanggal',
        'day' => 'Hari',
        'start_time' => 'Jam Mulai',
        'end_time' => 'Jam Selesai',
        'details' => 'Detail',
        'contact' => 'Kontak',
        'category' => 'Kategori',
        'entity' => 'Atas Nama',
        'bank' => 'Bank',
        'rekening' => 'No. Rekening',
        'display_rekening' => 'Tampilan Rekening',
        'qr_url' => 'Gambar QR',
        'hero_image_url' => 'Gambar Hero',
        'update_date' => 'Tanggal Update',
        'target_persembahan' => 'Target Persembahan',
        'persembahan_pembangunan' => 'Persembahan Pembangunan',
        'janji_iman_terealisasi' => 'Janji Iman Terealisasi',
        'janji_iman_belum_terealisasi' => 'Janji Iman Belum Terealisasi',
        'rincian_start_date' => 'Rincian Mulai',
        'rincian_end_date' => 'Rincian Selesai',
        'about_description' => 'Deskripsi Tentang',
        'about_image_url' => 'Gambar Tentang',
        'pelayanan_intro' => 'Intro Pelayanan',
        'address' => 'Alamat',
        'maps_url' => 'Link Google Maps',
        'map_embed_url' => 'Embed Peta',
        'location_image_url' => 'Foto Lokasi',
    ];

    /** @var list<array{label: string, old: mixed, new: mixed}> */
    private array $changes = [];

    private ?string $subject = null;

    private ?string $imageUrl = null;

    public function reset(): void
    {
        $this->changes = [];
        $this->subject = null;
        $this->imageUrl = null;
    }

    /**
     * @param  'created'|'updated'|'deleted'  $event
     */
    public function record(Model $model, string $event): void
    {
        $ignored = method_exists($model, 'activityIgnoredFields') ? $model->activityIgnoredFields() : [];
        $label = method_exists($model, 'activityLabel') ? $model->activityLabel() : null;

        $keys = match ($event) {
            'updated' => array_keys($model->getChanges()),
            default => array_keys($model->getAttributes()),
        };

        $entries = [];

        foreach ($keys as $key) {
            if ($this->isIgnored($key) || in_array($key, $ignored, true)) {
                continue;
            }

            $old = $event === 'created' ? null : $this->normalize($model->getOriginal($key));
            $new = $event === 'deleted' ? null : $this->normalize($model->getAttribute($key));

            if ($old === $new) {
                continue;
            }

            $entries[] = ['key' => $key, 'old' => $old, 'new' => $new];

            if (is_string($new) && str_contains($new, '/image/upload/') && $this->isUrlField($key)) {
                $this->imageUrl = $new;
            }
        }

        // An update that only touched ignored fields (e.g. reorder bookkeeping)
        // doesn't make this model the subject.
        if ($entries === [] && $event === 'updated') {
            return;
        }

        $this->subject ??= $label;

        // A second model changed in the same request: prefix its label so the
        // admin can tell which item each field belongs to.
        $prefix = $label !== null && $label !== $this->subject ? $label.' › ' : '';

        foreach ($entries as $entry) {
            $this->changes[] = [
                'label' => $prefix.(self::LABELS[$entry['key']] ?? Str::headline($entry['key'])),
                'old' => $entry['old'],
                'new' => $entry['new'],
            ];
        }
    }

    /**
     * Record an explicit before/after pair for changes model events can't see.
     */
    public function detail(string $label, mixed $old, mixed $new): void
    {
        $old = $this->normalize($old);
        $new = $this->normalize($new);

        if ($old !== $new) {
            $this->changes[] = ['label' => $label, 'old' => $old, 'new' => $new];
        }
    }

    /** @return list<array{label: string, old: mixed, new: mixed}> */
    public function changes(): array
    {
        return $this->changes;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    public function imageUrl(): ?string
    {
        return $this->imageUrl;
    }

    private function isIgnored(string $key): bool
    {
        return in_array($key, self::IGNORED, true)
            || str_ends_with($key, '_public_id')
            || str_ends_with($key, '_id');
    }

    private function isUrlField(string $key): bool
    {
        return $key === 'url' || str_ends_with($key, '_url');
    }

    /**
     * Collapse values into plain JSON-friendly scalars/arrays so equality checks
     * are meaningful and the log renders the same way it was recorded.
     */
    private function normalize(mixed $value): mixed
    {
        return match (true) {
            $value === null, $value === '', $value === [] => null,
            $value instanceof DateTimeInterface => $value->format('H:i:s') === '00:00:00'
                ? $value->format('Y-m-d')
                : $value->format('Y-m-d H:i'),
            is_bool($value) => $value ? 'Ya' : 'Tidak',
            is_array($value) => array_map(fn ($v) => $this->normalize($v), $value),
            default => $value,
        };
    }
}
