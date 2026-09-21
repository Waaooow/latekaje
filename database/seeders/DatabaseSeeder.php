<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetItem;
use App\Models\Location;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $gudang = Location::firstOrCreate(
            ['key' => 'gudang'],
            ['label' => 'Gudang (Penyimpanan)']
        );

        Location::firstOrCreate(
            ['key' => 'ruang_kantor'],
            ['label' => 'Ruang Kantor']
        );

        $labTjkt = Location::firstOrCreate(
            ['key' => 'lab_tjkt'],
            ['label' => 'Lab TJKT']
        );

        Location::firstOrCreate(
            ['key' => 'lab_kkpi'],
            ['label' => 'Lab KKPI']
        );

        Location::firstOrCreate(
            ['key' => 'lab_fo'],
            ['label' => 'Lab FO']
        );

        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'password' => Hash::make('@Alfin488704'),
                'role' => 'superadmin',
            ]
        );

        foreach ([
            'x_tjkt_1' => 'X TJKT 1',
            'x_tjkt_2' => 'X TJKT 2',
            'xi_tjkt_1' => 'XI TJKT 1',
            'xi_tjkt_2' => 'XI TJKT 2',
            'xii_tjkt_1' => 'XII TJKT 1',
            'xii_tjkt_2' => 'XII TJKT 2',
            'guru_staf' => 'GURU / STAF',
            'tamu_eksternal' => 'Tamu / Eksternal',
        ] as $key => $label) {
            SchoolClass::firstOrCreate(['key' => $key], ['label' => $label]);
        }

        $asset = Asset::firstOrCreate(
            ['kode_aset' => 'TJKT-NET-001'],
            [
                'nama_alat' => 'Router Mikrotik',
                'jenis' => 'Networking',
                'spesifikasi' => 'RB450G',
                'kegunaan' => 'praktik',
                'stok' => 3,
            ]
        );

        $serials = ['LTKJ-2026-00001', 'LTKJ-2026-00002', 'LTKJ-2026-00003'];

        $items = [];
        foreach ($serials as $serial) {
            $items[] = AssetItem::firstOrCreate(
                ['nomor_seri_atau_qr' => $serial],
                [
                    'asset_id' => $asset->id,
                    'status' => 'tersedia',
                    'kondisi' => 'baik',
                    'location_id' => $labTjkt->id,
                ]
            );
        }

        try {
            LoanService::borrow($items[0]->id, 'Budi Santoso', 'X TJKT 1');
        } catch (\Throwable $e) {
            // Skip demo loan if it already exists or item is unavailable.
        }
    }
}
