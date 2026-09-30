@extends('layouts.app')

@section('content')
<div class="section-header shadow-sm mb-4">
    <h1>Pelacakan Servis</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('bengkel.index') }}" method="GET" class="form-row align-items-end">
            <div class="form-group col-md-8 mb-md-0">
                <label for="plat_nomor_cari">Nomor plat atau kode antrean</label>
                <input id="plat_nomor_cari" type="text" name="q" class="form-control" value="{{ $search }}" placeholder="Contoh: B 1234 ABC atau SRV-260930-00001" maxlength="30" required>
            </div>
            <div class="form-group col-md-4 mb-0">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-search mr-1"></i> Cari
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-primary">
                <i class="fas fa-motorcycle"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                            <h4>Total antrean</h4>
                </div>
                <div class="card-body">
                    {{ $queueCount }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-warning">
                <i class="fas fa-tools"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                            <h4>Sedang dikerjakan</h4>
                </div>
                <div class="card-body">
                    {{ $workingCount }}
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
        <div class="card card-statistic-1 shadow-sm">
            <div class="card-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="card-wrap">
                <div class="card-header">
                            <h4>Selesai</h4>
                </div>
                <div class="card-body">
                    {{ $finishedCount }}
                </div>
            </div>
        </div>
    </div>                  
</div>

<div class="row">
    @if(auth()->check() && auth()->user()->role->value === 'admin')
    <div class="col-lg-4 col-md-12">
        <div class="card card-primary shadow-sm">
            <div class="card-header">
                <h4><i class="fas fa-plus-circle mr-2"></i>Daftar servis</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible show fade">
                        <div class="alert-body">
                            <button class="close" data-dismiss="alert"><span>&times;</span></button>
                            {{ session('success') }}
                        </div>
                    </div>
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

                <form action="{{ route('antrean.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Nomor plat</label>
                        <input type="text" name="plat_nomor" class="form-control" value="{{ old('plat_nomor') }}" placeholder="B 1234 ABC" required>
                    </div>
                    <div class="form-group">
                        <label>Nama pemilik</label>
                        <input type="text" name="nama_pemilik" class="form-control" value="{{ old('nama_pemilik') }}" placeholder="Nama Pelanggan" required>
                    </div>
                    <div class="form-group">
                        <label>Jenis kendaraan</label>
                        <select name="jenis_kendaraan" class="form-control" required>
                            <option value="Motor" {{ old('jenis_kendaraan') === 'Motor' ? 'selected' : '' }}>Motor</option>
                            <option value="Mobil" {{ old('jenis_kendaraan') === 'Mobil' ? 'selected' : '' }}>Mobil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Merk dan tipe</label>
                        <input type="text" name="merk_tipe" class="form-control" value="{{ old('merk_tipe') }}" placeholder="Contoh: Vario 150" required>
                    </div>
                    <div class="form-group">
                        <label>Keluhan</label>
                        <textarea name="keluhan" class="form-control" rows="3" required>{{ old('keluhan') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block font-weight-bold">
                        <i class="fas fa-paper-plane mr-1"></i> Tambah antrean
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    <div class="{{ auth()->check() && auth()->user()->role->value === 'admin' ? 'col-lg-8' : 'col-12' }} col-md-12">
        <div class="card card-dark shadow-sm">
            <div class="card-header">
                <h4><i class="fas fa-list mr-2"></i>Riwayat Servis Kendaraan</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped text-center mb-0">
                        <thead>
                            <tr>
                                @if($canViewHistory)
                                    <th>No</th>
                                    <th>Kode antrean</th>
                                @endif
                                <th>Plat Nomor</th>
                                @if($canViewHistory)
                                    <th>Kendaraan & Pemilik</th>
                                    <th>Keluhan</th>
                                    <th>Mekanik</th>
                                @endif
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($antreans as $index => $item)
                            <tr>
                                @if($canViewHistory)
                                    <td>{{ $index + 1 }}</td>
                                    <td><span class="badge badge-dark p-2">{{ $item->kode_antrean }}</span></td>
                                @endif
                                <td><span class="badge badge-dark p-2">{{ $item->plat_nomor }}</span></td>
                                @if($canViewHistory)
                                    <td>{{ $item->kendaraan->merk_tipe ?? '-' }} <br><small class="text-muted">({{ $item->kendaraan->nama_pemilik ?? '-' }})</small></td>
                                    <td>{{ $item->keluhan }}</td>
                                    <td><span class="badge badge-light border">{{ $item->mekanik->nama_karyawan ?? 'Belum Ada' }}</span></td>
                                @endif
                                <td>
                                    @php($tahap = [
                                        'Antre' => 0,
                                        'Sedang Dikerjakan' => 1,
                                        'Selesai' => 2,
                                        'Lunas' => 3,
                                    ][$item->status] ?? 0)
                                    <div class="font-weight-bold mb-1">{{ $item->status }}</div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $item->status === 'Lunas' ? 'bg-success' : 'bg-primary' }}" role="progressbar" style="width: {{ (($tahap + 1) / 4) * 100 }}%" aria-valuenow="{{ $tahap + 1 }}" aria-valuemin="0" aria-valuemax="4"></div>
                                    </div>
                                    @if($canViewHistory)
                                        <small class="text-muted">Mekanik: {{ $item->mekanik->nama_karyawan ?? 'Menunggu penugasan' }}</small>
                                    @endif
                                    @if($canViewHistory && ($item->transaksi?->jasaDetails->isNotEmpty() || $item->transaksi?->sparepartDetails->isNotEmpty()))
                                        <details class="mt-2 text-left">
                                            <summary>Rincian servis</summary>
                                            @foreach($item->transaksi->jasaDetails as $detail)
                                                <div>{{ $detail->jumlah }}x {{ $detail->jasa->nama_jasa }}</div>
                                            @endforeach
                                            @foreach($item->transaksi->sparepartDetails as $detail)
                                                <div>{{ $detail->jumlah }}x {{ $detail->sparepart->nama_barang }}</div>
                                            @endforeach
                                        </details>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    @if($search === '')
                                        Masukkan nomor plat atau kode antrean untuk melihat status servis.
                                    @else
                                        Tidak ditemukan riwayat servis untuk pencarian tersebut.
                                    @endif
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection