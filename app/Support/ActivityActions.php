<?php

namespace App\Support;

/**
 * Which requests appear in Log Aktivitas, and how they're described.
 *
 * Route name => [menu key, action label]. Menu keys match App\Support\Access
 * page keys (plus "user"). Routes missing here — auth, own password/profile,
 * page visits — are never logged.
 */
class ActivityActions
{
    public const ACTIONS = [
        // Dashboard
        'warta.store' => ['dashboard', 'Menambah warta'],
        'warta.update' => ['dashboard', 'Mengubah warta'],
        'warta.destroy' => ['dashboard', 'Menghapus warta'],
        'home-video.store' => ['dashboard', 'Mengunggah video home'],
        'home-video.destroy' => ['dashboard', 'Menghapus video home'],

        // Tentang Kami
        'hamba-tuhan.store' => ['tentangkami', 'Menambah hamba Tuhan'],
        'hamba-tuhan.update' => ['tentangkami', 'Mengubah hamba Tuhan'],
        'hamba-tuhan.image.update' => ['tentangkami', 'Mengganti foto hamba Tuhan'],
        'hamba-tuhan.reorder' => ['tentangkami', 'Mengubah urutan hamba Tuhan'],
        'hamba-tuhan.destroy' => ['tentangkami', 'Menghapus hamba Tuhan'],

        // Ibadah
        'kebaktian.update' => ['ibadah', 'Mengubah informasi kebaktian'],
        'kebaktian.images.store' => ['ibadah', 'Menambah gambar kebaktian'],
        'kebaktian.images.reorder' => ['ibadah', 'Mengubah urutan gambar kebaktian'],
        'kebaktian.images.destroy' => ['ibadah', 'Menghapus gambar kebaktian'],
        'kebaktian.home.update' => ['ibadah', 'Mengubah keterangan tampilan home'],
        'kebaktian.home-image.store' => ['ibadah', 'Mengganti gambar tampilan home'],
        'kebaktian.home-image.destroy' => ['ibadah', 'Menghapus gambar tampilan home'],

        // Event
        'event.store' => ['event', 'Menambah kegiatan'],
        'event.update' => ['event', 'Mengubah kegiatan'],
        'event.destroy' => ['event', 'Menghapus kegiatan'],
        'event.image.store' => ['event', 'Mengganti gambar kegiatan'],
        'event.image.destroy' => ['event', 'Menghapus gambar kegiatan'],

        // Pelayanan
        'pelayanan.update' => ['pelayanan', 'Mengubah informasi pelayanan'],
        'pelayanan.images.store' => ['pelayanan', 'Menambah gambar pelayanan'],
        'pelayanan.images.reorder' => ['pelayanan', 'Mengubah urutan gambar pelayanan'],
        'pelayanan.images.destroy' => ['pelayanan', 'Menghapus gambar pelayanan'],
        'pelayanan.details.sync' => ['pelayanan', 'Mengubah detail pelayanan'],

        // Bajem Benowo
        'bajem-benowo.settings.update' => ['bajem', 'Mengubah pengaturan Bajem Benowo'],
        'bajem-benowo.settings.image.update' => ['bajem', 'Mengganti gambar Bajem Benowo'],
        'bajem-benowo.settings.image.destroy' => ['bajem', 'Menghapus gambar Bajem Benowo'],
        'bajem-benowo.items.store' => ['bajem', 'Menambah item Bajem Benowo'],
        'bajem-benowo.items.update' => ['bajem', 'Mengubah item Bajem Benowo'],
        'bajem-benowo.items.reorder' => ['bajem', 'Mengubah urutan item Bajem Benowo'],
        'bajem-benowo.items.image.update' => ['bajem', 'Mengganti gambar item Bajem Benowo'],
        'bajem-benowo.items.image.destroy' => ['bajem', 'Menghapus gambar item Bajem Benowo'],
        'bajem-benowo.items.destroy' => ['bajem', 'Menghapus item Bajem Benowo'],

        // Pembangunan
        'pembangunan.store' => ['pembangunan', 'Menambah data dana pembangunan'],
        'pembangunan.destroy' => ['pembangunan', 'Menghapus data dana pembangunan'],
        'pembangunan.video.store' => ['pembangunan', 'Menambah video pembangunan'],
        'pembangunan.video.destroy' => ['pembangunan', 'Menghapus video pembangunan'],
        'pembangunan.image.store' => ['pembangunan', 'Menambah gambar pembangunan'],
        'pembangunan.images.reorder' => ['pembangunan', 'Mengubah urutan gambar pembangunan'],
        'pembangunan.image.destroy' => ['pembangunan', 'Menghapus gambar pembangunan'],

        // Persembahan
        'persembahan.store' => ['persembahan', 'Menambah item persembahan'],
        'persembahan.update' => ['persembahan', 'Mengubah item persembahan'],
        'persembahan.destroy' => ['persembahan', 'Menghapus item persembahan'],
        'persembahan.reorder' => ['persembahan', 'Mengubah urutan item persembahan'],
        'persembahan.qr-image.store' => ['persembahan', 'Mengganti gambar QR'],
        'persembahan.qr-image.destroy' => ['persembahan', 'Menghapus gambar QR'],
        'persembahan.hero-image.store' => ['persembahan', 'Mengganti gambar hero persembahan'],
        'persembahan.hero-image.destroy' => ['persembahan', 'Menghapus gambar hero persembahan'],

        // Master
        'master.pelayanan.store' => ['master', 'Menambah pelayanan (master)'],
        'master.pelayanan.update' => ['master', 'Mengubah nama pelayanan (master)'],
        'master.pelayanan.reorder' => ['master', 'Mengubah urutan pelayanan (master)'],
        'master.pelayanan.destroy' => ['master', 'Menghapus pelayanan (master)'],

        // User management (admin)
        'user.store' => ['user', 'Menambah user'],
        'user.destroy' => ['user', 'Menghapus user'],
        'user.regenerate' => ['user', 'Membuat password baru'],
        'user.access.update' => ['user', 'Mengubah akses (Set Pemakai)'],
    ];

    /**
     * @return array{0: string, 1: string}|null [menu key, action label]
     */
    public static function for(?string $routeName): ?array
    {
        return $routeName !== null ? (self::ACTIONS[$routeName] ?? null) : null;
    }

    /**
     * Menu key => label, in sidebar order, for the filter dropdown and badges.
     *
     * @return array<string, string>
     */
    public static function menuLabels(): array
    {
        return [...Access::pageLabels(), 'user' => 'User'];
    }
}
