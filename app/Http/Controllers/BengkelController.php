<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Jasa;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Sparepart;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BengkelController extends Controller
{
    // Halaman Depan untuk Pelanggan (Lacak Antrean)
    public function index(Request $request)
    {
        $platNomor = strtoupper(trim((string) $request->input('plat_nomor')));
        $antreans = Antrean::with(['kendaraan', 'mekanik'])
            ->when($platNomor !== '', fn ($query) => $query->where('plat_nomor', $platNomor))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('bengkel.index', compact('antreans', 'platNomor'));
    }

    // Simpan Antrean Baru
    public function storeAntrean(Request $request)
    {
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'merk_tipe' => ['required', 'string', 'max:255'],
            'keluhan' => ['required', 'string'],
        ]);

        $validated['plat_nomor'] = strtoupper(trim($validated['plat_nomor']));

        // Simpan / update data kendaraan
        Kendaraan::updateOrCreate(
            ['plat_nomor' => $validated['plat_nomor']],
            [
                'nama_pemilik' => $validated['nama_pemilik'],
                'jenis_kendaraan' => 'Motor',
                'merk_tipe' => $validated['merk_tipe'],
            ]
        );

        // Buat Antrean Baru
        $antrean = Antrean::create([
            'plat_nomor' => $validated['plat_nomor'],
            'keluhan' => $validated['keluhan'],
            'status' => 'Antre',
        ]);

        // Otomatis siapkan data transaksi
        Transaksi::create([
            'antrean_id' => $antrean->id,
            'total_biaya' => 0,
            'status_pembayaran' => 'Belum Bayar',
        ]);

        return redirect()->back()->with('success', 'Antrean berhasil ditambahkan!');
    }

    // Halaman Dashboard Admin / Mekanik (Kelola Status)
    public function admin()
    {
        $antreans = Antrean::with(['kendaraan.antrean.mekanik', 'mekanik'])->orderBy('id', 'desc')->get();
        $mekaniks = Karyawan::where('jabatan', 'Mekanik')->get();

        return view('bengkel.admin', compact('antreans', 'mekaniks'));
    }

    // Tambahkan data mekanik baru.
    public function storeMekanik(Request $request)
    {
        $validated = $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        Karyawan::create($validated + ['jabatan' => 'Mekanik']);

        return redirect()->route('bengkel.admin')->with('success', 'Data mekanik berhasil ditambahkan.');
    }

    // Perbarui data mekanik yang dipilih.
    public function updateMekanik(Request $request, Karyawan $mekanik)
    {
        abort_unless($mekanik->jabatan === 'Mekanik', 404);

        $validated = $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $mekanik->update($validated);

        return redirect()->route('bengkel.admin')->with('success', 'Data mekanik berhasil diperbarui.');
    }

    // Hapus mekanik; antrean lama tetap tersimpan tanpa penugasan.
    public function destroyMekanik(Karyawan $mekanik)
    {
        abort_unless($mekanik->jabatan === 'Mekanik', 404);
        $mekanik->delete();

        return redirect()->route('bengkel.admin')->with('success', 'Data mekanik berhasil dihapus.');
    }

    // Update Mekanik & Status Servis
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'id_mekanik' => ['nullable', 'exists:karyawans,id'],
            'status' => ['required', Rule::in(['Antre', 'Sedang Dikerjakan', 'Selesai'])],
        ]);

        $antrean = Antrean::findOrFail($id);
        $antrean->update($validated);

        return redirect()->back()->with('success', 'Status servis berhasil diperbarui!');
    }

    // Halaman Kasir (Pembayaran)
    public function kasir()
    {
        $transaksis = Transaksi::with([
            'antrean.kendaraan',
            'kasir',
            'jasaDetails.jasa',
            'sparepartDetails.sparepart',
        ])->orderBy('id', 'desc')->get();
        $kasirs = Karyawan::where('jabatan', 'Kasir')->get();
        $jasas = Jasa::orderBy('nama_jasa')->get();
        $spareparts = Sparepart::where('stok', '>', 0)->orderBy('nama_barang')->get();

        return view('bengkel.kasir', compact('transaksis', 'kasirs', 'jasas', 'spareparts'));
    }

    // Bayar Transaksi
    public function bayar(Request $request, $id)
    {
        $validated = $request->validate([
            'id_kasir' => ['required', Rule::exists('karyawans', 'id')->where('jabatan', 'Kasir')],
            'uang_dibayar' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'jasa_ids' => ['nullable', 'array'],
            'jasa_ids.*' => ['integer', 'distinct', 'exists:jasas,id'],
            'sparepart_ids' => ['nullable', 'array'],
            'sparepart_ids.*' => ['integer', 'distinct', 'exists:spareparts,id'],
        ]);

        $jasas = Jasa::whereIn('id', $validated['jasa_ids'] ?? [])->get();
        $spareparts = Sparepart::whereIn('id', $validated['sparepart_ids'] ?? [])->get();
        $totalBiaya = $jasas->sum('harga') + $spareparts->sum('harga');

        if ((float) $validated['uang_dibayar'] < (float) $totalBiaya) {
            return back()->withErrors([
                'uang_dibayar' => 'Uang yang diterima belum mencukupi total pembayaran.',
            ])->withInput();
        }

        DB::transaction(function () use ($validated, $id, $jasas, $spareparts, $totalBiaya): void {
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->update([
                'id_kasir' => $validated['id_kasir'],
                'total_biaya' => $totalBiaya,
                'uang_dibayar' => $validated['uang_dibayar'],
                'status_pembayaran' => 'Lunas',
            ]);

            $transaksi->jasaDetails()->delete();
            foreach ($jasas as $jasa) {
                $transaksi->jasaDetails()->create([
                    'jasa_id' => $jasa->id,
                    'jumlah' => 1,
                    'harga_satuan' => $jasa->harga,
                    'subtotal' => $jasa->harga,
                ]);
            }

            $transaksi->sparepartDetails()->delete();
            foreach ($spareparts as $sparepart) {
                $transaksi->sparepartDetails()->create([
                    'sparepart_id' => $sparepart->id,
                    'jumlah' => 1,
                    'harga_satuan' => $sparepart->harga,
                    'subtotal' => $sparepart->harga,
                ]);
            }
        });

        return redirect()->route('kasir.struk', $id)->with('success', 'Pembayaran berhasil disimpan.');
    }

    // Tampilkan struk transaksi yang sudah lunas.
    public function struk(Transaksi $transaksi)
    {
        abort_unless($transaksi->status_pembayaran === 'Lunas', 404);

        $transaksi->load([
            'antrean.kendaraan',
            'kasir',
            'jasaDetails.jasa',
            'sparepartDetails.sparepart',
        ]);

        return view('bengkel.struk', compact('transaksi'));
    }
}
