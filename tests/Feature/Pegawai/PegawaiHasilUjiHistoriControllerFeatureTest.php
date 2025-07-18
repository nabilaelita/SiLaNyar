<?php

namespace Tests\Feature\Pegawai;

use App\Models\FormPengajuan;
use App\Models\HasilUji;
use App\Models\HasilUjiHistori;
use App\Models\Instansi;
use App\Models\JenisCairan;
use App\Models\Kategori;
use App\Models\ParameterUji;
use App\Models\Pengujian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PegawaiHasilUjiHistoriControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $pegawai;
    protected User $customer;
    protected HasilUji $hasilUji;
    protected HasilUjiHistori $histori;
    protected Kategori $kategori;
    protected ParameterUji $parameter;

    public function setUp(): void
    {
        parent::setUp();

        // Konfigurasi Vite untuk testing
        config(['app.asset_url' => null]);

        // Membuat role dan permission
        $pegawaiRole = Role::firstOrCreate(
            ['name' => 'pegawai', 'guard_name' => 'web'],
            ['kode_role' => 'RL-002']
        );
        
        $customerRole = Role::firstOrCreate(
            ['name' => 'customer', 'guard_name' => 'web'],
            ['kode_role' => 'RL-001']
        );

        $permission = Permission::firstOrCreate([
            'name' => 'riwayat hasil uji',
            'guard_name' => 'web',
            'kode_permission' => 'PS-007'
        ]);

        $pegawaiRole->givePermissionTo($permission);

        // Membuat user
        $this->pegawai = User::factory()->create();
        $this->pegawai->assignRole($pegawaiRole);

        $this->customer = User::factory()->create();
        $this->customer->assignRole($customerRole);

        // Membuat data untuk testing
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        $instansi = Instansi::factory()->create(['id_user' => $this->customer->id]);
        $jenisCairan = JenisCairan::factory()->create();
        $this->kategori = Kategori::factory()->create();
        $this->parameter = ParameterUji::factory()->create();

        // Menghubungkan parameter ke kategori dengan baku_mutu
        $this->kategori->parameter()->attach($this->parameter->id, ['baku_mutu' => 50.0]);

        $formPengajuan = FormPengajuan::factory()->create([
            'id_instansi' => $instansi->id,
            'id_kategori' => $this->kategori->id,
            'id_jenis_cairan' => $jenisCairan->id,
            'status_pengajuan' => 'diterima'
        ]);

        $pengujian = Pengujian::factory()->create([
            'id_form_pengajuan' => $formPengajuan->id,
            'id_user' => $this->pegawai->id
        ]);

        $this->hasilUji = HasilUji::factory()->create([
            'id_pengujian' => $pengujian->id,
            'status' => 'draf'
        ]);

        // Membuat data parameter_pengujian yang akan dibaca oleh controller
        DB::table('parameter_pengujian')->insert([
            'id_pengujian' => $pengujian->id,
            'id_parameter' => $this->parameter->id,
            'nilai' => 45.5,
            'keterangan' => 'Memenuhi baku mutu',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Membuat histori dengan struktur data yang sederhana untuk matching controller
        $this->histori = HasilUjiHistori::factory()->create([
            'id_hasil_uji' => $this->hasilUji->id,
            'data_parameterdanpengujian' => [
                [
                    'id_parameter' => $this->parameter->id,
                    'nilai' => '45.5',
                    'keterangan' => 'Memenuhi baku mutu',
                ]
            ],
            'status' => 'proses_review',
            'diupdate_oleh' => $this->pegawai->nama ?? $this->pegawai->name
        ]);
    }

    public function test_index_menampilkan_daftar_histori_hasil_uji()
    {
        $response = $this->actingAs($this->pegawai)
            ->get(route('pegawai.hasil_uji.riwayat', $this->hasilUji->id));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/Histori')
                ->has('hasil_uji', fn (Assert $hasilUji) => $hasilUji
                    ->where('id', $this->hasilUji->id)
                    ->where('status', 'draf')
                    ->has('kode_hasil_uji')
                    ->etc()
                )
                ->has('histori', 1, fn (Assert $histori) => $histori
                    ->where('id', $this->histori->id)
                    ->where('status', 'proses_review')
                    ->where('diupdate_oleh', $this->pegawai->nama ?? $this->pegawai->name)
                    ->has('hasil_uji.pengujian.form_pengajuan.instansi.user')
                    ->has('hasil_uji.pengujian.user')
                    ->has('data_parameterdanpengujian')
                    ->etc()
                )
            );
    }

    public function test_index_mengurutkan_histori_berdasarkan_tanggal_terbaru()
    {
        // Membuat record histori tambahan dengan waktu yang jelas berbeda
        $histori2 = HasilUjiHistori::factory()->create([
            'id_hasil_uji' => $this->hasilUji->id,
            'status' => 'draf',
            'diupdate_oleh' => $this->pegawai->nama ?? $this->pegawai->name,
            'created_at' => now()->subDays(2)
        ]);

        $histori3 = HasilUjiHistori::factory()->create([
            'id_hasil_uji' => $this->hasilUji->id,
            'status' => 'revisi',
            'diupdate_oleh' => $this->pegawai->nama ?? $this->pegawai->name,
            'created_at' => now()->subDays(1)
        ]);

        $response = $this->actingAs($this->pegawai)
            ->get(route('pegawai.hasil_uji.riwayat', $this->hasilUji->id));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/Histori')
                ->has('histori', 3)
                // Verifikasi dengan mengambil data dan membandingkan tanggal
                ->where('histori', function ($histori) {
                    // Konversi ke Carbon untuk perbandingan yang lebih mudah
                    $dates = collect($histori)->pluck('created_at')->map(fn($date) => \Carbon\Carbon::parse($date));
                    
                    // Verifikasi bahwa tanggal diurutkan dari terbaru ke terlama
                    return $dates[0]->gte($dates[1]) && $dates[1]->gte($dates[2]);
                })
            );
    }

    public function test_index_memerlukan_permission_riwayat_hasil_uji()
    {
        $userTanpaPermission = User::factory()->create();
        $rolePolos = Role::create([
            'name' => 'role_polos',
            'guard_name' => 'web',
            'kode_role' => 'RL-999'
        ]);
        $userTanpaPermission->assignRole($rolePolos);

        $response = $this->actingAs($userTanpaPermission)
            ->get(route('pegawai.hasil_uji.riwayat', $this->hasilUji->id));

        $response->assertStatus(403);
    }

    public function test_index_menangani_hasil_uji_tidak_ditemukan()
    {
        $response = $this->actingAs($this->pegawai)
            ->get(route('pegawai.hasil_uji.riwayat', 99999));

        $response->assertStatus(404);
    }

    public function test_show_menampilkan_detail_histori_dengan_data_parameter()
    {
        $response = $this->actingAs($this->pegawai)
            ->get('/pegawai/hasiluji/riwayat/show/' . $this->histori->id);

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/ShowHistori')
                ->has('histori', fn (Assert $histori) => $histori
                    ->where('id', $this->histori->id)
                    ->where('status', 'proses_review')
                    ->where('diupdate_oleh', $this->pegawai->nama ?? $this->pegawai->name)
                    ->has('data_parameterdanpengujian')
                    ->has('hasil_uji.pengujian.form_pengajuan.kategori')
                    ->has('hasil_uji.pengujian.form_pengajuan.instansi.user')
                    ->has('hasil_uji.pengujian.user')
                    ->etc()
                )
                ->has('data_parameter', 1, fn (Assert $param) => $param
                    ->where('id_parameter', $this->parameter->id)
                    ->where('nilai', '45.5')
                    ->where('keterangan', 'Memenuhi baku mutu')
                    ->etc()
                )
            );
    }

    public function test_show_menampilkan_parameter_dari_kategori()
    {
        // Membuat parameter tambahan untuk kategori
        $parameterTambahan = ParameterUji::factory()->create(['nama_parameter' => 'Parameter Tambahan']);
        $this->kategori->parameter()->attach($parameterTambahan->id, ['baku_mutu' => 100.0]);

        // Update histori dengan data parameter tambahan
        $this->histori->update([
            'data_parameterdanpengujian' => [
                [
                    'id_parameter' => $this->parameter->id,
                    'nilai' => '45.5',
                    'keterangan' => 'Memenuhi baku mutu',
                ],
                [
                    'id_parameter' => $parameterTambahan->id,
                    'nilai' => '95.0',
                    'keterangan' => 'Memenuhi baku mutu',
                ]
            ]
        ]);

        $response = $this->actingAs($this->pegawai)
            ->get('/pegawai/hasiluji/riwayat/show/' . $this->histori->id);

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/ShowHistori')
                ->has('data_parameter', 2)
            );
    }

    public function test_show_menangani_parameter_tidak_ditemukan()
    {
        // Membuat parameter yang tidak dikaitkan dengan kategori
        $parameterTidakTerkait = ParameterUji::factory()->create(['nama_parameter' => 'Parameter Tidak Terkait']);
        
        // Update histori dengan parameter yang tidak terkait kategori
        $this->histori->update([
            'data_parameterdanpengujian' => [
                [
                    'id_parameter' => $this->parameter->id,
                    'nilai' => '45.5',
                    'keterangan' => 'Test',
                ],
                [
                    'id_parameter' => $parameterTidakTerkait->id,
                    'nilai' => '45.5',
                    'keterangan' => 'Test',
                ]
            ]
        ]);

        $response = $this->actingAs($this->pegawai)
            ->get('/pegawai/hasiluji/riwayat/show/' . $this->histori->id);

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/ShowHistori')
                ->has('data_parameter', 2)
            );
    }

    public function test_show_memerlukan_permission_riwayat_hasil_uji()
    {
        $userTanpaPermission = User::factory()->create();
        $rolePolos = Role::create([
            'name' => 'role_polos_show', 
            'guard_name' => 'web',
            'kode_role' => 'RL-998'
        ]);
        $userTanpaPermission->assignRole($rolePolos);

        $response = $this->actingAs($userTanpaPermission)
            ->get('/pegawai/hasiluji/riwayat/show/' . $this->histori->id);

        $response->assertStatus(403);
    }

    public function test_show_menangani_histori_tidak_ditemukan()
    {
        $response = $this->actingAs($this->pegawai)
            ->get('/pegawai/hasiluji/riwayat/show/99999');

        $response->assertStatus(404);
    }

    public function test_index_menampilkan_histori_kosong_untuk_hasil_uji_tanpa_histori()
    {
        $hasilUjiKosong = HasilUji::factory()->create([
            'id_pengujian' => $this->hasilUji->pengujian->id
        ]);

        $response = $this->actingAs($this->pegawai)
            ->get(route('pegawai.hasil_uji.riwayat', $hasilUjiKosong->id));

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/Histori')
                ->has('hasil_uji')
                ->has('histori', 0)
            );
    }

    public function test_show_menangani_data_parameter_kosong()
    {
        // Membuat hasil uji baru tanpa parameter_pengujian
        $pengujianKosong = Pengujian::factory()->create([
            'id_form_pengajuan' => $this->hasilUji->pengujian->form_pengajuan->id,
            'id_user' => $this->pegawai->id
        ]);

        $hasilUjiKosong = HasilUji::factory()->create([
            'id_pengujian' => $pengujianKosong->id
        ]);

        $historiKosong = HasilUjiHistori::factory()->create([
            'id_hasil_uji' => $hasilUjiKosong->id,
            'data_parameterdanpengujian' => [],
            'diupdate_oleh' => $this->pegawai->nama ?? $this->pegawai->name
        ]);

        $response = $this->actingAs($this->pegawai)
            ->get('/pegawai/hasiluji/riwayat/show/' . $historiKosong->id);

        $response->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('pegawai/hasil_uji/ShowHistori')
                ->has('histori')
                ->has('data_parameter', 0)
            );
    }
}
