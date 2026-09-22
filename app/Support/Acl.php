<?php

namespace App\Support;

class Acl
{
    /** ability => label Indonesia. Key format: "{policyMethod}:{ModelBasename}". */
    public const ABILITIES = [
        'viewAny:User' => 'Buka Kelola User',
        'create:User' => 'Buat user',
        'update:User' => 'Ubah user / password',
        'delete:User' => 'Hapus user',
        'viewAny:Asset' => 'Lihat katalog Aset',
        'create:Asset' => 'Buat aset',
        'update:Asset' => 'Ubah aset',
        'delete:Asset' => 'Hapus aset',
        'viewAny:AssetItem' => 'Lihat Data Unit',
        'create:AssetItem' => 'Tambah unit',
        'update:AssetItem' => 'Ubah unit',
        'delete:AssetItem' => 'Hapus unit',
        'viewAny:Loan' => 'Lihat Peminjaman',
        'create:Loan' => 'Buat pinjaman',
        'update:Loan' => 'Ubah / kembalikan pinjaman',
        'delete:Loan' => 'Hapus riwayat pinjam',
        'viewAny:Location' => 'Lihat Lokasi',
        'viewAny:SchoolClass' => 'Lihat Kelas',
        'viewAny:Student' => 'Lihat Siswa',
        'create:Student' => 'Tambah siswa',
    ];

    public static function key(string $ability, mixed $subject): string
    {
        if (is_object($subject)) {
            $subject = $subject::class;
        }

        $name = is_string($subject) ? class_basename($subject) : 'global';

        return $ability.':'.$name;
    }
}
