<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Jasa;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Sparepart;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BengkelAccessAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_cashier_pages_require_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/kasir')->assertRedirect(route('login'));
        $this->get('/')->assertRedirect(route('login'));
        $this->get('/antrean')->assertOk();
    }

    public function test_authenticated_home_redirects_to_the_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(route('bengkel.admin'));
    }

    public function test_login_page_renders_the_stisla_login_form(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Login Petugas')
            ->assertSee('Kata sandi');
    }

    public function test_staff_can_log_in_and_open_admin_page(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('bengkel.admin'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('bengkel.admin'))->assertOk();
    }

    public function test_admin_can_manage_mechanic_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.mekanik.store'), [
            'nama_karyawan' => 'Rudi Mekanik',
            'no_hp' => '081234567890',
        ])->assertRedirect(route('bengkel.admin'));

        $mekanik = Karyawan::where('nama_karyawan', 'Rudi Mekanik')->firstOrFail();
        $this->assertSame('Mekanik', $mekanik->jabatan);

        $this->put(route('admin.mekanik.update', $mekanik), [
            'nama_karyawan' => 'Rudi Pratama',
            'no_hp' => '081111111111',
        ])->assertRedirect(route('bengkel.admin'));
        $this->assertDatabaseHas('karyawans', ['id' => $mekanik->id, 'nama_karyawan' => 'Rudi Pratama']);

        $this->delete(route('admin.mekanik.destroy', $mekanik))->assertRedirect(route('bengkel.admin'));
        $this->assertDatabaseMissing('karyawans', ['id' => $mekanik->id]);
    }

    public function test_payment_uses_item_prices_and_displays_printable_receipt(): void
    {
        $user = User::factory()->create();
        $data = $this->buatDataTransaksi();

        $response = $this->actingAs($user)->post(route('kasir.bayar', $data['transaksi']), [
            'id_kasir' => $data['kasir']->id,
            'jasa_ids' => [$data['jasa']->id],
            'sparepart_ids' => [$data['sparepart']->id],
            'uang_dibayar' => 100000,
        ]);

        $response->assertRedirect(route('kasir.struk', $data['transaksi']));
        $this->assertDatabaseHas('transaksis', [
            'id' => $data['transaksi']->id,
            'total_biaya' => 85000,
            'uang_dibayar' => 100000,
            'status_pembayaran' => 'Lunas',
        ]);

        $this->get(route('kasir.struk', $data['transaksi']))
            ->assertOk()
            ->assertSee('Sentosa Motor')
            ->assertSee('Dina Pelanggan')
            ->assertSee('B 1234 ABC')
            ->assertSee('Servis Rutin')
            ->assertSee('Oli Mesin')
            ->assertSee('Rp 85.000')
            ->assertSee('Rp 15.000')
            ->assertSee('window.print()');
    }

    public function test_payment_rejects_cash_below_the_calculated_total(): void
    {
        $data = $this->buatDataTransaksi();

        $this->actingAs(User::factory()->create())
            ->post(route('kasir.bayar', $data['transaksi']), [
                'id_kasir' => $data['kasir']->id,
                'jasa_ids' => [$data['jasa']->id],
                'sparepart_ids' => [$data['sparepart']->id],
                'uang_dibayar' => 80000,
            ])
            ->assertSessionHasErrors('uang_dibayar');

        $this->assertDatabaseHas('transaksis', [
            'id' => $data['transaksi']->id,
            'status_pembayaran' => 'Belum Bayar',
        ]);
    }

    /** @return array{transaksi: Transaksi, kasir: Karyawan, jasa: Jasa, sparepart: Sparepart} */
    private function buatDataTransaksi(): array
    {
        $kasir = Karyawan::create(['nama_karyawan' => 'Siti Kasir', 'jabatan' => 'Kasir']);
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 1234 ABC',
            'nama_pemilik' => 'Dina Pelanggan',
            'jenis_kendaraan' => 'Motor',
            'merk_tipe' => 'Vario 150',
        ]);
        $antrean = Antrean::create([
            'plat_nomor' => $kendaraan->plat_nomor,
            'keluhan' => 'Servis rutin',
            'status' => 'Selesai',
        ]);
        $transaksi = Transaksi::create(['antrean_id' => $antrean->id]);
        $jasa = Jasa::create(['nama_jasa' => 'Servis Rutin', 'harga' => 50000]);
        $sparepart = Sparepart::create(['nama_barang' => 'Oli Mesin', 'stok' => 5, 'harga' => 35000]);

        return compact('transaksi', 'kasir', 'jasa', 'sparepart');
    }
}
