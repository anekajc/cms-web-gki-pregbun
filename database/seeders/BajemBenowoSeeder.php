<?php

namespace Database\Seeders;

use App\Models\BajemBenowoItem;
use App\Models\BajemBenowoSetting;
use Illuminate\Database\Seeder;

class BajemBenowoSeeder extends Seeder
{
    /**
     * Bootstraps the Bajem Benowo page with the text that used to be
     * hardcoded on the public site (src/pages/BajemBenowoPage.tsx and
     * src/components/BenowoSections.tsx). Idempotent: settings only fill
     * currently-empty fields, and items are matched by section+title so
     * reseeding never overwrites content already edited in the CMS. No
     * images — those are uploaded through the CMS afterwards.
     */
    public function run(): void
    {
        $this->seedSettings();
        $this->seedItems();
    }

    private function seedSettings(): void
    {
        $settings = BajemBenowoSetting::current();

        $defaults = [
            'about_description' => 'Perjalanan GKI Pregolan Bunder Bakal Jemaat (Bajem) Benowo berawal dari kerinduan sederhana sebuah keluarga untuk menghadirkan pelayanan Tuhan di wilayah Benowo. Kehadiran keluarga Ny. Sasmita, jemaat GKI Pregolan Bunder, di Perumahan Pondok Benowo Indah kemudian menjadi salah satu awal berkembangnya pelayanan di wilayah ini. Kerinduan tersebut mendapat perhatian dari Majelis Jemaat GKI Pregolan Bunder dan berkembang menjadi rencana untuk membuka Pos PI di Benowo. Pada 10 Mei 1998, kebaktian perdana dilaksanakan dan dipimpin oleh Pdt. Agus Surjanto, dihadiri oleh 18 orang termasuk majelis dan pendeta. Sejak saat itu, pelayanan di Benowo mulai bertumbuh dan menjadi bagian dari perjalanan pelayanan GKI Pregolan Bunder. Dalam perkembangannya, gereja menunjuk Bpk. Andreas Mintarso, S.Th sebagai pelayan penuh untuk mendampingi dan mengembangkan pelayanan di Benowo. Pada Agustus 2007, pelayanan kemudian dilanjutkan oleh Pnt. Ferry S. Kansil sebagai pembimbing. Dalam masa pelayanannya, jemaat Benowo terus bertumbuh, baik dalam kehidupan bersama maupun jumlah jemaat. Perjalanan itu memasuki babak baru pada 21 Februari 2021, ketika GKI Pregolan Bunder Pos Jemaat Benowo diresmikan oleh BPMS GKI di tengah situasi pandemi COVID-19. Tiga tahun kemudian, pada 21 Februari 2024, BPMS GKI kembali meresmikan perubahan status dari Pos Jemaat Benowo menjadi GKI Pregolan Bunder Bakal Jemaat Benowo. Setelah Pnt. Ferry S. Kansil memasuki masa pensiun pada tahun 2025, Majelis Jemaat GKI Pregolan Bunder kemudian mengangkat Sdr. Richard Sebua, S.Th sebagai pembimbing baru. Sejak Januari 2026, ia melanjutkan pelayanan bersama jemaat Benowo dalam perjalanan untuk terus bertumbuh dan menjadi berkat bagi lingkungan sekitarnya.',
            'pelayanan_intro' => 'Di luar ibadah mingguan, jemaat Bajem Benowo bertumbuh melalui pelayanan-pelayanan berikut.',
            'address' => "Jl. Pd. Benowo Indah Blk. OO1-5,\nSurabaya\nJawa Timur, Indonesia",
            'maps_url' => 'https://www.google.com/maps/search/?api=1&query=Benowo+Surabaya',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4910.549829324642!2d112.62451841226043!3d-7.2358545618556205!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd7ff004e78aa79%3A0x5fc1860ae31c8e49!2sGKI%20Bajem%20Benowo!5e0!3m2!1sen!2sid!4v1788404320393!5m2!1sen!2sid',
        ];

        // Only fill fields that are still empty, so a reseed never clobbers
        // content already edited in the CMS.
        $fill = collect($defaults)
            ->filter(fn ($value, $key) => blank($settings->{$key}))
            ->all();

        if ($fill !== []) {
            $settings->update($fill);
        }
    }

    private function seedItems(): void
    {
        $ibadah = [
            [
                'title' => 'Ibadah Umum',
                'description' => 'Ibadah Umum menjadi perjumpaan mingguan seluruh jemaat Bajem Benowo dalam pujian, doa, dan perenungan Firman Tuhan. Diselenggarakan setiap Minggu pagi di Bakal Jemaat Benowo, menjadi awal yang baik untuk melangkah memasuki minggu yang baru bersama Tuhan.',
                'schedules' => ['Minggu · 09.00 & 17.00 WIB'],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Seluruh jemaat & umum',
                'order' => 1,
            ],
            [
                'title' => 'Sekolah Minggu',
                'description' => 'Sekolah Minggu adalah tempat anak-anak Bajem Benowo bertumbuh mengenal kasih Tuhan Yesus melalui cerita, pujian, dan aktivitas yang menyenangkan. Berlangsung setiap Minggu pagi bersamaan dengan Ibadah Umum, di ruang khusus yang telah disiapkan bagi mereka.',
                'schedules' => ['Minggu · 07.30 WIB'],
                'location' => 'Ruang Sekolah Minggu, Bakal Jemaat Benowo',
                'audience' => 'Balita hingga pra-remaja',
                'order' => 2,
            ],
            [
                'title' => 'Pemuda & Remaja',
                'description' => 'Ibadah Pemuda & Remaja menjadi ruang bagi generasi muda Bajem Benowo untuk bertumbuh dalam iman lewat pujian yang hidup dan firman yang relevan dengan pergumulan sehari-hari. Diadakan setiap Sabtu sore, penuh kehangatan persahabatan dan semangat kebersamaan.',
                'schedules' => ['Sabtu II & IV · 18.30 WIB'],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Jemaat Pemuda Remaja',
                'order' => 3,
            ],
            [
                'title' => 'Persekutuan Senior',
                'description' => 'Persekutuan Senior menjadi wadah bagi jemaat lanjut usia Bajem Benowo untuk saling menguatkan dalam iman, berbagi kesaksian hidup, dan menikmati persekutuan yang hangat. Diadakan sebulan sekali pada hari Jumat sore, menjadi momen yang dinantikan untuk mempererat kasih persaudaraan.',
                'schedules' => ['Sebulan Sekali, Jumat · 17.00 WIB'],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Jemaat Senior',
                'order' => 4,
            ],
        ];

        $pelayanan = [
            [
                'title' => 'Doa Malam',
                'description' => 'Doa Malam menjadi kesempatan bagi jemaat untuk berhenti sejenak dari kesibukan dan membawa setiap pergumulan dalam doa bersama. Diadakan setiap Selasa petang di Bakal Jemaat Benowo, menjadi waktu yang menenangkan untuk kembali mendekat kepada Tuhan.',
                'schedules' => ['Selasa · 18.00 WIB'],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Jemaat Umum',
                'cadence' => 'Mingguan',
                'order' => 1,
            ],
            [
                'title' => 'Persekutuan Wilayah',
                'description' => 'Diadakan dua kali sebulan di masing-masing wilayah (Barat-barat, Timur, Barat Utara dan Selatan)',
                'schedules' => [],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Seluruh jemaat',
                'cadence' => '2× sebulan',
                'order' => 2,
            ],
            [
                'title' => 'Pelatihan Musik',
                'description' => 'Pelatihan Musik membekali jemaat yang melayani dalam pujian dan penyembahan untuk semakin siap dan percaya diri di setiap ibadah. Waktunya disepakati bersama sesuai kebutuhan, menjadi ruang bertumbuh sekaligus mengembangkan talenta yang dimiliki.',
                'schedules' => [],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Semua Jemaat',
                'cadence' => 'Terjadwal',
                'order' => 3,
            ],
            [
                'title' => 'Konseling',
                'description' => 'Pelayanan Konseling hadir sebagai tempat aman bagi jemaat untuk berbagi pergumulan pribadi maupun keluarga secara pribadi dan penuh kasih. Dilaksanakan sesuai perjanjian agar setiap orang dapat didampingi dengan waktu dan perhatian yang memadai.',
                'schedules' => ['Sesuai Perjanjian'],
                'location' => 'Bakal Jemaat Benowo',
                'audience' => 'Semua Jemaat',
                'cadence' => 'Pribadi',
                'order' => 4,
            ],
        ];

        foreach ($ibadah as $item) {
            BajemBenowoItem::firstOrCreate(
                ['section' => BajemBenowoItem::SECTION_IBADAH, 'title' => $item['title']],
                [...$item, 'section' => BajemBenowoItem::SECTION_IBADAH],
            );
        }

        foreach ($pelayanan as $item) {
            BajemBenowoItem::firstOrCreate(
                ['section' => BajemBenowoItem::SECTION_PELAYANAN, 'title' => $item['title']],
                [...$item, 'section' => BajemBenowoItem::SECTION_PELAYANAN],
            );
        }
    }
}
