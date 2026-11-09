<?php
// Preview by default; --persist applies the map after saving a local backup.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$location = DB::table('lokasi_kavling')->where('nama_kavling', 'Emerald Samarinda')->sole();
$master = DB::table('master_svg')->where('id_lokasi', $location->id)->sole();
$units = DB::table('kavling_peta')->where('id_lokasi', $location->id)->get();
$document = new DOMDocument();
$document->load(__DIR__.'/../public/svg/siteplan-emerald.svg', LIBXML_NONET);
$xpath = new DOMXPath($document);
$xpath->registerNamespace('s', 'http://www.w3.org/2000/svg');
$shapes = [];
foreach ($xpath->query('//s:g[count(s:polygon | s:path)=1 and count(s:g/s:text)=1]') as $group) {
    $label = trim($xpath->query('./s:g/s:text', $group)->item(0)->textContent);
    if (!preg_match('/^[A-Z]-\d+$/', $label)) continue;
    if (isset($shapes[$label])) throw new RuntimeException('Duplicate SVG label: '.$label);
    $geometry = $xpath->query('./s:polygon | ./s:path', $group)->item(0);
    $kind = $geometry->localName;
    $shapes[$label] = [
        'group' => $group,
        'jenis_map' => $kind,
        'map' => $geometry->getAttribute($kind === 'path' ? 'd' : 'points'),
        'matrik' => $xpath->query('./s:g', $group)->item(0)->getAttribute('transform'),
    ];
}
$missing = $units->pluck('kode_kavling')->diff(array_keys($shapes));
if ($missing->isNotEmpty()) throw new RuntimeException('Missing SVG units: '.$missing->implode(', '));
foreach ($shapes as $label => &$shape) {
    $group = $shape['group'];
    $group->parentNode->removeChild($group);
}
unset($shape);
$root = $document->documentElement;
$root->setAttribute('id', 'svg-image-'.$location->id);
$root->setAttribute('width', '[[lebar]]');
$root->setAttribute('height', '[[tinggi]]');
$svg = $document->saveXML($root);
// Dynamic units are inserted at root level; all other SVG artwork stays intact.
$header = substr($svg, 0, strrpos($svg, '</svg>'));
$template = '<g><polygon class="fil4" points="[[1]]" style="fill:[[2]]"/><g transform="[[3]]"><text x="20000" y="12000" class="fil5 fnt2">[[4]]</text></g></g>';
$pathTemplate = str_replace(['<polygon', 'points='], ['<path', 'd='], $template);
$rendered = str_replace(['[[lebar]]', '[[tinggi]]'], ['100%', '100%'], $header);
foreach ($shapes as $label => $shape) {
    $rendered .= str_replace(['[[1]]', '[[2]]', '[[3]]', '[[4]]'], [$shape['map'], '#ffffff', $shape['matrik'], $label], $shape['jenis_map'] === 'path' ? $pathTemplate : $template);
}
$validation = new DOMDocument();
if (!$validation->loadXML($rendered.'</svg>', LIBXML_NONET)) throw new RuntimeException('Invalid rendered SVG');
$persist = in_array('--persist', $argv, true);
if ($persist) {
    $backup = storage_path('app/siteplan-backups/'.date('Ymd-His').'-'.uniqid().'.json');
    if (!is_dir(dirname($backup))) mkdir(dirname($backup), 0755, true);
    file_put_contents($backup, json_encode(['master_svg' => $master, 'kavling_peta' => $units], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
    DB::transaction(function () use ($location, $units, $shapes, $master, $header, $template, $pathTemplate) {
        DB::table('master_svg')->where('id', $master->id)->update([
            'header_svg' => $header, 'polygon_svg' => $template, 'path_svg' => $pathTemplate, 'footer_svg' => '</svg>',
        ]);
        foreach ($units as $unit) {
            $shape = $shapes[$unit->kode_kavling];
            DB::table('kavling_peta')->where('id', $unit->id)->update([
                'jenis_map' => $shape['jenis_map'], 'map' => $shape['map'], 'matrik' => $shape['matrik'],
            ]);
        }
        $defaults = [];
        foreach (Schema::getColumns('kavling_peta') as $column) {
            if (!$column['nullable'] && $column['default'] === null && !$column['auto_increment']) {
                $defaults[$column['name']] = preg_match('/int|double|decimal|float/', $column['type_name']) ? 0 : '';
            }
        }
        foreach ($shapes as $label => $shape) {
            if ($units->contains('kode_kavling', $label)) continue;
            DB::table('kavling_peta')->insert(array_replace($defaults, [
                'id_lokasi' => $location->id, 'kode_kavling' => $label,
                'jenis_map' => $shape['jenis_map'], 'map' => $shape['map'], 'matrik' => $shape['matrik'],
                'keterangan' => 'Diimpor dari SVG; ukuran dan harga belum diisi.',
            ]));
        }
        $ready = DB::table('progres_list_penjualan')->where('status_progres', 'Ready')->first();
        if (!$ready) {
            $readyId = DB::table('progres_list_penjualan')->insertGetId([
                'status_progres' => 'Ready', 'urutan' => 1, 'keterangan' => 'Kavling tersedia',
                'warna' => '#ffffff', 'warna_bootstrap' => 'success', 'short_name' => 'Ready', 'stt_tampil' => 1,
            ]);
        } else $readyId = $ready->id;
        DB::table('customer')->where('id_lokasi', $location->id)->where(function ($q) {
            $q->whereNull('id_status_progres')->orWhere('id_status_progres', 0);
        })->update(['id_status_progres' => $readyId]);
    });
    copy(__DIR__.'/../public/svg/siteplan-emerald.svg', __DIR__.'/../public/svg/siteplan_'.$location->id.'.svg');
}
echo json_encode(['persisted' => $persist, 'existing_units' => $units->count(), 'svg_units' => count($shapes), 'new_units' => count($shapes) - $units->count(), 'backup' => $backup ?? null], JSON_PRETTY_PRINT).PHP_EOL;
