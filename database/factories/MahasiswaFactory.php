<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    /**
     * Track sequential NIM counters per angkatan year.
     *
     * @var array<int, int>
     */
    protected static array $nimCounters = [];

    /**
     * Reset NIM counters (useful for testing).
     */
    public static function resetNimCounters(): void
    {
        static::$nimCounters = [];
    }

    /**
     * Get next sequential NIM for a given angkatan.
     */
    protected static function getNextNim(int $angkatan): string
    {
        if (! isset(static::$nimCounters[$angkatan])) {
            static::$nimCounters[$angkatan] = 1;
        }

        $yearShort = substr((string) $angkatan, -2);
        $sequence = str_pad((string) static::$nimCounters[$angkatan], 3, '0', STR_PAD_LEFT);
        static::$nimCounters[$angkatan]++;

        return $yearShort.'106050'.$sequence;
    }

    /**
     * Realistic thesis titles by profil lulusan and penjurusan.
     *
     * @var array<string, array<string, array<string>>>
     */
    protected static array $judulByProfilPenjurusan = [
        'Ilmuwan' => [
            'Sistem Informasi' => [
                'Analisis Perbandingan Metode Data Mining untuk Prediksi Kelulusan Mahasiswa',
                'Studi Komparatif Algoritma Klasifikasi pada Sistem Pendukung Keputusan Penerima Beasiswa',
                'Pengembangan Model Analitik untuk Evaluasi Kinerja Sistem Informasi Akademik',
                'Analisis Faktor-Faktor yang Mempengaruhi Adopsi Sistem E-Learning Menggunakan TAM',
                'Studi Eksperimental Metode TOPSIS dan SAW dalam Pemilihan Supplier',
            ],
            'Perekayasa Perangkat Lunak' => [
                'Analisis Perbandingan Arsitektur Microservices dan Monolithic pada Aplikasi Skala Besar',
                'Studi Komparatif Framework JavaScript Modern untuk Pengembangan Single Page Application',
                'Pengembangan Metrik Kualitas Kode untuk Evaluasi Maintainability Perangkat Lunak',
                'Analisis Efektivitas Test-Driven Development pada Proyek Agile',
                'Studi Eksperimental Pengaruh Code Review terhadap Kualitas Perangkat Lunak',
            ],
            'Perekayasa Jaringan Komputer' => [
                'Analisis Perbandingan Protokol Routing OSPF dan EIGRP pada Jaringan Enterprise',
                'Studi Komparatif Metode Deteksi Intrusi Berbasis Anomali dan Signature',
                'Pengembangan Model Prediksi Trafik Jaringan Menggunakan Time Series Analysis',
                'Analisis Kinerja Software-Defined Networking pada Data Center Virtual',
                'Studi Eksperimental Keamanan Protokol IoT pada Smart Home System',
            ],
            'Sistem Cerdas' => [
                'Analisis Perbandingan Algoritma Deep Learning untuk Klasifikasi Citra Medis',
                'Pengembangan Model NLP Bahasa Indonesia untuk Ekstraksi Entitas Bernama',
                'Studi Komparatif Metode Ensemble Learning untuk Prediksi Harga Saham',
                'Implementasi dan Evaluasi Algoritma Reinforcement Learning pada Game Strategy',
                'Analisis Performa Model Transformer untuk Summarisasi Teks Otomatis',
            ],
        ],
        'Wirausaha' => [
            'Sistem Informasi' => [
                'Rancang Bangun Sistem Informasi Manajemen UMKM dengan Fitur Laporan Keuangan Otomatis',
                'Pengembangan Platform E-Commerce untuk Produk Kerajinan Lokal dengan Payment Gateway',
                'Sistem Informasi Reservasi dan Manajemen Klinik Kecantikan Berbasis Web',
                'Aplikasi Manajemen Inventori dan POS untuk Toko Retail dengan Barcode Scanner',
                'Platform Digital Pengelolaan Koperasi Simpan Pinjam dengan Fitur Mobile Banking',
            ],
            'Perekayasa Perangkat Lunak' => [
                'Pengembangan Aplikasi Mobile Marketplace Jasa Freelancer dengan Sistem Rating',
                'Rancang Bangun Platform SaaS untuk Manajemen Proyek Tim Remote',
                'Aplikasi Mobile Food Delivery dengan Fitur Real-Time Tracking dan Chat',
                'Pengembangan Sistem Booking Online untuk Lapangan Olahraga dengan Pembayaran Digital',
                'Platform Crowdfunding Berbasis Web untuk Startup Kreatif Indonesia',
            ],
            'Perekayasa Jaringan Komputer' => [
                'Rancang Bangun Sistem Monitoring Jaringan Berbasis Cloud untuk ISP Lokal',
                'Pengembangan Layanan VPN as a Service untuk Keamanan UMKM',
                'Sistem Manajemen Bandwidth dan Hotspot untuk Usaha Warnet dan Coworking Space',
                'Platform IoT untuk Monitoring dan Kontrol Smart Farming Skala UMKM',
                'Pengembangan Sistem Keamanan CCTV Terintegrasi dengan Notifikasi Mobile',
            ],
            'Sistem Cerdas' => [
                'Pengembangan Chatbot Customer Service untuk E-Commerce Menggunakan NLP',
                'Aplikasi Mobile Rekomendasi Produk Fashion dengan Computer Vision',
                'Sistem Prediksi Demand Inventory untuk UMKM Menggunakan Machine Learning',
                'Platform Analisis Sentimen Ulasan Produk untuk Seller Marketplace',
                'Aplikasi Pengenalan Tanaman Hias dengan AI untuk Toko Tanaman Online',
            ],
        ],
        'Profesional' => [
            'Sistem Informasi' => [
                'Perancangan Sistem Informasi Kepegawaian Terintegrasi untuk Instansi Pemerintah',
                'Implementasi Enterprise Resource Planning pada Perusahaan Manufaktur Menengah',
                'Pengembangan Dashboard Business Intelligence untuk Monitoring KPI Perusahaan',
                'Sistem Manajemen Dokumen Elektronik dengan Fitur Digital Signature dan Workflow',
                'Rancang Bangun Sistem Informasi Akademik dengan Integrasi SSO dan API Gateway',
            ],
            'Perekayasa Perangkat Lunak' => [
                'Implementasi CI/CD Pipeline untuk Pengembangan Aplikasi Enterprise dengan Docker',
                'Pengembangan API Gateway untuk Integrasi Sistem Legacy dengan Microservices',
                'Rancang Bangun Sistem Backend Scalable dengan Arsitektur Event-Driven',
                'Implementasi Design Pattern Repository dan Unit of Work pada Aplikasi Enterprise',
                'Pengembangan Framework Testing Otomatis untuk Quality Assurance Tim Development',
            ],
            'Perekayasa Jaringan Komputer' => [
                'Implementasi Zero Trust Network Architecture pada Infrastruktur Perusahaan',
                'Sistem Monitoring Infrastruktur Jaringan Berbasis SNMP dengan Alert Real-Time',
                'Perancangan Disaster Recovery dan Business Continuity untuk Data Center',
                'Implementasi Network Segmentation dan Firewall Policy pada Jaringan Kampus',
                'Pengembangan Sistem Manajemen Log Terpusat dengan ELK Stack untuk SOC',
            ],
            'Sistem Cerdas' => [
                'Implementasi Sistem Deteksi Fraud Transaksi Keuangan dengan Machine Learning',
                'Pengembangan Model Prediktif untuk Maintenance Peralatan Industri',
                'Sistem Klasifikasi Dokumen Otomatis untuk Arsip Digital Perusahaan',
                'Implementasi Face Recognition untuk Sistem Absensi Karyawan',
                'Pengembangan Sistem Analisis Sentimen Media Sosial untuk Brand Monitoring',
            ],
        ],
    ];

    /**
     * Get a random thesis title based on profil lulusan and penjurusan.
     */
    protected static function getJudul(string $profil, string $penjurusan): string
    {
        $titles = static::$judulByProfilPenjurusan[$profil][$penjurusan]
            ?? static::$judulByProfilPenjurusan['Profesional']['Sistem Informasi'];

        return fake()->randomElement($titles);
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $angkatan = fake()->numberBetween(2020, 2025);
        $profilLulusan = fake()->randomElement(['Ilmuwan', 'Wirausaha', 'Profesional']);
        $penjurusan = fake()->randomElement([
            'Sistem Informasi',
            'Perekayasa Perangkat Lunak',
            'Perekayasa Jaringan Komputer',
            'Sistem Cerdas',
        ]);

        return [
            'nim' => fn (array $attributes) => static::getNextNim($attributes['angkatan'] ?? $angkatan),
            'nama' => fake()->firstName().' '.fake()->lastName(),
            'angkatan' => $angkatan,
            'judul_skripsi' => static::getJudul($profilLulusan, $penjurusan),
            'profil_lulusan' => $profilLulusan,
            'penjurusan' => $penjurusan,
            'id_dospem' => Dosen::factory(),
            'siap_sidang' => false,
            'is_prioritas' => false,
            'keterangan_prioritas' => null,
        ];
    }

    /**
     * State untuk mahasiswa siap sidang.
     */
    public function siapSidang(): static
    {
        return $this->state(fn (array $attributes) => [
            'siap_sidang' => true,
        ]);
    }

    /**
     * State untuk mahasiswa prioritas.
     */
    public function prioritas(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_prioritas' => true,
            'keterangan_prioritas' => fake()->sentence(),
        ]);
    }

    /**
     * State untuk menggunakan dosen tertentu.
     */
    public function forDosen(Dosen $dosen): static
    {
        return $this->state(fn (array $attributes) => [
            'id_dospem' => $dosen->id,
        ]);
    }

    /**
     * State untuk angkatan tertentu.
     */
    public function angkatan(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'angkatan' => $year,
            'nim' => static::getNextNim($year),
        ]);
    }
}
