@extends('layouts.app')

@section('content')
<div class="section-header shadow-sm mb-4">
    <h1>Antrean Pengerjaan</h1>
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

<div class="card shadow-sm">
    <div class="card-header"><h4 class="mb-0">Tugas untuk {{ $mekanik->nama_karyawan }}</h4></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr><th>Kode / Kendaraan</th><th>Keluhan</th><th>Status</th><th>Jasa servis</th><th>Sparepart</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($antreans as $antrean)
                        <tr>
                            <td>
                                <strong>{{ $antrean->kode_antrean }}</strong><br>
                                {{ $antrean->plat_nomor }}<br>
                                <small class="text-muted">{{ $antrean->kendaraan->merk_tipe ?? '-' }}</small>
                            </td>
                            <td>{{ $antrean->keluhan }}</td>
                            <td><span class="badge badge-info">{{ $antrean->status }}</span></td>
                            <td>
                                @if($antrean->status === \App\Models\Antrean::STATUS_QUEUE)
                                    <span class="text-muted">Ambil antrean untuk mencatat jasa</span>
                                @else
                                    @foreach($jasas as $jasa)
                                        <label class="d-block mb-1">
                                            <input type="checkbox" name="jasa_ids[]" value="{{ $jasa->id }}" form="update-antrean-{{ $antrean->id }}" {{ $antrean->transaksi?->jasaDetails->contains('jasa_id', $jasa->id) ? 'checked' : '' }}>
                                            {{ $jasa->nama_jasa }} <small class="text-muted">Rp {{ number_format($jasa->harga, 0, ',', '.') }}</small>
                                        </label>
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($antrean->status === \App\Models\Antrean::STATUS_QUEUE)
                                    <span class="text-muted">Ambil antrean untuk mencatat sparepart</span>
                                @else
                                    @foreach($spareparts as $sparepart)
                                        @php($detailSparepart = $antrean->transaksi?->sparepartDetails->firstWhere('sparepart_id', $sparepart->id))
                                        <label class="d-flex align-items-center mb-2">
                                            <input type="checkbox" name="sparepart_ids[]" value="{{ $sparepart->id }}" form="update-antrean-{{ $antrean->id }}" {{ $detailSparepart ? 'checked' : '' }}>
                                            <span class="ml-2 mr-2">{{ $sparepart->nama_barang }} <small class="text-muted">({{ $sparepart->stok }} tersedia)</small></span>
                                            <input type="number" name="sparepart_quantities[{{ $sparepart->id }}]" value="{{ $detailSparepart?->jumlah ?? 1 }}" min="1" max="{{ $sparepart->stok }}" class="form-control form-control-sm" style="max-width: 76px;" form="update-antrean-{{ $antrean->id }}" aria-label="Jumlah {{ $sparepart->nama_barang }}">
                                        </label>
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-nowrap">
                                @if($antrean->status === \App\Models\Antrean::STATUS_QUEUE)
                                    <form action="{{ route('mekanik.antrean.take', $antrean) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-hand-paper mr-1"></i>Mulai pengerjaan</button>
                                    </form>
                                @else
                                    <form id="update-antrean-{{ $antrean->id }}" action="{{ route('mekanik.antrean.update', $antrean) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" name="status" value="{{ \App\Models\Antrean::STATUS_WORKING }}" class="btn btn-sm btn-outline-primary mb-1"><i class="fas fa-save mr-1"></i>Simpan rincian</button>
                                        <button type="submit" name="status" value="{{ \App\Models\Antrean::STATUS_FINISHED }}" class="btn btn-sm btn-success" onclick="return confirm('Tandai pengerjaan selesai?')"><i class="fas fa-check mr-1"></i>Selesai pengerjaan</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada antrean yang menunggu atau sedang dikerjakan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection