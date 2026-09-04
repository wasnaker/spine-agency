<?php

declare(strict_types=1);

namespace Modules\Agency\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Modules\Agency\Models\Agency;
use Modules\Agency\Models\AgencyJurisdiction;
use Modules\Region\Models\Province;
use Modules\Region\Models\Regency;

/**
 * AgencyDemoSeeder — data demo Disnaker provinsi + unit kab/kota.
 *
 * Isi: agency = Dinas Tenaga Kerja (Disnaker) provinsi; unit = UPTD/unit
 * di kabupaten/kota di bawah provinsi tsb. Analogy: Customer=Disnaker,
 * Branch=Unit.
 *
 * Idempotent: firstOrCreate by code + parent.
 * Jalankan: php artisan db:seed --class="Modules\Agency\Database\Seeders\AgencyDemoSeeder"
 */
class AgencyDemoSeeder extends Seeder
{
    /** Nama Disnaker provinsi (10, kode regions). */
    private const AGENCIES = [
        ['code' => 'DKR-BANTEN',  'name' => 'Disnaker Banten',      'prov' => '36'],
        ['code' => 'DKR-JABAR',   'name' => 'Disnaker Jabar',       'prov' => '32'],
        ['code' => 'DKR-JATENG',  'name' => 'Disnaker Jateng',      'prov' => '33'],
        ['code' => 'DKR-JATIM',   'name' => 'Disnaker Jatim',       'prov' => '35'],
        ['code' => 'DKR-DKI',     'name' => 'Disnaker DKI Jakarta', 'prov' => '31'],
        ['code' => 'DKR-SUMSEL',  'name' => 'Disnaker Sumsel',      'prov' => '16'],
        ['code' => 'DKR-SUMUT',   'name' => 'Disnaker Sumut',       'prov' => '12'],
        ['code' => 'DKR-RIAU',    'name' => 'Disnaker Riau',        'prov' => '14'],
        ['code' => 'DKR-BALI',    'name' => 'Disnaker Bali',        'prov' => '51'],
        ['code' => 'DKR-SULSEL',  'name' => 'Disnaker Sulsel',      'prov' => '73'],
    ];

    /** Unit kab/kota per provinsi (1 unit per kab/kota; urut array = order). */
    private const UNITS = [
        '36' => ['Unit Kota Serang', 'Unit Kota Tangerang', 'Unit Kab. Tangerang'],
        '32' => ['Unit Kota Bandung', 'Unit Kota Bekasi', 'Unit Karawang'],
        '33' => ['Unit Kota Semarang', 'Unit Kota Surakarta', 'Unit Kab. Banyumas'],
        '35' => ['Unit Kota Surabaya', 'Unit Kota Malang', 'Unit Kab. Sidoarjo'],
        '31' => ['Unit Kota Jakarta Pusat', 'Unit Kota Jakarta Utara', 'Unit Kota Jakarta Selatan'],
        '16' => ['Unit Kota Palembang', 'Unit Kab. Banyuasin', 'Unit Kota Prabumulih'],
        '12' => ['Unit Kota Medan', 'Unit Kab. Deli Serdang', 'Unit Kota Binjai'],
        '14' => ['Unit Kota Pekanbaru', 'Unit Kota Dumai', 'Unit Kab. Kampar'],
        '51' => ['Unit Kota Denpasar', 'Unit Kab. Badung', 'Unit Kab. Gianyar'],
        '73' => ['Unit Kota Makassar', 'Unit Kab. Gowa', 'Unit Kota Parepare'],
    ];

    public function run(): void
    {
        $allProvinces = Province::pluck('id', 'code');
        if ($allProvinces->count() < count(self::AGENCIES)) {
            $this->command?->warn('Provinsi belum lengkap — jalankan RegionSeeder dulu.');
            return;
        }

        // 1 regency acak per provinsi, stabil utk demo
        $regencyByProvince = Regency::select('province_id', 'id')
            ->get()
            ->groupBy('province_id')
            ->map(fn ($r) => $r->first()->id);

        $adminPass = 'adminpass';
        $makeAdmin = function (string $code, string $name, int $salt, string $role): int {
            $email = strtolower("dg.{$code}.{$salt}@wasnaker.lan");
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'      => "Admin {$name}",
                    'password'  => Hash::make($adminPass),
                    'is_active' => true,
                ]
            );
            if (! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            return $user->id;
        };

        foreach (self::AGENCIES as $i => $a) {
            $provId = $allProvinces[$a['prov']] ?? null;
            if (! $provId) {
                continue;
            }

            $agency = Agency::firstOrCreate(
                ['code' => $a['code'], 'parent_id' => null],
                [
                    'name'       => $a['name'],
                    'email'      => strtolower($a['code']) . '@wasnaker.lan',
                    'phone'      => sprintf('021-555%04d', $i + 1),
                    'address'    => "Jl. Dinas Tenaga Kerja No. {$i}, " . ($allProvinces->search($provId) ?? ''),
                    'is_active'  => true,
                    'type'       => 'agency',
                ]
            );
            $agency->update([
                'admin_id'     => $makeAdmin($a['code'], $agency->name, $i, 'agency-admin'),
                'province_id'  => $provId,
                'regency_id'   => $regencyByProvince[$provId] ?? null,
            ]);

            foreach (self::UNITS[$a['prov']] ?? [] as $uIdx => $unitName) {
                $unitCode = $a['code'] . '-' . str_pad((string) ($uIdx + 1), 2, '0', STR_PAD_LEFT);
                $unit = Agency::firstOrCreate(
                    ['code' => $unitCode, 'parent_id' => $agency->id],
                    [
                        'name'       => $unitName,
                        'email'      => strtolower($unitCode) . '@wasnaker.lan',
                        'phone'      => sprintf('021-666%02d%02d', $i + 1, $uIdx + 1),
                        'address'    => "Jl. Unit No. {$i}{$uIdx}, {$unitName}",
                        'is_active'  => true,
                        'type'       => 'unit',
                    ]
                );
                $unit->update([
                    'admin_id'     => $makeAdmin($unitCode, $unit->name, $i + $uIdx, 'agency-unit-admin'),
                    'province_id'  => $provId,
                    'regency_id'   => $regencyByProvince[$provId] ?? null,
                ]);

                // Jurisdiction: 2-3 kab/kota dari provinsi yang sama, belum dipakai unit lain.
                $this->assignJurisdictions($unit, $provId);
            }
        }

        $this->command?->info(sprintf(
            'Demo data siap: %d Disnaker, %d unit, %d jurisdiction dalam agencies.',
            Agency::where('type', 'agency')->count(),
            Agency::where('type', 'unit')->count(),
            AgencyJurisdiction::count()
        ));
    }

    /** Assign regency milik provinsi yang sama; skip yang sudah terpakai. */
    private function assignJurisdictions(Agency $unit, int $provId): void
    {
        $taken = AgencyJurisdiction::pluck('regency_id');

        $candidates = Regency::where('province_id', $provId)
            ->whereNotIn('id', $taken)
            ->orderBy('id')
            ->take(3)
            ->pluck('id');

        foreach ($candidates as $rid) {
            $unit->jurisdictions()->firstOrCreate(['regency_id' => $rid]);
        }
    }
}