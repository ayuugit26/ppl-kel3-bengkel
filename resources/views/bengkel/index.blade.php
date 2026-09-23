@extends('layouts.app')

@section('content')
<div class="section-header shadow-sm mb-4">
    <h1>Antrean Servis</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('bengkel.index') }}" method="GET" class="form-row align-items-end">
            <div class="form-group col-md-8 mb-md-0">
                <label for="plat_nomor_cari">Cek status kendaraan</label>
                <input id="plat_nomor_cari" type="text" name="plat_nomor" class="form-control" value="{{ $platNomor }}" placeholder="Masukkan nomor plat" maxlength="20">
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
                    {{ $antreans->count() }}
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
                    {{ $antreans->where('status', 'Sedang Dikerjakan')->count() }}
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
                    {{ $antreans->where('status', 'Selesai')->count() }}
                </div>
            </div>
        </div>
    </div>                  
</div>

<div class="row">
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
                        <select name="jenis_kendaraan" class="form-control">
                            <option value="Motor" {{ old('jenis_kendaraan', 'Motor') === 'Motor' ? 'selected' : '' }}>Motor</option>
                            <option value="Mobil" {{ old('jenis_kendaraan') === 'Mobil' ? 'selected' : '' }}>Mobil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Merek / tipe</label>
                        <input type="text" name="merk_tipe" class="form-control" value="{{ old('merk_tipe') }}" placeholder="Vario 150 / Avanza" required>
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

    <div class="col-lg-8 col-md-12">
        <div class="card card-dark shadow-sm">
            <div class="card-header">
                <h4><i class="fas fa-list mr-2"></i>Antrean hari ini</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped text-center mb-0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Plat Nomor</th>
                                <th>Kendaraan & Pemilik</th>
                                <th>Keluhan</th>
                                <th>Mekanik</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($antreans as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><span class="badge badge-dark p-2">{{ $item->plat_nomor }}</span></td>
                                <td>{{ $item->kendaraan->merk_tipe ?? '-' }} <br><small class="text-muted">({{ $item->kendaraan->nama_pemilik ?? '-' }})</small></td>
                                <td>{{ $item->keluhan }}</td>
                                <td><span class="badge badge-light border">{{ $item->mekanik->nama_karyawan ?? 'Belum Ada' }}</span></td>
                                <td>
                                    @if($item->status == 'Antre')
                                        <span class="badge badge-warning">Antre</span>
                                    @elseif($item->status == 'Sedang Dikerjakan')
                                        <span class="badge badge-info">Sedang Dikerjakan</span>
                                    @else
                                        <span class="badge badge-success">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Belum ada antrean hari ini.</td>
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