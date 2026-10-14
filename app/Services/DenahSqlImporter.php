<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class DenahSqlImporter
{
    public function import(string $sql, bool $reset): array
    {
        $data = (new DenahSqlParser)->parse($sql);
        foreach ($data as $table => &$rows) {
            $definitions = Schema::getColumns($table);
            $columns = array_column($definitions, 'name');
            $missingDefaults = [];
            foreach ($definitions as $definition) {
                if ($definition['name'] === 'id' || $definition['auto_increment'] || $definition['nullable']
                    || $definition['default'] !== null || !empty($definition['generation'])) {
                    continue;
                }
                $type = strtolower($definition['type_name']);
                if (preg_match('/int|decimal|numeric|float|double|real|bool|bit/', $type)) {
                    $missingDefaults[$definition['name']] = 0;
                } elseif (preg_match('/char|text|blob|binary/', $type)) {
                    $missingDefaults[$definition['name']] = '';
                }
            }
            if (!in_array('id', $columns, true)) {
                throw new InvalidArgumentException("Tabel {$table} tidak sesuai struktur aplikasi (kolom id tidak ditemukan).");
            }
            foreach ($rows as &$row) {
                $alias = match ($table) {
                    'lokasi_kavling' => 'id_lokasi',
                    'kavling_peta' => 'id_kavling',
                    default => 'id',
                };
                if ($alias !== 'id' && array_key_exists($alias, $row)) {
                    $row['id'] = $row[$alias];
                    unset($row[$alias]);
                }
                // Import only columns present in the destination table.
                $row = array_intersect_key($row, array_flip($columns));
                // Legacy siteplan dumps omit visibility; imported locations should appear in ST Penjualan.
                if ($table === 'lokasi_kavling' && in_array('stt_tampil', $columns, true)
                    && !array_key_exists('stt_tampil', $row)) {
                    $row['stt_tampil'] = 1;
                }
                // Older denah dumps omit required application fields, such as foto_kavling.
                $row += $missingDefaults;
            }
            unset($row);
        }
        unset($rows);

        return DB::transaction(function () use ($data, $reset) {
            // Serialize imports so two uploads cannot allocate the same next IDs.
            foreach (DenahSqlParser::TABLES as $table) {
                if (array_key_exists($table, $data)) {
                    DB::table($table)->orderBy('id')->lockForUpdate()->get(['id']);
                }
            }
            if ($reset) {
                foreach (array_reverse(DenahSqlParser::TABLES) as $table) {
                    if (array_key_exists($table, $data)) {
                        DB::table($table)->delete();
                    }
                }
            }
            $locations = [];
            $counts = [];
            foreach (DenahSqlParser::TABLES as $table) {
                if (!array_key_exists($table, $data)) {
                    continue;
                }
                $nextId = (int) DB::table($table)->max('id');
                $seen = [];
                foreach ($data[$table] as $row) {
                    $oldId = $row['id'] ?? null;
                    if ($oldId !== null && (!ctype_digit((string) $oldId) || isset($seen[$oldId]))) {
                        throw new InvalidArgumentException("ID tidak valid atau duplikat pada {$table}.");
                    }
                    $seen[$oldId] = true;
                    $row['id'] = $reset && $oldId !== null ? (int) $oldId : ++$nextId;
                    $nextId = max($nextId, $row['id']);
                    if ($table === 'lokasi_kavling' && $oldId !== null) {
                        $locations[$oldId] = $row['id'];
                    }
                    if ($table !== 'lokasi_kavling' && isset($row['id_lokasi'])) {
                        $location = $row['id_lokasi'];
                        if (array_key_exists('lokasi_kavling', $data) && !isset($locations[$location])) {
                            throw new InvalidArgumentException("Lokasi {$location} tidak ditemukan dalam SQL upload.");
                        }
                        $row['id_lokasi'] = $locations[$location] ?? $location;
                        if (!DB::table('lokasi_kavling')->where('id', $row['id_lokasi'])->exists()) {
                            throw new InvalidArgumentException("Lokasi {$location} tidak ditemukan.");
                        }
                    }
                    DB::table($table)->insert($row);
                }
                $counts[$table] = count($data[$table]);
            }
            return $counts;
        });
    }
}
