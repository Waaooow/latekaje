<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak QR Code LATEKAJE</title>
    <style>
        /* CSS Khusus Cetak Stiker */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
            background-color: #fff;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .sticker-box {
            border: 1px dashed #ccc;
            padding: 10px;
            text-align: center;
            background: #fff;
            page-break-inside: avoid;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }
        .qr-code {
            margin: 5px 0;
        }
        .code-text {
            font-size: 9px;
            font-family: monospace;
            background: #eee;
            padding: 2px 4px;
            border-radius: 3px;
            margin-top: 3px;
        }
        /* Trigger otomatis buka dialog print saat halaman dimuat */
        @media print {
            body { padding: 0; }
            .sticker-box { border: 1px solid #000; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="grid-container">
        @foreach($items as $item)
            <div class="sticker-box">
                <div class="title">LATEKAJE ASSET</div>
                
                <div class="qr-code">
                    <!-- Generate QR Code format SVG biar super tajam pas di-print -->
                    {!! QrCode::size(90)->margin(1)->generate($item->nomor_seri_atau_qr) !!}
                </div>
                
                <div class="title" style="font-size: 9px; max-width: 120px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                    {{ $item->asset?->nama_alat ?? 'Alat' }}
                </div>
                <div class="code-text">{{ $item->nomor_seri_atau_qr }}</div>
            </div>
        @endforeach
    </div>

</body>
</html>