<?php

namespace App\Services;

use App\Models\AssetItem;

class AssetItemCode
{
    /**
     * Generate kode unik berikutnya: LTKJ-YYYY-#####.
     * Unik dijamin unique DB + retry di pemanggil (lihat AssetItemService).
     */
    public static function next(): string
    {
        $prefix = 'LTKJ-'.date('Y').'-';

        $last = AssetItem::query()
            ->where('nomor_seri_atau_qr', 'like', $prefix.'%')
            ->orderByDesc('nomor_seri_atau_qr')
            ->value('nomor_seri_atau_qr');

        $next = $last ? ((int) substr($last, -5)) + 1 : 1;

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
