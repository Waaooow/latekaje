<?php

use Illuminate\Support\Facades\Route;
use App\Models\AssetItem;
use Illuminate\Http\Request;

Route::redirect('/', '/admin');

Route::get('/print-qr', function (Request $request) {
    $idsParam = $request->query('ids');
    
    if ($idsParam === 'all' || !$idsParam) {
        $items = AssetItem::with('asset')->get();
    } else {
        // 🟢 FIX: Ganti 'with' menjadi 'asset' biar relasi katalognya ketarik sempurna
        $ids = explode(',', $idsParam);
        $items = AssetItem::with('asset')->whereIn('id', $ids)->get();
    }
    
    if ($items->isEmpty()) {
        return "Gagal cetak: Tidak ada item yang ditemukan.";
    }

    return view('print-qrcode', compact('items'));
})->name('print.qr')->middleware(['auth']);