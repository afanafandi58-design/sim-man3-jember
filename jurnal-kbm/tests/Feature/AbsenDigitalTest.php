<?php

namespace Tests\Feature;

use App\Models\AbsenKegiatan;
use App\Models\AbsenKehadiran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AbsenDigitalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_menu_memisahkan_aksi_admin(): void
    {
        $menu = collect(config('adminlte.menu'))->firstWhere('text', 'Absen Digital');

        $this->assertNotNull($menu);
        $submenu = collect($menu['submenu']);
        $this->assertSame(
            ['Kegiatan', 'Scan Barcode', 'Absen Saya', 'Ringkasan', 'Cetak'],
            $submenu->pluck('text')->all()
        );
        $this->assertSame('role-absen-admin', $submenu->firstWhere('text', 'Ringkasan')['can']);
        $this->assertSame('role-absen-admin', $submenu->firstWhere('text', 'Cetak')['can']);
        $this->assertSame('role-absen-digital', $submenu->firstWhere('text', 'Absen Saya')['can']);
    }

    public function test_guru_tidak_bisa_membuka_halaman_admin(): void
    {
        $guru = $this->user('guru');
        $this->actingAs($guru);

        $this->assertTrue(Gate::allows('role-absen-digital'));
        $this->assertFalse(Gate::allows('role-absen-admin'));

        $this->get(route('absen-digital.ringkasan'))->assertForbidden();
        $this->get(route('absen-digital.cetak'))->assertForbidden();
        $this->get(route('absen-digital.kegiatan.create'))->assertForbidden();
        $this->post(route('absen-digital.kegiatan.store'), [
            'nama' => 'Rapat gelap',
            'jenis' => 'rapat',
            'tanggal' => now()->toDateString(),
        ])->assertForbidden();

        $this->get(route('absen-digital.kegiatan.index'))
            ->assertOk()
            ->assertDontSee('Tambah kegiatan');
    }

    public function test_alur_formulir_scan_ringkasan_dan_cetak(): void
    {
        $admin = $this->user('admin');
        $guru = $this->user('guru', 'Siti Guru');
        $staff = $this->user('staff', 'Budi Staff');

        $this->actingAs($admin)->post(route('absen-digital.kegiatan.store'), [
            'nama' => 'Rapat Kurikulum',
            'jenis' => 'rapat',
            'tanggal' => now()->toDateString(),
            'waktu' => '09:00',
            'tempat' => 'Aula',
        ])->assertRedirect();

        $kegiatan = AbsenKegiatan::query()->firstOrFail();
        $this->assertNotSame('', $kegiatan->kode);

        $this->actingAs($admin)
            ->get(route('absen-digital.kegiatan.show', $kegiatan))
            ->assertOk()
            ->assertSee($kegiatan->kode)
            ->assertSee('Hasil rapat')
            ->assertSee('Foto dokumentasi');

        $this->actingAs($guru)
            ->get(route('absen-digital.kegiatan.show', $kegiatan))
            ->assertOk()
            ->assertDontSee('Hasil rapat')
            ->assertDontSee('Tambah kegiatan');

        $this->actingAs($guru)->post(route('absen-digital.form.store'), [
            'kegiatan_id' => $kegiatan->id,
            'mode_peserta' => 'user',
            'user_id' => $guru->id,
            'foto' => UploadedFile::fake()->image('selfie.jpg'),
            'latitude' => -8.171234,
            'longitude' => 113.702211,
            'ttd' => $this->signatureDataUrl(),
        ])->assertRedirect(route('absen-digital.form'));

        $hadir = AbsenKehadiran::query()->where('user_id', $guru->id)->firstOrFail();
        $this->assertSame('form', $hadir->metode);
        $this->assertSame('Siti Guru', $hadir->nama);
        $this->assertEqualsWithDelta(-8.171234, (float) $hadir->latitude, 0.0000001);
        $this->assertEqualsWithDelta(113.702211, (float) $hadir->longitude, 0.0000001);
        Storage::disk('local')->assertExists($hadir->foto_path);
        Storage::disk('local')->assertExists($hadir->ttd_path);

        $this->actingAs($guru)
            ->get(route('absen-digital.form'))
            ->assertOk()
            ->assertSee('Siti Guru')
            ->assertSee('-8.171234');

        $this->actingAs($guru)->post(route('absen-digital.form.store'), [
            'kegiatan_id' => $kegiatan->id,
            'mode_peserta' => 'user',
            'user_id' => $guru->id,
            'foto' => UploadedFile::fake()->image('selfie.jpg'),
            'latitude' => -8.1,
            'longitude' => 113.7,
            'ttd' => $this->signatureDataUrl(),
        ])->assertSessionHasErrors('kegiatan_id');

        $this->actingAs($staff)->post(route('absen-digital.form.store'), [
            'kegiatan_id' => $kegiatan->id,
            'mode_peserta' => 'nik',
            'nik' => '3509123456789012',
            'nama' => 'Tamu Undangan',
            'foto' => UploadedFile::fake()->image('tamu.jpg'),
            'latitude' => -8.18,
            'longitude' => 113.71,
            'ttd' => $this->signatureDataUrl(),
        ])->assertRedirect(route('absen-digital.form'));

        $this->assertDatabaseHas('absen_kehadirans', [
            'kegiatan_id' => $kegiatan->id,
            'nik' => '3509123456789012',
            'nama' => 'Tamu Undangan',
            'user_id' => null,
        ]);

        $this->actingAs($staff)->post(route('absen-digital.scan.store'), [
            'kode' => url('/absen-digital/scan?kode='.strtolower($kegiatan->kode)),
            'user_id' => $staff->id,
        ])->assertRedirect(route('absen-digital.scan', ['kode' => $kegiatan->kode]));

        $this->assertDatabaseHas('absen_kehadirans', [
            'kegiatan_id' => $kegiatan->id,
            'user_id' => $staff->id,
            'metode' => 'qr',
        ]);

        $this->actingAs($admin)
            ->get(route('absen-digital.ringkasan', ['kegiatan_id' => $kegiatan->id]))
            ->assertOk()
            ->assertSee('Rapat Kurikulum')
            ->assertSee('>3<', false);

        $this->actingAs($admin)->post(route('absen-digital.kegiatan.hasil.store', $kegiatan), [
            'hasil_drive_url' => 'https://drive.google.com/file/d/abc/view',
            'hasil_file' => UploadedFile::fake()->image('notulen.jpg'),
            'hasil_catatan' => 'Notulen singkat',
        ])->assertRedirect();

        $kegiatan->refresh();
        $this->assertSame('https://drive.google.com/file/d/abc/view', $kegiatan->hasil_drive_url);
        $this->assertNotNull($kegiatan->hasil_file_path);
        Storage::disk('local')->assertExists($kegiatan->hasil_file_path);

        $this->actingAs($admin)->post(route('absen-digital.kegiatan.foto.store', $kegiatan), [
            'foto' => UploadedFile::fake()->image('aula.jpg'),
            'keterangan' => 'Suasana aula',
        ])->assertRedirect();
        $this->assertSame(1, $kegiatan->fotos()->count());

        $this->actingAs($guru)
            ->get(route('absen-digital.berkas', ['jenis' => 'hasil', 'id' => $kegiatan->id]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('absen-digital.berkas', ['jenis' => 'hasil', 'id' => $kegiatan->id]))
            ->assertOk();

        $this->actingAs($guru)
            ->get(route('absen-digital.berkas', ['jenis' => 'foto', 'id' => $hadir->id]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('absen-digital.cetak.halaman', ['kegiatan_id' => $kegiatan->id]))
            ->assertOk()
            ->assertSee('Daftar Hadir')
            ->assertSee('Siti Guru')
            ->assertSee('Tamu Undangan')
            ->assertSee('3509123456789012')
            ->assertSee('-8.171234')
            ->assertSee('window.print');
    }

    public function test_tautan_drive_palsu_ditolak_dan_ttd_kosong_ditolak(): void
    {
        $admin = $this->user('admin');
        $kegiatan = AbsenKegiatan::create([
            'nama' => 'Upacara',
            'jenis' => 'upacara',
            'tanggal' => now()->toDateString(),
            'kode' => 'UPACARA1',
            'dibuat_oleh' => $admin->id,
        ]);

        $this->actingAs($admin)->post(route('absen-digital.kegiatan.hasil.store', $kegiatan), [
            'hasil_drive_url' => 'https://evil.example/rahasia',
        ])->assertSessionHasErrors('hasil_drive_url');

        $this->actingAs($admin)->post(route('absen-digital.form.store'), [
            'kegiatan_id' => $kegiatan->id,
            'mode_peserta' => 'user',
            'user_id' => $admin->id,
            'foto' => UploadedFile::fake()->image('selfie.jpg'),
            'latitude' => -8.2,
            'longitude' => 113.7,
            'ttd' => $this->blankSignatureDataUrl(),
        ])->assertSessionHasErrors('ttd');

        $this->assertSame(0, AbsenKehadiran::query()->count());
    }

    private function user(string $role, ?string $name = null): User
    {
        return User::factory()->create([
            'name' => $name ?? ('Pengguna '.$role),
            'role' => $role,
            'is_active' => true,
        ]);
    }

    private function signatureDataUrl(): string
    {
        $image = imagecreatetruecolor(60, 30);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 10, 10, 10);
        imagefilledrectangle($image, 0, 0, 59, 29, $white);
        imagefilledrectangle($image, 8, 8, 40, 22, $black);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($binary);
    }

    private function blankSignatureDataUrl(): string
    {
        $image = imagecreatetruecolor(30, 20);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefilledrectangle($image, 0, 0, 29, 19, $white);
        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($binary);
    }
}
