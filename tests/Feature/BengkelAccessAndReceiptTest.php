<?php

namespace Tests\Feature;

use App\Models\Antrean;
use App\Models\Jasa;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Sparepart;
use App\Models\Transaksi;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BengkelAccessAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_cashier_pages_require_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/kasir')->assertRedirect(route('login'));
        $this->get('/')->assertOk();
        $this->get('/antrean')->assertOk();
    }

    public function test_authenticated_home_displays_the_public_tracker(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
            ->get('/')
            ->assertOk();
    }

    public function test_customer_can_register_and_is_assigned_the_customer_role(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Dina Pelanggan',
            'email' => 'dina@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('pelanggan.dashboard'));

        $this->assertAuthenticated();
        $this->get(route('pelanggan.dashboard'))->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'dina@example.test',
            'role' => UserRole::Customer->value,
        ]);
    }

    public function test_customer_can_register_a_vehicle_for_service_from_their_dashboard(): void
    {
        $customer = User::factory()->create(['role' => UserRole::Customer]);

        $this->actingAs($customer)->post(route('antrean.store'), [
            'plat_nomor' => 'B 2222 XYZ',
            'nama_pemilik' => 'Nama yang diabaikan',
            'jenis_kendaraan' => 'Motor',
            'merk_tipe' => 'Vario',
            'keluhan' => 'Rem berbunyi',
        ])->assertRedirect(route('pelanggan.dashboard'));

        $this->assertDatabaseHas('kendaraans', [
            'plat_nomor' => 'B 2222 XYZ',
            'user_id' => $customer->id,
            'nama_pemilik' => $customer->name,
        ]);
        $this->assertDatabaseHas('antreans', [
            'plat_nomor' => 'B 2222 XYZ',
            'status' => Antrean::STATUS_QUEUE,
        ]);
    }

    public function test_public_tracking_hides_customer_details_but_owner_can_view_history(): void
    {
        $owner = User::factory()->create(['role' => UserRole::Customer]);
        $kendaraan = Kendaraan::create([
            'user_id' => $owner->id,
            'plat_nomor' => 'B 1111 XYZ',
            'nama_pemilik' => 'Rina Rahasia',
            'jenis_kendaraan' => 'Motor',
            'merk_tipe' => 'Beat',
        ]);
        Antrean::create([
            'plat_nomor' => $kendaraan->plat_nomor,
            'keluhan' => 'Keluhan pribadi',
            'status' => Antrean::STATUS_WORKING,
        ]);

        $this->get(route('home', ['q' => $kendaraan->plat_nomor]))
            ->assertOk()
            ->assertSee(Antrean::STATUS_WORKING)
            ->assertDontSee('Rina Rahasia')
            ->assertDontSee('Keluhan pribadi');

        $this->actingAs($owner)
            ->get(route('home', ['q' => $kendaraan->plat_nomor]))
            ->assertOk()
            ->assertSee('Rina Rahasia')
            ->assertSee('Keluhan pribadi');
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
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('bengkel.admin'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('bengkel.admin'))->assertOk();
    }

    public function test_admin_can_manage_mechanic_data(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($user)->post(route('admin.mekanik.store'), [
            'nama_karyawan' => 'Rudi Mekanik',
            'no_hp' => '081234567890',
            'email' => 'rudi.mekanik@example.test',
            'password' => 'password123',
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

    public function test_mechanic_records_service_and_deducts_stock_before_payment(): void
    {
        $user = User::factory()->create(['role' => UserRole::Mechanic]);
        $mekanik = Karyawan::create([
            'user_id' => $user->id,
            'nama_karyawan' => 'Agus Mekanik',
            'jabatan' => 'Mekanik',
        ]);
        $kendaraan = Kendaraan::create([
            'plat_nomor' => 'B 9876 XYZ',
            'nama_pemilik' => 'Rina Pelanggan',
            'jenis_kendaraan' => 'Motor',
            'merk_tipe' => 'Beat',
        ]);
        $antrean = Antrean::create([
            'plat_nomor' => $kendaraan->plat_nomor,
            'keluhan' => 'Mesin brebet',
            'status' => Antrean::STATUS_QUEUE,
        ]);
        $transaksi = Transaksi::create(['antrean_id' => $antrean->id]);
        $jasa = Jasa::create(['nama_jasa' => 'Servis Rutin', 'harga' => 50000]);
        $sparepart = Sparepart::create(['nama_barang' => 'Busi', 'stok' => 2, 'harga' => 20000]);

        $this->actingAs($user)
            ->post(route('mekanik.antrean.take', $antrean))
            ->assertRedirect();

        $serviceDetails = [
            'status' => Antrean::STATUS_WORKING,
            'jasa_ids' => [$jasa->id],
            'sparepart_ids' => [$sparepart->id],
            'sparepart_quantities' => [$sparepart->id => 1],
        ];

        $this->put(route('mekanik.antrean.update', $antrean), $serviceDetails)->assertRedirect();
        $this->put(route('mekanik.antrean.update', $antrean), [
            ...$serviceDetails,
            'status' => Antrean::STATUS_FINISHED,
        ])->assertRedirect();

        $this->assertDatabaseHas('antreans', [
            'id' => $antrean->id,
            'id_mekanik' => $mekanik->id,
            'status' => Antrean::STATUS_FINISHED,
        ]);
        $this->assertDatabaseHas('transaksi_spareparts', [
            'transaksi_id' => $transaksi->id,
            'sparepart_id' => $sparepart->id,
            'jumlah' => 1,
            'subtotal' => 20000,
        ]);
        $this->assertDatabaseHas('spareparts', ['id' => $sparepart->id, 'stok' => 1]);
    }

    public function test_payment_uses_item_prices_and_displays_printable_receipt(): void
    {
        $user = User::factory()->create(['role' => UserRole::Admin]);
        $data = $this->buatDataTransaksi();

        $response = $this->actingAs($user)->post(route('kasir.bayar', $data['transaksi']), [
            'metode_pembayaran' => 'Tunai',
            'uang_dibayar' => 100000,
        ]);

        $response->assertRedirect(route('kasir.struk', $data['transaksi']));
        $this->assertDatabaseHas('transaksis', [
            'id' => $data['transaksi']->id,
            'total_biaya' => 85000,
            'uang_dibayar' => 100000,
            'metode_pembayaran' => 'Tunai',
            'status_pembayaran' => 'Lunas',
        ]);
        $this->assertDatabaseHas('antreans', [
            'id' => $data['transaksi']->antrean_id,
            'status' => Antrean::STATUS_PAID,
        ]);
        $this->assertDatabaseHas('spareparts', [
            'id' => $data['sparepart']->id,
            'stok' => 4,
        ]);

        $this->get(route('kasir.struk', $data['transaksi']))
            ->assertOk()
            ->assertSee('Hendra Otomotif')
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

        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
            ->post(route('kasir.bayar', $data['transaksi']), [
                'metode_pembayaran' => 'Tunai',
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
            'status' => Antrean::STATUS_FINISHED,
        ]);
        $transaksi = Transaksi::create(['antrean_id' => $antrean->id]);
        $jasa = Jasa::create(['nama_jasa' => 'Servis Rutin', 'harga' => 50000]);
        $sparepart = Sparepart::create(['nama_barang' => 'Oli Mesin', 'stok' => 4, 'harga' => 35000]);
        $transaksi->jasaDetails()->create([
            'jasa_id' => $jasa->id,
            'jumlah' => 1,
            'harga_satuan' => $jasa->harga,
            'subtotal' => $jasa->harga,
        ]);
        $transaksi->sparepartDetails()->create([
            'sparepart_id' => $sparepart->id,
            'jumlah' => 1,
            'harga_satuan' => $sparepart->harga,
            'subtotal' => $sparepart->harga,
        ]);

        return compact('transaksi', 'kasir', 'jasa', 'sparepart');
    }
}
