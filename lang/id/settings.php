<?php

return [
    // Page labels
    'nav_label' => 'Setting',
    'title' => 'Notifikasi',

    // Notifications
    'saved' => 'Pengaturan disimpan',
    'gowa_connected' => 'Terhubung! (HTTP :status)',
    'gowa_failed' => 'Gagal: :body',
    'gowa_ok_title' => 'GOWA terhubung',
    'gowa_fail_title' => 'GOWA gagal',
    'fill_test_number' => 'Isi Nomor Tes dulu.',
    'test_wa_body' => 'Tes LATEKAJE OK — :time. Balas pesan ini bila diterima.',
    'test_sent_to' => 'Pesan tes terkirim ke :target! (HTTP :status)',
    'test_send_fail' => 'Gagal kirim ke :target: :body',
    'test_sent_title' => 'Pesan tes terkirim',
    'test_failed_title' => 'Pesan tes gagal',
    'fill_webhook' => 'Isi webhook URL dulu.',
    'webhook_test_message' => 'Tes koneksi webhook LATEKAJE',
    'webhook_sent' => 'Terkirim! (HTTP :status)',
    'webhook_failed' => 'Gagal (HTTP :status): :body',
    'maintenance_off' => 'Mode perawatan MATI — aplikasi live',
    'maintenance_on' => 'Mode perawatan NYALA — hanya superadmin bisa buka',
    'token_name_required' => 'Isi nama token dulu',
    'token_created' => 'Token dibuat — salin sekarang, hanya tampil sekali',
    'token_revoked' => 'Token dicabut',

    // Blade: Aplikasi section
    'app_heading' => 'Aplikasi',
    'app_desc' => 'Status: :status.',
    'status_maintenance' => 'MODE PERAWATAN (tutup untuk umum)',
    'status_live' => 'Live normal',
    'maintenance_enable' => 'Nyalakan Mode Perawatan',
    'maintenance_disable' => 'Matikan Mode Perawatan',

    // Blade: API section
    'api_heading' => 'API (untuk integrasi luar)',
    'api_desc' => 'Token milik akunmu. Sertakan sebagai header Authorization: Bearer <token>.',
    'new_token_name' => 'Nama token baru',
    'new_token_placeholder' => 'cth: hp-kiosk-1',
    'create_token' => 'Buat Token',
    'th_name' => 'Nama',
    'th_created' => 'Dibuat',
    'th_last_used' => 'Terakhir dipakai',
    'revoke' => 'Cabut',
    'empty_tokens' => 'Belum ada token.',

    // Blade: Jadwal section
    'schedule_heading' => 'Jadwal',
    'auto_daily' => 'Kirim otomatis tiap hari',
    'send_time' => 'Jam kirim (WIB)',

    // Blade: GOWA section
    'gowa_heading' => 'WhatsApp via GOWA',
    'gowa_desc' => 'Prioritas utama. Isi base URL GOWA + target nomor/grup, lalu Tes Koneksi.',
    'gowa_enable' => 'Aktifkan kirim via GOWA',
    'gowa_base' => 'Base URL GOWA',
    'optional' => '(opsional)',
    'basic_user' => 'Basic Auth User',
    'basic_pass' => 'Basic Auth Password',
    'target_label' => 'Target (nomor 628.. / JID grup ....@g.us)',
    'relay_url' => 'Relay URL',
    'relay_url_hint' => '(opsional — bila GOWA tidak terjangkau langsung dari server)',
    'relay_secret' => 'Relay Secret',
    'test_conn' => 'Tes Koneksi GOWA',
    'test_target_placeholder' => 'Nomor tes (kosongkan = pakai Target)',
    'send_test' => 'Kirim Pesan Tes',

    // Blade: Webhook section
    'webhook_heading' => 'Webhook Umum',
    'webhook_desc' => 'POST JSON {event, generated_at, total, items} ke URL apa pun (n8n, bot sendiri, dll).',
    'webhook_enable' => 'Aktifkan webhook',
    'webhook_url' => 'Webhook URL',
    'secret_how' => 'Cara kirim secret',
    'opt_header' => 'Header X-Webhook-Secret',
    'opt_bearer' => 'Bearer token',
    'opt_basic' => 'Basic auth (user + password)',
    'opt_query' => 'Query param ?secret=',
    'opt_none' => 'Tanpa secret',
    'secret_token' => 'Secret / Token',
    'basic_user_cond' => 'Basic User',
    'basic_cond_hint' => '(bila mode basic)',
    'basic_pass_plain' => 'Basic Password',
    'test_webhook' => 'Tes Webhook',

    // Blade: save + history
    'save_all' => 'Simpan Semua Pengaturan',
    'history_heading' => 'Riwayat Pengiriman (10 terakhir)',
    'th_time' => 'Waktu',
    'th_channel' => 'Kanal',
    'th_target' => 'Target',
    'th_units' => 'Unit',
    'th_status' => 'Status',
    'th_response' => 'Respon',
    'empty_logs' => 'Belum ada pengiriman.',
];
