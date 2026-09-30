<?php

namespace App\Http\Controllers;

use App\Models\Antrean;
use App\Models\Karyawan;
use App\Models\Kendaraan;
use App\Models\Transaksi;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BengkelController extends Controller
{
    public function index(Request $request): View
    {
        $search = strtoupper(trim($request->string('q')->toString()));
        $antreans = collect();
        $canViewHistory = false;

        if ($search !== '') {
            $matchingAntrean = Antrean::query()
                ->where('plat_nomor', $search)
                ->orWhere('kode_antrean', $search)
                ->latest('id')
                ->first();

            if ($matchingAntrean !== null) {
                $vehicleOwnerId = $matchingAntrean->kendaraan()->value('user_id');
                $canViewHistory = $request->user()?->role === UserRole::Customer
                    && (int) $request->user()->id === (int) $vehicleOwnerId;
                $relations = ['kendaraan', 'mekanik'];
                if ($canViewHistory) {
                    $relations = array_merge($relations, [
                        'transaksi.kasir',
                        'transaksi.jasaDetails.jasa',
                        'transaksi.sparepartDetails.sparepart',
                    ]);
                }

                $antreans = Antrean::with($relations)
                    ->where('plat_nomor', $matchingAntrean->plat_nomor)
                    ->latest('id')
                    ->get();
            }
        }

        return view('bengkel.index', [
            'antreans' => $antreans,
            'search' => $search,
            'canViewHistory' => $canViewHistory,
            'queueCount' => Antrean::where('status', Antrean::STATUS_QUEUE)->count(),
            'workingCount' => Antrean::where('status', Antrean::STATUS_WORKING)->count(),
            'finishedCount' => Antrean::whereIn('status', [Antrean::STATUS_FINISHED, Antrean::STATUS_PAID])->count(),
        ]);
    }

    public function pelanggan(Request $request): View
    {
        $kendaraans = Kendaraan::query()
            ->where('user_id', $request->user()->id)
            ->with([
                'antrean.mekanik',
                'antrean.transaksi.jasaDetails.jasa',
                'antrean.transaksi.sparepartDetails.sparepart',
            ])
            ->orderBy('plat_nomor')
            ->get();

        return view('bengkel.pelanggan', [
            'kendaraans' => $kendaraans,
            'customer' => $request->user(),
        ]);
    }

    // Simpan Antrean Baru
    public function storeAntrean(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plat_nomor' => ['required', 'string', 'max:20'],
            'nama_pemilik' => ['required', 'string', 'max:255'],
            'jenis_kendaraan' => ['required', Rule::in(['Motor', 'Mobil'])],
            'merk_tipe' => ['required', 'string', 'max:255'],
            'keluhan' => ['required', 'string'],
            'customer_user_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'pelanggan')],
        ]);

        $validated['plat_nomor'] = strtoupper(trim($validated['plat_nomor']));
        $currentUser = auth()->user();
        $ownerId = $currentUser->role === UserRole::Customer
            ? $currentUser->id
            : ($validated['customer_user_id'] ?? null);
        if ($currentUser->role === UserRole::Customer) {
            $validated['nama_pemilik'] = $currentUser->name;
        }

        $antrean = DB::transaction(function () use ($validated, $ownerId): Antrean {
            $existingVehicle = Kendaraan::find($validated['plat_nomor']);
            abort_if(
                $existingVehicle?->user_id !== null
                    && $ownerId !== null
                    && (int) $existingVehicle->user_id !== (int) $ownerId,
                403,
            );

            $kendaraan = Kendaraan::updateOrCreate(
                ['plat_nomor' => $validated['plat_nomor']],
                [
                    'user_id' => $ownerId ?? $existingVehicle?->user_id,
                    'nama_pemilik' => $validated['nama_pemilik'],
                    'jenis_kendaraan' => $validated['jenis_kendaraan'],
                    'merk_tipe' => $validated['merk_tipe'],
                ],
            );

            $antrean = Antrean::create([
                'plat_nomor' => $kendaraan->plat_nomor,
                'keluhan' => $validated['keluhan'],
                'status' => Antrean::STATUS_QUEUE,
            ]);
            $antrean->update([
                'kode_antrean' => 'SRV-'.now()->format('ymd').'-'.str_pad((string) $antrean->id, 5, '0', STR_PAD_LEFT),
            ]);

            Transaksi::create([
                'antrean_id' => $antrean->id,
                'total_biaya' => 0,
                'status_pembayaran' => 'Belum Bayar',
            ]);

            return $antrean;
        });

        if ($currentUser->role === UserRole::Customer) {
            return redirect()->route('pelanggan.dashboard')
                ->with('success', 'Antrean berhasil dibuat dengan kode '.$antrean->kode_antrean.'.');
        }

        return redirect()->route('bengkel.index', ['q' => $antrean->kode_antrean])
            ->with('success', 'Antrean berhasil dibuat.')
            ->with('kode_antrean', $antrean->kode_antrean);
    }

    // Halaman Dashboard Admin / Mekanik (Kelola Status)
    public function admin(): View
    {
        $antreans = Antrean::with(['kendaraan.antrean.mekanik', 'mekanik', 'transaksi'])
            ->orderByDesc('id')
            ->get();
        $mekaniks = Karyawan::where('jabatan', 'Mekanik')->get();
        $users = User::with('karyawan')->orderBy('name')->get();
        $customers = User::where('role', UserRole::Customer)->orderBy('name')->get();

        return view('bengkel.admin', compact('antreans', 'mekaniks', 'users', 'customers'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated): void {
            $role = UserRole::from($validated['role']);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $role,
            ]);

            if ($role !== UserRole::Customer) {
                Karyawan::create([
                    'user_id' => $user->id,
                    'nama_karyawan' => $validated['name'],
                    'no_hp' => $validated['no_hp'] ?? null,
                    'jabatan' => match ($role) {
                        UserRole::Admin => 'Admin',
                        UserRole::Mechanic => 'Mekanik',
                        UserRole::Customer => throw new \LogicException,
                    },
                ]);
            }
        });

        return redirect()->route('bengkel.admin')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->update(array_filter($validated, fn ($value) => $value !== null && $value !== ''));
        $user->karyawan?->update(['nama_karyawan' => $validated['name']]);

        return redirect()->route('bengkel.admin')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroyUser(User $user): RedirectResponse
    {
        abort_unless($user->id !== auth()->id(), 403, 'Akun yang sedang digunakan tidak dapat dihapus.');

        if ($user->karyawan?->jabatan === 'Mekanik'
            && Antrean::where('id_mekanik', $user->karyawan->id)
                ->whereIn('status', [Antrean::STATUS_QUEUE, Antrean::STATUS_WORKING])
                ->exists()) {
            throw ValidationException::withMessages([
                'user' => 'Pindahkan antrean aktif mekanik sebelum menghapus akunnya.',
            ]);
        }

        $user->delete();

        return redirect()->route('bengkel.admin')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    // Tambahkan data mekanik baru.
    public function storeMekanik(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::create([
                'name' => $validated['nama_karyawan'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => UserRole::Mechanic,
            ]);

            Karyawan::create([
                'user_id' => $user->id,
                'nama_karyawan' => $validated['nama_karyawan'],
                'no_hp' => $validated['no_hp'] ?? null,
                'jabatan' => 'Mekanik',
            ]);
        });

        return redirect()->route('bengkel.admin')->with('success', 'Akun dan data mekanik berhasil dibuat.');
    }

    // Perbarui data mekanik yang dipilih.
    public function updateMekanik(Request $request, Karyawan $mekanik): RedirectResponse
    {
        abort_unless($mekanik->jabatan === 'Mekanik', 404);

        $validated = $request->validate([
            'nama_karyawan' => ['required', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $mekanik->update($validated);
        $mekanik->user?->update(['name' => $validated['nama_karyawan']]);

        return redirect()->route('bengkel.admin')->with('success', 'Data mekanik berhasil diperbarui.');
    }

    // Hapus mekanik; antrean lama tetap tersimpan tanpa penugasan.
    public function destroyMekanik(Karyawan $mekanik): RedirectResponse
    {
        abort_unless($mekanik->jabatan === 'Mekanik', 404);
        $mekanik->user?->delete();
        $mekanik->delete();

        return redirect()->route('bengkel.admin')->with('success', 'Data mekanik berhasil dihapus.');
    }

    // Update Mekanik & Status Servis
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'id_mekanik' => ['nullable', Rule::exists('karyawans', 'id')->where('jabatan', 'Mekanik')],
        ]);

        $antrean = Antrean::findOrFail($id);
        abort_unless(in_array($antrean->status, [Antrean::STATUS_QUEUE, Antrean::STATUS_WORKING], true), 409);
        $antrean->update(['id_mekanik' => $validated['id_mekanik'] ?? null]);

        return redirect()->back()->with('success', 'Penugasan mekanik berhasil diperbarui.');
    }

    // Halaman Kasir (Pembayaran)
    public function kasir(): View
    {
        $transaksis = Transaksi::with([
            'antrean.kendaraan',
            'kasir',
            'jasaDetails.jasa',
            'sparepartDetails.sparepart',
        ])
            ->whereHas('antrean', fn ($query) => $query->whereIn('status', [Antrean::STATUS_FINISHED, Antrean::STATUS_PAID]))
            ->orderByDesc('id')
            ->get();

        return view('bengkel.kasir', compact('transaksis'));
    }

    public function bayar(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $validated = $request->validate([
            'metode_pembayaran' => ['required', Rule::in(['Tunai', 'Non-Tunai'])],
            'uang_dibayar' => ['nullable', 'required_if:metode_pembayaran,Tunai', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $user = $request->user();
        $karyawan = $user->karyawan;
        abort_if($user->role !== UserRole::Admin, 403);

        DB::transaction(function () use ($validated, $transaksi, $karyawan): void {
            $lockedTransaksi = Transaksi::query()
                ->with('antrean')
                ->lockForUpdate()
                ->findOrFail($transaksi->id);
            abort_unless(
                $lockedTransaksi->antrean->status === Antrean::STATUS_FINISHED
                    && $lockedTransaksi->status_pembayaran === 'Belum Bayar',
                409,
            );

            $jasaDetails = $lockedTransaksi->jasaDetails()->get();
            $sparepartDetails = $lockedTransaksi->sparepartDetails()->get();
            $totalBiaya = $jasaDetails->sum('subtotal') + $sparepartDetails->sum('subtotal');
            $uangDibayar = $validated['metode_pembayaran'] === 'Tunai'
                ? (float) $validated['uang_dibayar']
                : $totalBiaya;

            if ($uangDibayar < $totalBiaya) {
                throw ValidationException::withMessages([
                    'uang_dibayar' => 'Uang yang diterima belum mencukupi total pembayaran.',
                ]);
            }

            $lockedTransaksi->update([
                'id_kasir' => $karyawan?->id,
                'total_biaya' => $totalBiaya,
                'uang_dibayar' => $uangDibayar,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => 'Lunas',
            ]);
            $lockedTransaksi->antrean->update(['status' => Antrean::STATUS_PAID]);
        });

        return redirect()->route('kasir.struk', $transaksi)->with('success', 'Pembayaran berhasil disimpan.');
    }

    public function struk(Transaksi $transaksi): View
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
