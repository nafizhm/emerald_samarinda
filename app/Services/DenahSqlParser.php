<?php

namespace App\Services;

use InvalidArgumentException;

class DenahSqlParser
{
    public const TABLES = ['lokasi_kavling', 'kavling_peta', 'master_svg'];

    public function parse(string $sql): array
    {
        $data = [];
        foreach ($this->split($sql, ';') as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            // Dump metadata is never executed against the application's schema.
            if (preg_match('/^(SET\s|START TRANSACTION\b|COMMIT\b|CREATE TABLE\s|ALTER TABLE\s)/i', $statement)) {
                if (preg_match('/^(?:CREATE|ALTER) TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $table)) {
                    $this->checkTable($table[1]);
                    $data[$table[1]] ??= [];
                }
                continue;
            }
            if (!preg_match('/^INSERT\s+INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*(.+)$/is', $statement, $match)) {
                throw new InvalidArgumentException('Format SQL tidak didukung. Gunakan dump dengan INSERT INTO ... VALUES.');
            }
            $this->checkTable($match[1]);
            $columns = array_map(fn ($column) => trim($column, " \r\n\t`"), explode(',', $match[2]));
            if (count($columns) !== count(array_unique($columns))) {
                throw new InvalidArgumentException('Kolom SQL duplikat.');
            }
            foreach ($columns as $column) {
                if (!preg_match('/^\w+$/', $column)) {
                    throw new InvalidArgumentException('Nama kolom tidak valid.');
                }
            }
            foreach ($this->split($match[3], ',') as $tuple) {
                $tuple = trim($tuple);
                if (!str_starts_with($tuple, '(') || !str_ends_with($tuple, ')')) {
                    throw new InvalidArgumentException('Baris INSERT tidak valid.');
                }
                $values = array_map([$this, 'value'], $this->split(substr($tuple, 1, -1), ','));
                if (count($values) !== count($columns)) {
                    throw new InvalidArgumentException('Jumlah nilai SQL tidak sesuai kolom.');
                }
                $data[$match[1]][] = array_combine($columns, $values);
            }
        }
        if (!$data || !array_filter($data)) {
            throw new InvalidArgumentException('File SQL tidak berisi data denah.');
        }
        return $data;
    }

    private function checkTable(string $table): void
    {
        if (!in_array($table, self::TABLES, true)) {
            throw new InvalidArgumentException("Tabel {$table} tidak termasuk tabel denah yang didukung.");
        }
    }

    private function value(string $value): mixed
    {
        $value = trim($value);
        if (strcasecmp($value, 'NULL') === 0) {
            return null;
        }
        if (preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return $value;
        }
        // Desktop exports encode text (including IDs and SVG) as MySQL hex literals.
        if (preg_match("/^X'([0-9a-f]*)'$/i", $value, $match)) {
            if (strlen($match[1]) % 2 !== 0) {
                throw new InvalidArgumentException('Nilai teks heksadesimal SQL tidak valid.');
            }
            return hex2bin($match[1]);
        }
        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            return preg_replace_callback('/\\\\(.)|\'\'/s', static function ($match) {
                if ($match[0] === "''") {
                    return "'";
                }
                return match ($match[1]) {
                    '0' => "\0", 'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1a",
                    default => $match[1],
                };
            }, substr($value, 1, -1));
        }
        throw new InvalidArgumentException('SQL hanya boleh berisi nilai teks, angka, atau NULL.');
    }

    private function split(string $input, string $separator): array
    {
        $parts = [];
        $part = '';
        $quote = null;
        $depth = 0;
        $length = strlen($input);
        for ($i = 0; $i < $length; $i++) {
            $char = $input[$i];
            $next = $input[$i + 1] ?? '';
            if ($quote !== null) {
                $part .= $char;
                if ($char === '\\' && $next !== '') {
                    $part .= $next;
                    $i++;
                } elseif ($char === $quote) {
                    if ($next === $quote) {
                        $part .= $next;
                        $i++;
                    } else {
                        $quote = null;
                    }
                }
                continue;
            }
            if (($char === '-' && $next === '-' && ctype_space($input[$i + 2] ?? ' ')) || $char === '#') {
                while ($i < $length && $input[$i] !== "\n") {
                    $i++;
                }
                $part .= "\n";
                continue;
            }
            if ($char === '/' && $next === '*') {
                $end = strpos($input, '*/', $i + 2);
                if ($end === false) {
                    throw new InvalidArgumentException('Komentar SQL tidak lengkap.');
                }
                $i = $end + 1;
                $part .= ' ';
                continue;
            }
            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            }
            if ($depth < 0) {
                throw new InvalidArgumentException('Kurung SQL tidak valid.');
            }
            if ($char === $separator && $depth === 0) {
                $parts[] = $part;
                $part = '';
            } else {
                $part .= $char;
            }
        }
        if ($quote !== null || $depth !== 0) {
            throw new InvalidArgumentException('SQL tidak lengkap.');
        }
        if (trim($part) !== '') {
            $parts[] = $part;
        }
        return $parts;
    }
}
