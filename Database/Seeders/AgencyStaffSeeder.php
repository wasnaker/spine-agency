<?php

namespace Modules\Agency\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\AgencyStaff;
use Spatie\Permission\PermissionRegistrar;

/**
 * Dibangun dari record DB (demo reset periodik — sumber kebenaran = DB).
 * Idempotent: re-run aman (firstOrCreate by email, updateOrCreate staff).
 *
 * Setiap staff: user login (email/name) + profil agency_staffs
 * (realname/jabatan/nip). unit_code null = bertugas di pusat (Disnaker HO).
 * Wajib jalan SETELAH AgencyDemoSeeder (agencies + admin users sudah ada).
 */
class AgencyStaffSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('adminpass');

        $staffs = [
            ['email' => 'dg.dkr-banten.0@wasnaker.lan', 'name' => 'Admin Disnaker Banten', 'realname' => 'Maya Hartono', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-jabar.1@wasnaker.lan', 'name' => 'Admin Disnaker Jabar', 'realname' => 'Lina Zahara', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-jateng.2@wasnaker.lan', 'name' => 'Admin Disnaker Jateng', 'realname' => 'Sari Nasution', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-jatim.3@wasnaker.lan', 'name' => 'Admin Disnaker Jatim', 'realname' => 'Fitri Firmansyah', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-dki.4@wasnaker.lan', 'name' => 'Admin Disnaker DKI Jakarta', 'realname' => 'Candra Utami', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'admin.dkr-sumsel@wasnaker.lan', 'name' => 'Admin Disnaker Sumsel', 'realname' => 'Sari Wulandari', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-sumut.6@wasnaker.lan', 'name' => 'Admin Disnaker Sumut', 'realname' => 'Indah Anggraini', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-riau.7@wasnaker.lan', 'name' => 'Admin Disnaker Riau', 'realname' => 'Hesti Yuliana', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-bali.8@wasnaker.lan', 'name' => 'Admin Disnaker Bali', 'realname' => 'Julia Simanjuntak', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-sulsel.9@wasnaker.lan', 'name' => 'Admin Disnaker Sulsel', 'realname' => 'Wawan Permatasari', 'jabatan' => 'Admin Dinas', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => null, 'role' => 'admin-dinas'],
            ['email' => 'dg.dkr-banten-01.0@wasnaker.lan', 'name' => 'Admin Unit Kota Serang', 'realname' => 'Dewi Nainggolan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BANTEN-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Serang', 'realname' => 'Vina Yuliana', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BANTEN-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Serang', 'realname' => 'Yuli Handayani', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-banten-02.1@wasnaker.lan', 'name' => 'Admin Unit Kota Tangerang', 'realname' => 'Agus Firmansyah', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BANTEN-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Tangerang', 'realname' => 'Candra Yuliana', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BANTEN-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Tangerang', 'realname' => 'Zainal Ginting', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-banten-03.2@wasnaker.lan', 'name' => 'Admin Unit Kab. Tangerang', 'realname' => 'Dedi Ramadhani', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BANTEN-03@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Tangerang', 'realname' => 'Andi Susanto', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BANTEN-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Tangerang', 'realname' => 'Hesti Siregar', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BANTEN', 'unit_code' => 'DKR-BANTEN-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jabar-01.1@wasnaker.lan', 'name' => 'Admin Unit Kota Bandung', 'realname' => 'Ayu Saragih', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JABAR-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Bandung', 'realname' => 'Dedi Wibowo', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JABAR-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Bandung', 'realname' => 'Oscar Saputra', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jabar-02.2@wasnaker.lan', 'name' => 'Admin Unit Kota Bekasi', 'realname' => 'Rahmat Kurniawan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JABAR-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Bekasi', 'realname' => 'Bella Firmansyah', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JABAR-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Bekasi', 'realname' => 'Oscar Kusuma', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jabar-03.3@wasnaker.lan', 'name' => 'Admin Unit Karawang', 'realname' => 'Lukman Harahap', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JABAR-03@wasnaker.lan', 'name' => 'Pengawas Unit Karawang', 'realname' => 'Hendra Susanto', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JABAR-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Karawang', 'realname' => 'Yanti Nasution', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JABAR', 'unit_code' => 'DKR-JABAR-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jateng-01.2@wasnaker.lan', 'name' => 'Admin Unit Kota Semarang', 'realname' => 'Agus Utami', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATENG-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Semarang', 'realname' => 'Maya Hartono', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATENG-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Semarang', 'realname' => 'Ayu Ramadhani', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jateng-02.3@wasnaker.lan', 'name' => 'Admin Unit Kota Surakarta', 'realname' => 'Dedi Tambunan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATENG-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Surakarta', 'realname' => 'Putri Wulandari', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATENG-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Surakarta', 'realname' => 'Yanti Santoso', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jateng-03.4@wasnaker.lan', 'name' => 'Admin Unit Kab. Banyumas', 'realname' => 'Tono Simarmata', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATENG-03@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Banyumas', 'realname' => 'Budi Lubis', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATENG-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Banyumas', 'realname' => 'Candra Yuliana', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATENG', 'unit_code' => 'DKR-JATENG-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jatim-01.3@wasnaker.lan', 'name' => 'Admin Unit Kota Surabaya', 'realname' => 'Umar Dalimunthe', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATIM-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Surabaya', 'realname' => 'Qori Lestari', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATIM-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Surabaya', 'realname' => 'Hesti Dalimunthe', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jatim-02.4@wasnaker.lan', 'name' => 'Admin Unit Kota Malang', 'realname' => 'Agus Purnama', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATIM-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Malang', 'realname' => 'Fitri Sinaga', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATIM-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Malang', 'realname' => 'Oki Lestari', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-jatim-03.5@wasnaker.lan', 'name' => 'Admin Unit Kab. Sidoarjo', 'realname' => 'Indah Susanto', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-JATIM-03@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Sidoarjo', 'realname' => 'Tono Yuliana', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-JATIM-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Sidoarjo', 'realname' => 'Fitri Purba', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-JATIM', 'unit_code' => 'DKR-JATIM-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-dki-01.4@wasnaker.lan', 'name' => 'Admin Unit Kota Jakarta Pusat', 'realname' => 'Krisna Nainggolan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-DKI-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Jakarta Pusat', 'realname' => 'Budi Saragih', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-DKI-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Jakarta Pusat', 'realname' => 'Maya Hartono', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-dki-02.5@wasnaker.lan', 'name' => 'Admin Unit Kota Jakarta Utara', 'realname' => 'Dewi Dalimunthe', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-DKI-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Jakarta Utara', 'realname' => 'Krisna Melati', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-DKI-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Jakarta Utara', 'realname' => 'Fitri Firmansyah', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-dki-03.6@wasnaker.lan', 'name' => 'Admin Unit Kota Jakarta Selatan', 'realname' => 'Eka Maulana', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-DKI-03@wasnaker.lan', 'name' => 'Pengawas Unit Kota Jakarta Selatan', 'realname' => 'Citra Ramadhani', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-DKI-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Jakarta Selatan', 'realname' => 'Dewi Kurniawan', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-DKI', 'unit_code' => 'DKR-DKI-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumsel-01.5@wasnaker.lan', 'name' => 'Admin Unit Kota Palembang', 'realname' => 'Wahyu Ginting', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMSEL-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Palembang', 'realname' => 'Umar Santoso', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMSEL-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Palembang', 'realname' => 'Gita Hartono', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumsel-02.6@wasnaker.lan', 'name' => 'Admin Unit Kab. Banyuasin', 'realname' => 'Slamet Zahara', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMSEL-02@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Banyuasin', 'realname' => 'Oki Ramadhani', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMSEL-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Banyuasin', 'realname' => 'Sari Nasution', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumsel-03.7@wasnaker.lan', 'name' => 'Admin Unit Kota Prabumulih', 'realname' => 'Gita Saragih', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMSEL-03@wasnaker.lan', 'name' => 'Pengawas Unit Kota Prabumulih', 'realname' => 'Hendra Saragih', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMSEL-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Prabumulih', 'realname' => 'Kartika Hartono', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMSEL', 'unit_code' => 'DKR-SUMSEL-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumut-01.6@wasnaker.lan', 'name' => 'Admin Unit Kota Medan', 'realname' => 'Oscar Panjaitan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMUT-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Medan', 'realname' => 'Putri Wibowo', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMUT-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Medan', 'realname' => 'Citra Situmorang', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumut-02.7@wasnaker.lan', 'name' => 'Admin Unit Kab. Deli Serdang', 'realname' => 'Tuti Hidayat', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMUT-02@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Deli Serdang', 'realname' => 'Lukman Manullang', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMUT-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Deli Serdang', 'realname' => 'Kartika Panjaitan', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sumut-03.8@wasnaker.lan', 'name' => 'Admin Unit Kota Binjai', 'realname' => 'Wahyu Yuliana', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SUMUT-03@wasnaker.lan', 'name' => 'Pengawas Unit Kota Binjai', 'realname' => 'Rahmat Panjaitan', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SUMUT-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Binjai', 'realname' => 'Budi Rahayu', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SUMUT', 'unit_code' => 'DKR-SUMUT-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-riau-01.7@wasnaker.lan', 'name' => 'Admin Unit Kota Pekanbaru', 'realname' => 'Slamet Ramadhani', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-RIAU-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Pekanbaru', 'realname' => 'Citra Pratama', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-RIAU-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Pekanbaru', 'realname' => 'Lina Sari', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-riau-02.8@wasnaker.lan', 'name' => 'Admin Unit Kota Dumai', 'realname' => 'Fitri Sari', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-RIAU-02@wasnaker.lan', 'name' => 'Pengawas Unit Kota Dumai', 'realname' => 'Wahyu Saputra', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-RIAU-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Dumai', 'realname' => 'Eka Tambunan', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-riau-03.9@wasnaker.lan', 'name' => 'Admin Unit Kab. Kampar', 'realname' => 'Julia Dalimunthe', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-RIAU-03@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Kampar', 'realname' => 'Candra Hutapea', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-RIAU-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Kampar', 'realname' => 'Dedi Hartono', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-RIAU', 'unit_code' => 'DKR-RIAU-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-bali-01.8@wasnaker.lan', 'name' => 'Admin Unit Kota Denpasar', 'realname' => 'Novi Kusuma', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BALI-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Denpasar', 'realname' => 'Umar Pratama', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BALI-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Denpasar', 'realname' => 'Oki Sihombing', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-bali-02.9@wasnaker.lan', 'name' => 'Admin Unit Kab. Badung', 'realname' => 'Oscar Ginting', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BALI-02@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Badung', 'realname' => 'Eka Setiawan', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BALI-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Badung', 'realname' => 'Dedi Saragih', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-bali-03.10@wasnaker.lan', 'name' => 'Admin Unit Kab. Gianyar', 'realname' => 'Kartika Irawan', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-BALI-03@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Gianyar', 'realname' => 'Sari Simarmata', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-BALI-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Gianyar', 'realname' => 'Gunawan Nainggolan', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-BALI', 'unit_code' => 'DKR-BALI-03', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sulsel-01.9@wasnaker.lan', 'name' => 'Admin Unit Kota Makassar', 'realname' => 'Eko Wulandari', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-01', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SULSEL-01@wasnaker.lan', 'name' => 'Pengawas Unit Kota Makassar', 'realname' => 'Vivi Zahara', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-01', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SULSEL-01@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Makassar', 'realname' => 'Qori Sinaga', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-01', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sulsel-02.10@wasnaker.lan', 'name' => 'Admin Unit Kab. Gowa', 'realname' => 'Budi Sianturi', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-02', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SULSEL-02@wasnaker.lan', 'name' => 'Pengawas Unit Kab. Gowa', 'realname' => 'Citra Wibowo', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-02', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SULSEL-02@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kab. Gowa', 'realname' => 'Julia Sari', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-02', 'role' => 'pengawas-spesialis'],
            ['email' => 'dg.dkr-sulsel-03.11@wasnaker.lan', 'name' => 'Admin Unit Kota Parepare', 'realname' => 'Oki Rahayu', 'jabatan' => 'Admin Unit', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-03', 'role' => 'agency-unit-admin'],
            ['email' => 'pengawas.DKR-SULSEL-03@wasnaker.lan', 'name' => 'Pengawas Unit Kota Parepare', 'realname' => 'Andi Sihombing', 'jabatan' => 'Pengawas Ketenagakerjaan', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-03', 'role' => 'pengawas'],
            ['email' => 'pengawas-spesialis.DKR-SULSEL-03@wasnaker.lan', 'name' => 'Pengawas Spesialis Unit Kota Parepare', 'realname' => 'Dedi Susanto', 'jabatan' => 'Pengawas Spesialis', 'nip' => null, 'agency_code' => 'DKR-SULSEL', 'unit_code' => 'DKR-SULSEL-03', 'role' => 'pengawas-spesialis'],
        ];

        foreach ($staffs as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                ['name' => $s['name'], 'password' => $password, 'is_active' => true]
            );

            if ($s['role']) {
                $user->assignRole($s['role']);
            }

            $agency = Agency::where('code', $s['agency_code'])->first();
            if (!$agency) {
                $this->command->warn("Agency code not found: {$s['agency_code']} (skip {$s['email']})");
                continue;
            }

            $unit = $s['unit_code'] ? Agency::where('code', $s['unit_code'])->first() : null;

            AgencyStaff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'agency_id' => $agency->id,
                    'unit_id'   => $unit?->id,
                    'realname'  => $s['realname'],
                    'jabatan'   => $s['jabatan'],
                    'nip'       => $s['nip'],
                    'is_active' => true,
                ]
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}