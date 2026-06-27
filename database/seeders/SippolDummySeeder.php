<?php

namespace Database\Seeders;

use App\Models\SatpolKegiatan;
use App\Models\Satlinmas;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SippolDummySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed SatpolKegiatan (Data Kegiatan Lapangan)
        $kegiatans = [
            [
                'no_kegiatan' => 'JRN-20260620-001',
                'tanggal_kegiatan' => Carbon::now()->subDays(7)->setTime(9, 0),
                'bidang' => 'Trantibum',
                'jenis_kegiatan' => 'Patroli Ketertiban Umum',
                'lokasi' => 'Eks Pelabuhan Buleleng, Singaraja',
                'jumlah_personel' => 12,
                'uraian_kegiatan' => 'Melaksanakan patroli ketertiban umum di sekitar kawasan wisata Eks Pelabuhan Buleleng untuk menertibkan pedagang kaki lima (PKL) yang berjualan di atas trotoar dan memastikan situasi kondusif.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260621-002',
                'tanggal_kegiatan' => Carbon::now()->subDays(6)->setTime(10, 30),
                'bidang' => 'Trantibum',
                'jenis_kegiatan' => 'Pengamanan Aset & Protokoler',
                'lokasi' => 'Kantor Bupati Buleleng, Jl. Pahlawan',
                'jumlah_personel' => 8,
                'uraian_kegiatan' => 'Melakukan pengamanan intensif di area gerbang masuk utama Kantor Bupati Buleleng sehubungan dengan adanya Rapat Koordinasi Forkopimda tingkat Kabupaten.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260622-003',
                'tanggal_kegiatan' => Carbon::now()->subDays(5)->setTime(8, 0),
                'bidang' => 'Perada',
                'jenis_kegiatan' => 'Sidak Masker / Kawasan Tanpa Rokok (KTR)',
                'lokasi' => 'RSUD Buleleng, Jl. Ngurah Rai',
                'jumlah_personel' => 6,
                'uraian_kegiatan' => 'Melaksanakan sidak penegakan Perda Kawasan Tanpa Rokok (KTR) di lingkungan RSUD Kabupaten Buleleng. Memberikan teguran lisan kepada 3 pengunjung yang melanggar.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260623-004',
                'tanggal_kegiatan' => Carbon::now()->subDays(4)->setTime(19, 0),
                'bidang' => 'Linmas',
                'jenis_kegiatan' => 'Pembinaan Kamling & Pos Ronda',
                'lokasi' => 'Desa Pancasari, Sukasada',
                'jumlah_personel' => 4,
                'uraian_kegiatan' => 'Melaksanakan kunjungan dan pembinaan berkala ke Pos Ronda Kamling Dusun Buyan, Desa Pancasari untuk memotivasi anggota Linmas setempat dalam menjaga keamanan lingkungan.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260624-005',
                'tanggal_kegiatan' => Carbon::now()->subDays(3)->setTime(13, 0),
                'bidang' => 'SDA',
                'jenis_kegiatan' => 'Penyuluhan Hukum Perda',
                'lokasi' => 'Balai Desa Bebetin, Sawan',
                'jumlah_personel' => 5,
                'uraian_kegiatan' => 'Menyelenggarakan sosialisasi dan edukasi regulasi daerah terkait Perda Ketertiban Umum kepada aparat desa dan tokoh masyarakat Desa Bebetin.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260625-006',
                'tanggal_kegiatan' => Carbon::now()->subDays(2)->setTime(22, 0),
                'bidang' => 'Trantibum',
                'jenis_kegiatan' => 'Patroli K3 & Penertiban PKL',
                'lokasi' => 'Pasar Anyar Singaraja, Jl. Diponegoro',
                'jumlah_personel' => 15,
                'uraian_kegiatan' => 'Melaksanakan operasi malam penertiban pedagang pasar tumpah yang melebihi batas jam operasional di bahu jalan sekitar Pasar Anyar Singaraja guna kelancaran arus lalu lintas pagi.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260626-007',
                'tanggal_kegiatan' => Carbon::now()->subDays(1)->setTime(14, 0),
                'bidang' => 'Perada',
                'jenis_kegiatan' => 'Pemeriksaan Izin Usaha',
                'lokasi' => 'Kawasan Hotel & Bar Lovina, Kalibukbuk',
                'jumlah_personel' => 10,
                'uraian_kegiatan' => 'Melakukan pengecekan dokumen perizinan dan pematuhan jam operasional tempat hiburan malam di wilayah wisata Lovina, Kabupaten Buleleng.',
                'foto_bukti' => null,
            ],
            [
                'no_kegiatan' => 'JRN-20260627-008',
                'tanggal_kegiatan' => Carbon::now()->setTime(10, 0),
                'bidang' => 'Linmas',
                'jenis_kegiatan' => 'Simulasi Penanggulangan Bencana',
                'lokasi' => 'Pesisir Pantai Sangsit, Sawan',
                'jumlah_personel' => 20,
                'uraian_kegiatan' => 'Mendampingi simulasi tanggap darurat bencana tsunami dan evakuasi mandiri bersama Satlinmas Desa Sangsit dan BPBD Kabupaten Buleleng.',
                'foto_bukti' => null,
            ],
        ];

        foreach ($kegiatans as $kegiatan) {
            SatpolKegiatan::create($kegiatan);
        }

        // 2. Seed Satlinmas (Data Anggota Linmas)
        $satlinmas = [
            [
                'kecamatan' => 'Sukasada',
                'desa' => 'Pancasari',
                'anggota_pria' => 25,
                'anggota_wanita' => 5,
                'nama_kades' => 'I Wayan Dedi',
                'nama_kasi' => 'Ketut Sukrata',
                'kontak_perangkat' => '081234567890',
                'jumlah_pos_kamling' => 4,
                'status_pakaian_dinas' => 'Ada',
                'ket_pakaian_dinas' => 'Seragam Linmas Baru pembagian tahun 2025 lengkap atribut.',
                'jumlah_senter' => 6,
                'jumlah_pentungan' => 30,
                'jumlah_ht' => 2,
                'anggaran_honor' => 250000,
                'status_sk_satlinmas' => 'Ada',
                'peraturan_desa' => 'Perdes No. 3 Tahun 2023 tentang Keamanan Lingkungan',
                'status_struktur' => 'Ada',
                'pelatihan_anggota' => 'Pernah',
                'status_kta' => 'Ada (Digital)',
                'petugas_pendata' => 'I Made Budi',
                'tanggal_pendataan' => Carbon::now(),
            ],
            [
                'kecamatan' => 'Sawan',
                'desa' => 'Bebetin',
                'anggota_pria' => 18,
                'anggota_wanita' => 2,
                'nama_kades' => 'Gede Ariawan',
                'nama_kasi' => 'Ketut Sugiartha',
                'kontak_perangkat' => '081987654321',
                'jumlah_pos_kamling' => 3,
                'status_pakaian_dinas' => 'Ada',
                'ket_pakaian_dinas' => 'Seragam hijau standar Satlinmas kondisi baik.',
                'jumlah_senter' => 4,
                'jumlah_pentungan' => 20,
                'jumlah_ht' => 0,
                'anggaran_honor' => 200000,
                'status_sk_satlinmas' => 'Ada',
                'peraturan_desa' => 'Perdes No. 2 Tahun 2022',
                'status_struktur' => 'Ada',
                'pelatihan_anggota' => 'Pernah',
                'status_kta' => 'Ada (Fisik)',
                'petugas_pendata' => 'Putu Adi',
                'tanggal_pendataan' => Carbon::now(),
            ],
            [
                'kecamatan' => 'Banjar',
                'desa' => 'Munduk',
                'anggota_pria' => 20,
                'anggota_wanita' => 4,
                'nama_kades' => 'Ketut Rencana',
                'nama_kasi' => 'Nyoman Darsa',
                'kontak_perangkat' => '087766554433',
                'jumlah_pos_kamling' => 5,
                'status_pakaian_dinas' => 'Ada',
                'ket_pakaian_dinas' => 'Pembagian seragam baru dari desa.',
                'jumlah_senter' => 8,
                'jumlah_pentungan' => 24,
                'jumlah_ht' => 4,
                'anggaran_honor' => 300000,
                'status_sk_satlinmas' => 'Ada',
                'peraturan_desa' => 'Perdes Ketertiban Wilayah Wisata',
                'status_struktur' => 'Ada',
                'pelatihan_anggota' => 'Pernah',
                'status_kta' => 'Ada (Digital)',
                'petugas_pendata' => 'Wayan Juni',
                'tanggal_pendataan' => Carbon::now(),
            ],
            [
                'kecamatan' => 'Seririt',
                'desa' => 'Ringdikit',
                'anggota_pria' => 15,
                'anggota_wanita' => 0,
                'nama_kades' => 'I Made Swastika',
                'nama_kasi' => 'Gede Pastika',
                'kontak_perangkat' => '085544332211',
                'jumlah_pos_kamling' => 2,
                'status_pakaian_dinas' => 'Tidak Ada',
                'ket_pakaian_dinas' => 'Seragam lama rusak, sedang pengajuan APBDes.',
                'jumlah_senter' => 2,
                'jumlah_pentungan' => 15,
                'jumlah_ht' => 0,
                'anggaran_honor' => 150000,
                'status_sk_satlinmas' => 'Ada',
                'peraturan_desa' => null,
                'status_struktur' => 'Ada',
                'pelatihan_anggota' => 'Belum Pernah',
                'status_kta' => 'Tidak Ada',
                'petugas_pendata' => 'Komang Tri',
                'tanggal_pendataan' => Carbon::now(),
            ],
        ];

        foreach ($satlinmas as $sat) {
            Satlinmas::create($sat);
        }
    }
}
