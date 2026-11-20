<?php

namespace App\Support;

class IndonesianText
{
    public static function lcfirstSmart(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return $s;
        }
        if (preg_match('/^[A-Z]{2,}(\/[A-Z]{2,})*\b/', $s)) {
            return $s;
        }

        return mb_strtolower(mb_substr($s, 0, 1)).mb_substr($s, 1);
    }

    public static function joinList(array $items): string
    {
        $items = array_values(array_filter(array_map('strval', $items), fn ($v) => $v !== ''));
        $count = count($items);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            return $items[0];
        }
        if ($count === 2) {
            return $items[0].' dan '.$items[1];
        }
        $last = array_pop($items);

        return implode(', ', $items).' dan '.$last;
    }

    public static function percent(float $value): string
    {
        $rounded = round($value, 1);
        if (abs($rounded - round($rounded)) < 1e-9) {
            return number_format($rounded, 0, ',', '').'%';
        }

        return number_format($rounded, 1, ',', '').'%';
    }
}
