<?php

return [
    'model_label' => 'Data Unit',
    'model_plural' => 'Data Unit',

    // Form
    'asset_label' => 'Aset',
    'serial_label' => 'Nomor Seri / QR',
    'qty_created_label' => 'Jumlah Unit Dibuat',
    'qty_created_helper' => 'Isi > 1 untuk membuat banyak unit sekaligus. Kode sisanya digenerate otomatis.',
    'sn_manual_label' => 'SN Manual (opsional)',
    'sn_manual_placeholder' => "Satu SN per baris, misal:\nSN-PC-001\nSN-PC-002",
    'sn_manual_helper' => 'Kosongkan bila unit tidak punya SN — sistem generate kode otomatis.',
    'status_label' => 'Status',
    'status_available' => 'Tersedia',
    'status_borrowed' => 'Dipinjam',
    'condition_label' => 'Kondisi',
    'condition_good' => 'Baik',
    'condition_damaged' => 'Rusak',
    'condition_total_loss' => 'Rusak Total',
    'placement_label' => 'Lokasi Penempatan',

    // Table
    'group_asset' => 'Aset',
    'name_label' => 'Nama Alat',
    'code_label' => 'Kode Aset',
    'borrowed_by_label' => 'Dipinjam Oleh',
    'location_label' => 'Lokasi',

    // Header / record / bulk actions
    'download_excel' => 'Unduh Excel',
    'print_all_qr' => 'Cetak Semua QR',
    'print_qr' => 'Cetak QR',
    'print_selected_qr' => 'Cetak QR Terpilih',
    'move_room' => 'Pindah Ruangan',
    'target_location_label' => 'Lokasi Tujuan',
    'actions_label' => 'Aksi',

    // Pages & notifications
    'create_action' => 'Tambah Unit',
    'created_many_title' => ':count unit berhasil dibuat',
    'created_many_body' => 'Kode: :first s/d :last',

    // Importer columns
    'import_col_tool_name' => 'Nama Alat',
    'import_col_asset_code' => 'Kode Aset',
    'import_col_type' => 'Jenis',
    'import_col_spec' => 'Spesifikasi',
    'import_col_usage' => 'Kegunaan',
    'import_col_serial' => 'Nomor Seri / QR',
    'import_col_qty' => 'Jumlah',
    'import_col_condition' => 'Kondisi',
    'import_col_location' => 'Lokasi',
    'import_col_status' => 'Status',
    'import_completed' => 'Impor data unit selesai: :success baris berhasil',
    'import_failed_suffix' => ', :failed baris gagal',

    // Exporter
    'export_completed' => 'Ekspor data unit selesai: :success baris berhasil',
    'export_failed_suffix' => ', :failed baris gagal',

    // Validation (AssetItemService)
    'sn_manual_too_many' => 'Jumlah SN manual (:count) melebihi jumlah unit (:qty).',
    'sn_manual_duplicate' => "SN ':sn' sudah terdaftar di sistem.",

    // Abilities
    'ability_view_any' => 'Lihat Data Unit',
    'ability_create' => 'Tambah unit',
    'ability_update' => 'Ubah unit',
    'ability_delete' => 'Hapus unit',
];
