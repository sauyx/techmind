<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Hasil Analisis Karier TechMind</title>
    <style>
        @page { margin: 24px 30px; }
        body { font-family: sans-serif; font-size: 11px; color: #18181b; line-height: 1.4; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #4F46E5; padding-bottom: 8px; margin-bottom: 16px; }
        .title { font-size: 18px; font-weight: bold; color: #09090b; }
        .meta { font-size: 9px; color: #71717a; margin-top: 4px; }
        .card { border: 1px solid #e4e4e7; border-radius: 6px; padding: 12px; margin-bottom: 14px; background: #ffffff; }
        .badge { display: inline-block; background: #EEF2FF; color: #4F46E5; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; }
        .h2 { font-size: 14px; font-weight: bold; color: #09090b; margin: 0 0 6px 0; }
        .p { margin: 4px 0; color: #3f3f46; }
        .table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .table th, .table td { border-bottom: 1px solid #e4e4e7; padding: 6px 8px; text-align: left; font-size: 10px; }
        .table th { background: #f4f4f5; color: #52525b; font-weight: bold; }
        .chart-box { text-align: center; margin: 10px 0; }
        .chart-img { width: 340px; height: 280px; }
        .pill { display: inline-block; border: 1px solid #d4d4d8; padding: 2px 6px; border-radius: 4px; font-size: 9px; color: #27272a; margin: 2px; }
        .footer { font-size: 8px; color: #a1a1aa; border-top: 1px solid #e4e4e7; padding-top: 8px; margin-top: 20px; text-align: center; }
        .grid-3 { width: 100%; margin-top: 8px; }
        .grid-3 td { vertical-align: top; width: 33%; padding: 4px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">TechMind — Laporan Analisis Karier</div>
        <div class="meta">
            Peserta: <strong>{{ $session['visitor_name'] ?: 'Peserta Anonim' }}</strong> |
            Tanggal: {{ $completed_date }} |
            Sesi ID: <code>{{ substr($session['token'], 0, 8) }}</code>
        </div>
    </div>

    @if($topResult)
    <div class="card" style="border-left: 4px solid #4F46E5;">
        <span class="badge">Rekomendasi Utama (Rank #1)</span>
        <div class="h2" style="margin-top: 6px;">{{ $topResult['career_name'] }} — {{ $topResult['match_percentage'] }}% Kecocokan</div>
        @if($topCareer && $topCareer['summary'])
            <div class="p">{{ $topCareer['summary'] }}</div>
        @endif
    </div>
    @endif

    <div class="card">
        <div class="h2">Ringkasan Analisis Personal</div>
        <div class="p" style="white-space: pre-line;">{{ $ai_explanation['content'] }}</div>
    </div>

    <div class="card chart-box">
        <div class="h2" style="text-align: left;">Grafik Perbandingan Profil</div>
        <img class="chart-img" src="{{ $radar_svg }}" alt="Grafik Radar">
    </div>

    <div class="card">
        <div class="h2">Peringkat 10 Karier Teratas</div>
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 40px;">Rank</th>
                    <th>Jalur Karier</th>
                    <th style="width: 90px; text-align: right;">Kecocokan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $item)
                <tr>
                    <td>#{{ $item['rank'] }}</td>
                    <td><strong>{{ $item['career_name'] }}</strong></td>
                    <td style="text-align: right;">{{ $item['match_percentage'] }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($topCareer)
    <div class="card">
        <div class="h2">Detail Karier Teratas ({{ $topCareer['name'] }})</div>
        <table class="grid-3">
            <tr>
                <td>
                    <strong>Skill Utama:</strong><br>
                    @foreach($topCareer['skills'] ?? [] as $skill)
                        <span class="pill">{{ $skill }}</span>
                    @endforeach
                </td>
                <td>
                    <strong>Tools:</strong><br>
                    @foreach($topCareer['tools'] ?? [] as $tool)
                        <span class="pill">{{ $tool }}</span>
                    @endforeach
                </td>
                <td>
                    <strong>Roadmap Belajar:</strong>
                    <ol style="margin: 4px 0; padding-left: 16px; font-size: 9px; color: #3f3f46;">
                        @foreach($topCareer['roadmap'] ?? [] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <div class="footer">
        Skor merupakan indeks kecocokan relatif berbasis metode SAW (Simple Additive Weighting). Data matriks bersifat rekomendasi awal dan belum tervalidasi sebagai probabilitas keberhasilan mutlak.
    </div>
</body>
</html>
