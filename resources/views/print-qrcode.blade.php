<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR — LATEKAJE</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; margin: 16px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .sticker { border: 1px dashed #999; border-radius: 8px; padding: 10px; text-align: center; page-break-inside: avoid; }
        .sticker .name { font-weight: bold; font-size: 13px; margin-top: 6px; }
        .sticker .code { font-size: 12px; color: #444; }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 12px;">
        <button onclick="window.print()">Cetak</button>
    </div>

    <div class="grid">
        @forelse ($items as $item)
            <div class="sticker">
                {!! QrCode::size(90)->generate($item->nomor_seri_atau_qr) !!}
                <div class="name">{{ $item->asset?->nama_alat ?? 'Alat' }}</div>
                <div class="code">{{ $item->nomor_seri_atau_qr }}</div>
            </div>
        @empty
            <p>Tidak ada unit untuk dicetak.</p>
        @endforelse
    </div>

    <script>
        // window.print();
    </script>
</body>
</html>
