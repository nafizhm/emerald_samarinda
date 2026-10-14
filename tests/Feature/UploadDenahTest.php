<?php

namespace Tests\Feature;

use App\Services\DenahSqlImporter;
use App\Services\DenahSqlParser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UploadDenahTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        Schema::create('lokasi_kavling', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama_kavling');
        });
        Schema::create('kavling_peta', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_lokasi');
            $table->text('map');
        });
        Schema::create('master_svg', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_lokasi');
            $table->text('header_svg');
        });
    }

    private function dump(): string
    {
        return "-- phpMyAdmin dump\nSET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
            CREATE TABLE `lokasi_kavling` (`id_lokasi` int);
            INSERT INTO `kavling_peta` (`id_kavling`, `id_lokasi`, `nama_cluster`, `map`) VALUES (1,1,'','a,b;(c)'), (2,1,'','second');
            INSERT INTO `lokasi_kavling` (`id_lokasi`, `nama_kavling`) VALUES (1,'O\\'Brien');
            INSERT INTO `master_svg` (`id`, `id_lokasi`, `header_svg`) VALUES (1,1,'<svg>\n<!-- text; -->\n</svg>');
            ALTER TABLE `lokasi_kavling` ADD PRIMARY KEY (`id_lokasi`); COMMIT;";
    }

    public function test_append_allocates_ids_and_remaps_location(): void
    {
        DB::table('lokasi_kavling')->insert(['id' => 7, 'nama_kavling' => 'Lama']);
        DB::table('kavling_peta')->insert(['id' => 15, 'id_lokasi' => 7, 'map' => 'lama']);
        (new DenahSqlImporter)->import($this->dump(), false);
        $this->assertSame("O'Brien", DB::table('lokasi_kavling')->where('id', 8)->value('nama_kavling'));
        $this->assertEquals(8, DB::table('kavling_peta')->where('id', 16)->value('id_lokasi'));
        $this->assertEquals(8, DB::table('master_svg')->value('id_lokasi'));
        $this->assertSame('a,b;(c)', DB::table('kavling_peta')->where('id', 16)->value('map'));
        $this->assertSame(3, DB::table('kavling_peta')->count());
    }

    public function test_reset_replaces_only_uploaded_tables(): void
    {
        DB::table('lokasi_kavling')->insert(['id' => 7, 'nama_kavling' => 'Lama']);
        DB::table('master_svg')->insert(['id' => 3, 'id_lokasi' => 7, 'header_svg' => 'keep']);
        (new DenahSqlImporter)->import("INSERT INTO lokasi_kavling (id_lokasi,nama_kavling) VALUES (1,'Baru');", true);
        $this->assertSame(1, DB::table('lokasi_kavling')->count());
        $this->assertSame('Baru', DB::table('lokasi_kavling')->where('id', 1)->value('nama_kavling'));
        $this->assertSame('keep', DB::table('master_svg')->value('header_svg'));
    }

    public function test_failed_reset_rolls_back_deleted_data(): void
    {
        DB::table('kavling_peta')->insert(['id' => 5, 'id_lokasi' => 1, 'map' => 'keep']);
        try {
            (new DenahSqlImporter)->import("INSERT INTO kavling_peta (id_kavling,id_lokasi,map) VALUES (1,999,'bad');", true);
            $this->fail('Invalid location should fail.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertSame('keep', DB::table('kavling_peta')->where('id', 5)->value('map'));
        }
    }

    public function test_unknown_columns_are_ignored_for_append_and_reset(): void
    {
        $sql = "INSERT INTO lokasi_kavling (id_lokasi,nama_kavling,status) VALUES (1,'Baru',9);
            INSERT INTO kavling_peta (id_kavling,id_lokasi,map,nama_cluster,status) VALUES (1,1,'denah','Cluster A',2);
            INSERT INTO master_svg (id,id_lokasi,header_svg,status) VALUES (1,1,'<svg></svg>',3);";

        foreach ([false, true] as $reset) {
            $counts = (new DenahSqlImporter)->import($sql, $reset);
            $this->assertSame(['lokasi_kavling' => 1, 'kavling_peta' => 1, 'master_svg' => 1], $counts);
            $this->assertSame('Baru', DB::table('lokasi_kavling')->value('nama_kavling'));
            $this->assertSame('denah', DB::table('kavling_peta')->value('map'));
            $this->assertSame('<svg></svg>', DB::table('master_svg')->value('header_svg'));
        }
    }

    public function test_executable_sql_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new DenahSqlParser)->parse("INSERT INTO lokasi_kavling (id_lokasi,nama_kavling) VALUES (1,'Test'); DROP TABLE users;");
    }

    public function test_desktop_hex_export_imports_and_remaps_location(): void
    {
        $sql = "SET NAMES utf8mb4;
            CREATE TABLE IF NOT EXISTS `lokasi_kavling` (`id_lokasi` int);
            CREATE TABLE IF NOT EXISTS `master_svg` (`id` int);
            CREATE TABLE IF NOT EXISTS `kavling_peta` (`id_kavling` int);
            START TRANSACTION;
            INSERT INTO lokasi_kavling (id_lokasi,nama_kavling) VALUES (X'3336',X'456d6572616c642053616d6172696e6461');
            INSERT INTO master_svg (id,id_lokasi,header_svg) VALUES (X'3233',X'3336',X'3c7376673e3c2f7376673e');
            INSERT INTO kavling_peta (id_kavling,id_lokasi,map) VALUES (X'323531',X'3336',X'');
            COMMIT;";
        DB::table('lokasi_kavling')->insert(['id'=>7,'nama_kavling'=>'Lama']);
        $counts = (new DenahSqlImporter)->import($sql, false);
        $this->assertSame(['lokasi_kavling'=>1,'kavling_peta'=>1,'master_svg'=>1], $counts);
        $this->assertSame('Emerald Samarinda', DB::table('lokasi_kavling')->where('id',8)->value('nama_kavling'));
        $this->assertEquals(8, DB::table('master_svg')->value('id_lokasi'));
        $this->assertSame('<svg></svg>', DB::table('master_svg')->value('header_svg'));
        $this->assertSame('', DB::table('kavling_peta')->value('map'));
        (new DenahSqlImporter)->import($sql, true);
        $this->assertSame('Emerald Samarinda', DB::table('lokasi_kavling')->where('id',36)->value('nama_kavling'));
        $this->assertEquals(36, DB::table('kavling_peta')->where('id',251)->value('id_lokasi'));
    }

    public function test_create_if_not_exists_does_not_bypass_table_validation(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new DenahSqlParser)->parse('CREATE TABLE IF NOT EXISTS users (id int);');
    }

    public function test_invalid_hex_literal_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new DenahSqlParser)->parse("INSERT INTO lokasi_kavling (nama_kavling) VALUES (X'123');");
    }

    public function test_missing_required_fields_receive_empty_values_and_database_defaults(): void
    {
        Schema::table('lokasi_kavling', function (Blueprint $table) {
            $table->string('foto_kavling');
            $table->integer('stt_tampil')->default(1);
        });
        Schema::table('kavling_peta', function (Blueprint $table) {
            $table->double('panjang');
            $table->integer('hrg_jual');
            $table->text('keterangan');
        });
        Schema::table('master_svg', function (Blueprint $table) {
            $table->text('body_svg');
            $table->integer('lebar');
        });
        foreach ([false, true] as $reset) {
            (new DenahSqlImporter)->import($this->dump(), $reset);
            $this->assertSame('', DB::table('lokasi_kavling')->value('foto_kavling'));
            $this->assertEquals(1, DB::table('lokasi_kavling')->value('stt_tampil'));
            $this->assertEquals(0, DB::table('kavling_peta')->value('panjang'));
            $this->assertEquals(0, DB::table('kavling_peta')->value('hrg_jual'));
            $this->assertSame('', DB::table('kavling_peta')->value('keterangan'));
            $this->assertSame('', DB::table('master_svg')->value('body_svg'));
            $this->assertEquals(0, DB::table('master_svg')->value('lebar'));
        }
    }

    public function test_upload_requires_login(): void
    {
        $this->get('/uploaddenah')->assertRedirect(route('login'));
        $this->post('/uploaddenah')->assertRedirect(route('login'));
    }

    public function test_imported_locations_are_visible_unless_sql_explicitly_hides_them(): void
    {
        Schema::table('lokasi_kavling', function (Blueprint $table) {
            $table->integer('stt_tampil')->default(0);
        });
        (new DenahSqlImporter)->import($this->dump(), false);
        $this->assertEquals(1, DB::table('lokasi_kavling')->where('id', 1)->value('stt_tampil'));
        (new DenahSqlImporter)->import("INSERT INTO lokasi_kavling (id_lokasi,nama_kavling,stt_tampil) VALUES (1,'Hidden',0);", false);
        $this->assertEquals(0, DB::table('lokasi_kavling')->where('id', 2)->value('stt_tampil'));
    }
}
