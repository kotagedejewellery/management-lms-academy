<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 26px; }
        body { border: 3px solid #b18b42; color: #171717; font-family: DejaVu Sans, sans-serif; margin: 0; padding: 44px; text-align: center; }
        .brand { color: #9c7632; font-size: 14px; font-weight: bold; letter-spacing: 3px; }
        h1 { font-family: DejaVu Serif, serif; font-size: 36px; font-weight: normal; margin: 38px 0 12px; }
        .lead { color: #525252; font-size: 14px; margin: 0; }
        .name { font-family: DejaVu Serif, serif; font-size: 31px; margin: 30px 0 12px; }
        .course { color: #9c7632; font-size: 20px; font-weight: bold; margin: 8px 0 34px; }
        .meta { border-top: 1px solid #d4bd8c; color: #525252; font-size: 11px; margin: 0 auto; padding-top: 16px; width: 80%; }
    </style>
</head>
<body>
    <div class="brand">KOTAGEDE JEWELLERY · KGJ ACADEMY</div>
    <h1>Sertifikat Penyelesaian</h1>
    <p class="lead">Dengan ini diberikan kepada</p>
    <div class="name">{{ $certificate->user->name }}</div>
    <p class="lead">atas penyelesaian course</p>
    <div class="course">{{ $certificate->course->title }}</div>
    <div class="meta">
        Nomor sertifikat: {{ $certificate->number }} &nbsp;·&nbsp;
        Diterbitkan: {{ $certificate->issued_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}
    </div>
</body>
</html>
