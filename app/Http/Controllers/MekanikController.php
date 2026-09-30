<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Jasa;
use App\Models\Karyawan;
use App\Models\Sparepart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MekanikController extends Controller
{
    public function index(Request $request): View
    {
        $mekanik = $this->authenticatedMechanic($request);
        $antreans = Antrean::with([
            'kendaraan',
            'transaksi.jasaDetails.jasa',
            'transaksi.sparepartDetails.sparepart',
        ])
            ->where(function ($query) use ($mekanik): void {
                $query->whereNull('id_mekanik')->orWhere('id_mekanik', $mekanik->id);
            })
            ->whereIn('status', [Antrean::STATUS_QUEUE, Antrean::STATUS_WORKING])
            ->orderBy('id')
            ->get();

        return view('bengkel.mekanik', [
            'antreans' => $antreans,
            'mekanik' => $mekanik,
            'jasas' => Jasa::orderBy('nama_jasa')->get(),
            'spareparts' => Sparepart::where('stok', '>', 0)->orderBy('nama_barang')->get(),
        ]);
    }

    public function take(Request $request, Antrean $antrean): RedirectResponse
    {
        $mekanik = $this->authenticatedMechanic($request);

        DB::transaction(function () use ($antrean, $mekanik): void {
            $lockedAntrean = Antrean::query()->lockForUpdate()->findOrFail($antrean->id);
            abort_unless(
                $lockedAntrean->status === Antrean::STATUS_QUEUE
                    && ($lockedAntrean->id_mekanik === null || (int) $lockedAntrean->id_mekanik === $mekanik->id),
                409,
            );

            $lockedAntrean->update([
                'id_mekanik' => $mekanik->id,
                'status' => Antrean::STATUS_WORKING,
            ]);

            $lockedAntrean->transaksi()->firstOrCreate([], [
                'status_pembayaran' => 'Belum Bayar',
                'total_biaya' => 0,
            ]);
        });

        return back()->with('success', 'Antrean berhasil diambil untuk dikerjakan.');
    }

    public function update(Request $request, Antrean $antrean): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([Antrean::STATUS_WORKING, Antrean::STATUS_FINISHED])],
            'jasa_ids' => ['nullable', 'array'],
            'jasa_ids.*' => ['integer', 'distinct', 'exists:jasas,id'],
            'sparepart_ids' => ['nullable', 'array'],
            'sparepart_ids.*' => ['integer', 'distinct', 'exists:spareparts,id'],
            'sparepart_quantities' => ['nullable', 'array'],
            'sparepart_quantities.*' => ['integer', 'min:1'],
        ]);
        $mekanik = $this->authenticatedMechanic($request);

        DB::transaction(function () use ($antrean, $mekanik, $validated): void {
            $lockedAntrean = Antrean::query()->lockForUpdate()->findOrFail($antrean->id);
            abort_unless(
                (int) $lockedAntrean->id_mekanik === $mekanik->id
                    && $lockedAntrean->status === Antrean::STATUS_WORKING,
                403,
            );

            $transaksi = $lockedAntrean->transaksi()->firstOrCreate([], [
                'status_pembayaran' => 'Belum Bayar',
                'total_biaya' => 0,
            ]);
            $jasaItems = Jasa::whereIn('id', $validated['jasa_ids'] ?? [])->get()->keyBy('id');
            $sparepartItems = Sparepart::whereIn('id', $validated['sparepart_ids'] ?? [])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($jasaItems->count() !== count($validated['jasa_ids'] ?? [])
                || $sparepartItems->count() !== count($validated['sparepart_ids'] ?? [])) {
                abort(422, 'Data jasa atau sparepart tidak valid.');
            }

            $transaksi->jasaDetails()->delete();
            foreach ($jasaItems as $jasa) {
                $transaksi->jasaDetails()->create([
                    'jasa_id' => $jasa->id,
                    'jumlah' => 1,
                    'harga_satuan' => $jasa->harga,
                    'subtotal' => $jasa->harga,
                ]);
            }

            $existingSparepartDetails = $transaksi->sparepartDetails()->get()->keyBy('sparepart_id');
            foreach ($existingSparepartDetails as $sparepartId => $detail) {
                if (! $sparepartItems->has($sparepartId)) {
                    Sparepart::whereKey($sparepartId)->increment('stok', $detail->jumlah);
                    $detail->delete();
                }
            }

            foreach ($sparepartItems as $sparepart) {
                $quantity = (int) ($validated['sparepart_quantities'][$sparepart->id] ?? 1);
                $existingDetail = $existingSparepartDetails->get($sparepart->id);
                $oldQuantity = (int) ($existingDetail?->jumlah ?? 0);
                abort_unless($quantity <= $sparepart->stok + $oldQuantity, 422, 'Jumlah sparepart melebihi stok tersedia.');

                $quantityChange = $quantity - $oldQuantity;
                if ($quantityChange > 0) {
                    $sparepart->decrement('stok', $quantityChange);
                } elseif ($quantityChange < 0) {
                    $sparepart->increment('stok', abs($quantityChange));
                }

                $transaksi->sparepartDetails()->updateOrCreate([
                    'sparepart_id' => $sparepart->id,
                ], [
                    'jumlah' => $quantity,
                    'harga_satuan' => $sparepart->harga,
                    'subtotal' => $sparepart->harga * $quantity,
                ]);
            }

            $lockedAntrean->update(['status' => $validated['status']]);
        });

        return back()->with('success', 'Pengerjaan dan item servis berhasil diperbarui.');
    }

    private function authenticatedMechanic(Request $request): Karyawan
    {
        return $request->user()->karyawan()->where('jabatan', 'Mekanik')->firstOrFail();
    }
}
