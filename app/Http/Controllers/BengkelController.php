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
            'jenis_kendaraan' => ['required', Rule::in(['Motor', 'Mobil'])],
            'merk_tipe' => ['required', 'string', 'max:255'],
            'keluhan' => ['required', 'string'],
        ]);

        $validated['plat_nomor'] = strtoupper(trim($validated['plat_nomor']));

        // Simpan / update data kendaraan
        Kendaraan::updateOrCreate(
            ['plat_nomor' => $validated['plat_nomor']],
            [
                'nama_pemilik' => $validated['nama_pemilik'],
                'jenis_kendaraan' => $validated['jenis_kendaraan'],
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
            'id_kasir' => ['required', 'exists:karyawans,id'],
            'total_biaya' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'jasa_ids' => ['nullable', 'array'],
            'jasa_ids.*' => ['integer', 'distinct', 'exists:jasas,id'],
            'sparepart_ids' => ['nullable', 'array'],
            'sparepart_ids.*' => ['integer', 'distinct', 'exists:spareparts,id'],
        ]);

        DB::transaction(function () use ($validated, $id): void {
            $transaksi = Transaksi::findOrFail($id);
            $transaksi->update([
                'id_kasir' => $validated['id_kasir'],
                'total_biaya' => $validated['total_biaya'],
                'status_pembayaran' => 'Lunas',
            ]);

            $transaksi->jasaDetails()->delete();
            foreach (Jasa::whereIn('id', $validated['jasa_ids'] ?? [])->get() as $jasa) {
                $transaksi->jasaDetails()->create([
                    'jasa_id' => $jasa->id,
                    'jumlah' => 1,
                    'harga_satuan' => $jasa->harga,
                    'subtotal' => $jasa->harga,
                ]);
            }

            $transaksi->sparepartDetails()->delete();
            foreach (Sparepart::whereIn('id', $validated['sparepart_ids'] ?? [])->get() as $sparepart) {
                $transaksi->sparepartDetails()->create([
                    'sparepart_id' => $sparepart->id,
                    'jumlah' => 1,
                    'harga_satuan' => $sparepart->harga,
                    'subtotal' => $sparepart->harga,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pembayaran berhasil ditambahkan!');
    }
}
