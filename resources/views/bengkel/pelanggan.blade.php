@extends('layouts.app')

@section('content')
<div class="section-header shadow-sm mb-4">
    <h1>Dashboard Pelanggan</h1>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm mb-4">
    <div class="card-header"><h4 class="mb-0">Daftarkan servis</h4></div>
    <div class="card-body">
        <form action="{{ route('antrean.store') }}" method="POST" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-3">
                <label for="customer_plat">Nomor plat</label>
                <input id="customer_plat" name="plat_nomor" class="form-control" value="{{ old('plat_nomor') }}" maxlength="20" placeholder="B 1234 ABC" required>
            </div>
            <div class="form-group col-md-3">
                <label for="customer_owner">Nama pemilik</label>
                <input id="customer_owner" name="nama_pemilik" class="form-control" value="{{ $customer->name }}" readonly>
            </div>
            <div class="form-group col-md-2">
                <label for="customer_vehicle_type">Jenis kendaraan</label>
                <select id="customer_vehicle_type" name="jenis_kendaraan" class="form-control" required>
                    <option value="Motor">Motor</option>
                    <option value="Mobil">Mobil</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="customer_vehicle_model">Merk dan tipe</label>
                <input id="customer_vehicle_model" name="merk_tipe" class="form-control" value="{{ old('merk_tipe') }}" maxlength="255" placeholder="Contoh: Vario 150" required>
            </div>
            <div class="form-group col-12">
                <label for="customer_complaint">Keluhan awal</label>
                <textarea id="customer_complaint" name="keluhan" class="form-control" rows="3" required>{{ old('keluhan') }}</textarea>
            </div>
            <div class="form-group col-12 mb-0">
                <button type="submit" class="btn btn-primary"><i class="fas fa-calendar-plus mr-1"></i>Buat antrean servis</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h4 class="mb-0">Kendaraan dan riwayat servis</h4></div>
    <div class="card-body">
        @forelse($kendaraans as $kendaraan)
            <section class="border-bottom pb-3 mb-3">
                <h5 class="mb-1">{{ $kendaraan->plat_nomor }} <small class="text-muted">{{ $kendaraan->jenis_kendaraan }} · {{ $kendaraan->merk_tipe }}</small></h5>
                @forelse($kendaraan->antrean as $antrean)
                    <details class="py-2">
                        <summary>
                            <strong>{{ $antrean->created_at?->format('d/m/Y') }}</strong>
                            <span class="mx-1">·</span>{{ $antrean->kode_antrean }}
                            <span class="badge {{ $antrean->status === 'Lunas' ? 'badge-success' : ($antrean->status === 'Selesai' ? 'badge-primary' : 'badge-warning') }} ml-1">{{ $antrean->status }}</span>
                        </summary>
                        <div class="pt-2 pl-3">
                            <div><strong>Keluhan:</strong> {{ $antrean->keluhan }}</div>
                            <div><strong>Mekanik:</strong> {{ $antrean->mekanik->nama_karyawan ?? 'Belum ditugaskan' }}</div>
                            @if($antrean->transaksi?->jasaDetails->isNotEmpty())
                                <div class="mt-2"><strong>Jasa</strong></div>
                                @foreach($antrean->transaksi->jasaDetails as $detail)
                                    <div>{{ $detail->jumlah }}x {{ $detail->jasa->nama_jasa }} · Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</div>
                                @endforeach
                            @endif
                            @if($antrean->transaksi?->sparepartDetails->isNotEmpty())
                                <div class="mt-2"><strong>Sparepart</strong></div>
                                @foreach($antrean->transaksi->sparepartDetails as $detail)
                                    <div>{{ $detail->jumlah }}x {{ $detail->sparepart->nama_barang }} · Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</div>
                                @endforeach
                            @endif
                        </div>
                    </details>
                @empty
                    <p class="text-muted mb-0 mt-2">Belum ada riwayat servis.</p>
                @endforelse
            </section>
        @empty
            <p class="text-muted mb-0">Kendaraan yang kamu daftarkan akan muncul di sini.</p>
        @endforelse
    </div>
</div>
@endsection
