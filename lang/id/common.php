<?php

return [
    // Shared actions
    'edit' => 'Ubah',
    'delete' => 'Hapus',
    'save' => 'Simpan',
    'cancel' => 'Batal',
    'yes' => 'Ya',
    'no' => 'Tidak',
    'import' => 'Import',
    'template_csv' => 'Template CSV',

    // Generic words
    'status' => 'Status',
    'active' => 'Aktif',
    'all' => 'Semua',

    // Navigation
    'nav_dashboard' => 'Dasbor',
    'nav_units' => 'Data Unit/QR',
    'nav_assets' => 'Aset',
    'nav_loans' => 'Peminjaman',
    'nav_users' => 'Kelola User',
    'nav_locations' => 'Lokasi',
    'nav_classes' => 'Kelas',
    'nav_students' => 'Siswa',
    'nav_settings' => 'Setting',
    'nav_about' => 'Tentang Aplikasi',
    'nav_location_data' => 'Data per Lokasi',

    // Navigation groups
    'nav_group_transactions' => 'Transaksi',
    'nav_group_master' => 'Data Master',
    'nav_group_reports' => 'Laporan',
    'nav_group_system' => 'Sistem',

    // About page
    'about_version' => 'Versi :version',
    'about_body' => 'Aplikasi inventaris dan peminjaman alat lab Teknik Jaringan Komputer dan Telekomunikasi (TJKT). Kelola stok alat, pantau kondisi dan lokasi, serta catat peminjaman dan pengembalian secara cepat lewat scan QR.',

    // Loan abilities (owned by the Loans domain elsewhere; labels live here
    // because Acl.php belongs to this workstream)
    'ability_loan_view_any' => 'Lihat Peminjaman',
    'ability_loan_create' => 'Buat pinjaman',
    'ability_loan_update' => 'Ubah / kembalikan pinjaman',
    'ability_loan_delete' => 'Hapus riwayat pinjam',
];
