<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pengaduan;
use App\Models\Disposisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists to avoid duplicates
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'name' => 'Admin SIPPOL',
                'username' => 'admin',
                'email' => 'admin@sippol.com',
                'password' => Hash::make('admin'),
                'role' => 'super_admin',
            ]);
        }

        // Seed some Pengaduan and Disposisi for Trantib
        if (!Pengaduan::where('id_tiket', 'TKT-2026-99991')->exists()) {
            Pengaduan::create([
                'id_tiket' => 'TKT-2026-99991',
                'nama_pelapor' => 'Wayan Sudarta',
                'is_anonim' => false,
                'nomor_whatsapp' => '081234567890',
                'kategori_masalah' => 'PKL Liar',
                'kronologi' => 'Banyak pedagang kaki lima berjualan di sepanjang trotoar depan pasar Buleleng yang sangat mengganggu pejalan kaki dan menyebabkan kemacetan lalu lintas.',
                'latitude' => '-8.115456',
                'longitude' => '115.090544',
                'status_laporan' => 'Disposisi',
                'bidang_disposisi' => 'Bidang Trantib',
                'waktu_kirim' => now()->subHours(5),
            ]);

            Disposisi::create([
                'id_tiket' => 'TKT-2026-99991',
                'nama_admin' => 'Admin Utama',
                'waktu_verifikasi' => now()->subHours(4),
                'bidang_tujuan' => 'Bidang Trantib',
                'kedaruratan' => 'Segera',
                'catatan' => 'Tolong regu patroli segera merapat dan melakukan penertiban secara persuasif di lokasi tersebut.',
                'waktu_dikirim' => now()->subHours(4),
            ]);
        }

        // Seed for Linmas
        if (!Pengaduan::where('id_tiket', 'TKT-2026-99992')->exists()) {
            Pengaduan::create([
                'id_tiket' => 'TKT-2026-99992',
                'nama_pelapor' => 'Ketut Sari',
                'is_anonim' => true,
                'nomor_whatsapp' => '087654321098',
                'kategori_masalah' => 'ODGJ',
                'kronologi' => 'Ada seorang ODGJ mengamuk di depan pertokoan Jalan Diponegoro, Singaraja, berteriak-teriak dan membawa batu sehingga membahayakan warga sekitar.',
                'latitude' => '-8.116512',
                'longitude' => '115.088124',
                'status_laporan' => 'Disposisi',
                'bidang_disposisi' => 'Bidang Linmas',
                'waktu_kirim' => now()->subHours(8),
            ]);

            Disposisi::create([
                'id_tiket' => 'TKT-2026-99992',
                'nama_admin' => 'Admin Utama',
                'waktu_verifikasi' => now()->subHours(7),
                'bidang_tujuan' => 'Bidang Linmas',
                'kedaruratan' => 'Darurat',
                'catatan' => 'Segera koordinasikan dengan Dinas Sosial dan lakukan pengamanan terhadap warga agar situasi terkendali.',
                'waktu_dikirim' => now()->subHours(7),
            ]);
        }

        // Seed for Perada
        if (!Pengaduan::where('id_tiket', 'TKT-2026-99993')->exists()) {
            Pengaduan::create([
                'id_tiket' => 'TKT-2026-99993',
                'nama_pelapor' => 'Made Wirawan',
                'is_anonim' => false,
                'nomor_whatsapp' => '089876543210',
                'kategori_masalah' => 'Reklame/Baliho',
                'kronologi' => 'Sebuah baliho komersial besar yang melintang di dekat pertigaan jalan Ahmad Yani terpasang tanpa izin resmi dan sudah robek sehingga rawan roboh.',
                'latitude' => '-8.112411',
                'longitude' => '115.093412',
                'status_laporan' => 'Disposisi',
                'bidang_disposisi' => 'Bidang Perada',
                'waktu_kirim' => now()->subHours(12),
            ]);

            Disposisi::create([
                'id_tiket' => 'TKT-2026-99993',
                'nama_admin' => 'Admin Utama',
                'waktu_verifikasi' => now()->subHours(11),
                'bidang_tujuan' => 'Bidang Perada',
                'kedaruratan' => 'Biasa',
                'catatan' => 'Lakukan pemanggilan terhadap vendor/pemilik reklame untuk verifikasi izin resmi pemasangan baliho tersebut.',
                'waktu_dikirim' => now()->subHours(11),
            ]);
        }
    }
}
