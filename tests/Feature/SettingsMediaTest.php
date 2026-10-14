<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SettingsMediaTest extends TestCase
{
    private string $testPublic;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'session.driver' => 'array']);
        DB::purge('sqlite');
        $this->testPublic = sys_get_temp_dir().'/settings-media-'.bin2hex(random_bytes(8));
        mkdir($this->testPublic);
        mkdir($this->testPublic.'/config_media');
        $this->app->usePublicPath($this->testPublic);
        Schema::create('menu', function (Blueprint $table) {
            $table->increments('id'); $table->string('route_name');
        });
        Schema::create('hak_akses', function (Blueprint $table) {
            $table->increments('id'); $table->integer('id_user'); $table->integer('id_menu');
            $table->integer('edit'); $table->integer('hapus');
        });
        Schema::create('konfigurasi_media', function (Blueprint $table) {
            $table->increments('id'); $table->string('jenis_data'); $table->string('nama_file');
        });
        DB::table('menu')->insert(['id' => 19, 'route_name' => 'pengaturan-profile.index']);
        DB::table('hak_akses')->insert(['id_user' => 1, 'id_menu' => 19, 'edit' => 1, 'hapus' => 0]);
        DB::table('konfigurasi_media')->insert(['id' => 1, 'jenis_data' => 'logo website', 'nama_file' => 'old.png']);
        file_put_contents($this->testPublic.'/config_media/old.png', 'old image');
        $user = new User; $user->id = 1; $this->actingAs($user);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->testPublic.'/config_media/*') as $file) {
            unlink($file);
        }
        rmdir($this->testPublic.'/config_media');
        rmdir($this->testPublic);
        parent::tearDown();
    }

    public function test_old_media_page_redirects_to_combined_page(): void
    {
        $this->get(route('pengaturan-media.index'))->assertRedirect(route('pengaturan-profile.index', ['tab' => 'media']));
    }

    public function test_replacement_removes_old_file_after_new_image_is_saved(): void
    {
        $file = UploadedFile::fake()->image('new.png');
        $this->post(route('pengaturan-media.update', 1), ['_method' => 'PUT', 'nama_file' => $file])
            ->assertOk()->assertJson(['success' => true]);
        $name = DB::table('konfigurasi_media')->value('nama_file');
        $this->assertFileExists($this->testPublic.'/config_media/'.$name);
        $this->assertFileDoesNotExist($this->testPublic.'/config_media/old.png');
        fclose($file->tempFile);
    }

    public function test_invalid_upload_preserves_old_file(): void
    {
        $this->postJson(route('pengaturan-media.update', 1), ['_method' => 'PUT', 'nama_file' => UploadedFile::fake()->create('bad.txt', 1)])
            ->assertUnprocessable();
        $this->assertSame('old.png', DB::table('konfigurasi_media')->value('nama_file'));
        $this->assertFileExists($this->testPublic.'/config_media/old.png');
    }

    public function test_delete_clears_file_but_preserves_slot_for_reupload(): void
    {
        $this->deleteJson(route('pengaturan-media.destroy', 1))->assertOk();
        $this->assertSame('', DB::table('konfigurasi_media')->value('nama_file'));
        $this->assertSame(1, DB::table('konfigurasi_media')->count());
        $this->assertFileDoesNotExist($this->testPublic.'/config_media/old.png');
    }

    public function test_read_only_user_cannot_replace_or_remove_media(): void
    {
        DB::table('hak_akses')->update(['edit' => 0]);
        $this->deleteJson(route('pengaturan-media.destroy', 1))->assertForbidden();
        $this->putJson(route('pengaturan-media.update', 1))->assertForbidden();
        $this->assertFileExists($this->testPublic.'/config_media/old.png');
    }
}
