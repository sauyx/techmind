<?php

namespace App\Services\Pdf;

final class RadarSvgGenerator
{
    private const SHORT_LABELS = [
        'logic_problem_solving' => 'Logika',
        'data_analysis' => 'Data',
        'math_statistics' => 'Matematika',
        'attention_detail' => 'Ketelitian',
        'visual_design' => 'Desain',
        'creativity' => 'Kreativitas',
        'user_empathy' => 'Empati',
        'communication' => 'Komunikasi',
        'coding' => 'Coding',
        'network_infra' => 'Jaringan',
        'security_awareness' => 'Keamanan',
        'business_process' => 'Bisnis',
    ];

    public function generate(array $ratings, array $profiles, array $criteria): string
    {
        $cx = 180;
        $cy = 135;
        $radius = 80;
        $total = count($criteria);

        if ($total === 0) {
            return '';
        }

        $gridLines = '';
        for ($level = 1; $level <= 5; $level++) {
            $r = ($radius / 5) * $level;
            $points = [];
            for ($i = 0; $i < $total; $i++) {
                $angle = deg2rad(-90 + ($i * 360 / $total));
                $x = $cx + $r * cos($angle);
                $y = $cy + $r * sin($angle);
                $points[] = sprintf('%.1f,%.1f', $x, $y);
            }
            $gridLines .= sprintf(
                '<polygon points="%s" fill="none" stroke="#E4E4E7" stroke-width="0.8"/>',
                implode(' ', $points)
            );
        }

        $axisLines = '';
        $labels = '';
        $userPoints = [];
        $idealPoints = [];

        for ($i = 0; $i < $total; $i++) {
            $criterion = $criteria[$i];
            $code = (string) $criterion['code'];
            $angle = deg2rad(-90 + ($i * 360 / $total));

            $ax = $cx + $radius * cos($angle);
            $ay = $cy + $radius * sin($angle);
            $axisLines .= sprintf(
                '<line x1="%d" y1="%d" x2="%.1f" y2="%.1f" stroke="#E4E4E7" stroke-width="0.8"/>',
                $cx,
                $cy,
                $ax,
                $ay
            );

            $userVal = max(0, min(5, (int) ($ratings[$code] ?? 0)));
            $ur = ($radius / 5) * $userVal;
            $ux = $cx + $ur * cos($angle);
            $uy = $cy + $ur * sin($angle);
            $userPoints[] = sprintf('%.1f,%.1f', $ux, $uy);

            $idealVal = max(0, min(5, (int) ($profiles[$code] ?? 0)));
            $ir = ($radius / 5) * $idealVal;
            $ix = $cx + $ir * cos($angle);
            $iy = $cy + $ir * sin($angle);
            $idealPoints[] = sprintf('%.1f,%.1f', $ix, $iy);

            $lx = $cx + ($radius + 18) * cos($angle);
            $ly = $cy + ($radius + 18) * sin($angle);
            $labelText = self::SHORT_LABELS[$code] ?? $criterion['name'];

            $anchor = 'middle';
            if (cos($angle) > 0.3) {
                $anchor = 'start';
            } elseif (cos($angle) < -0.3) {
                $anchor = 'end';
            }

            $labels .= sprintf(
                '<text x="%.1f" y="%.1f" font-family="sans-serif" font-size="7.5" fill="#3F3F46" text-anchor="%s" dominant-baseline="central">%s</text>',
                $lx,
                $ly,
                $anchor,
                htmlspecialchars($labelText, ENT_QUOTES, 'UTF-8')
            );
        }

        $userPolygon = sprintf(
            '<polygon points="%s" fill="#4F46E5" fill-opacity="0.18" stroke="#4F46E5" stroke-width="1.8"/>',
            implode(' ', $userPoints)
        );

        $idealPolygon = sprintf(
            '<polygon points="%s" fill="#64748B" fill-opacity="0.12" stroke="#64748B" stroke-width="1.5" stroke-dasharray="3,3"/>',
            implode(' ', $idealPoints)
        );

        $legend = '
            <rect x="70" y="275" width="10" height="10" fill="#4F46E5" rx="2"/>
            <text x="86" y="283" font-family="sans-serif" font-size="9" fill="#27272A">Profil Kamu</text>
            <rect x="190" y="275" width="10" height="10" fill="#64748B" rx="2"/>
            <text x="206" y="283" font-family="sans-serif" font-size="9" fill="#27272A">Profil Ideal Karier #1</text>
        ';

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 360 300" width="360" height="300" style="background:#ffffff;">%s%s%s%s%s%s</svg>',
            $gridLines,
            $axisLines,
            $idealPolygon,
            $userPolygon,
            $labels,
            $legend
        );

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
