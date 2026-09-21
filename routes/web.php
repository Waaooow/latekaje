<?php

use App\Models\AssetItem;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

Route::get('/print-qr', function () {
    $ids = request()->query('ids', 'all');

    $query = AssetItem::query()->with('asset')->orderBy('nomor_seri_atau_qr');

    if ($ids !== 'all') {
        $idList = collect(explode(',', (string) $ids))
            ->map(fn ($v) => trim($v))
            ->filter()
            ->values()
            ->all();

        $query->whereIn('id', $idList);
    }

    return view('print-qrcode', [
        'items' => $query->get(),
    ]);
})->middleware('auth')->name('print.qr');

Route::get("/login", fn () => redirect("/admin/login"))->name("login");
