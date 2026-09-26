@extends('layouts.app')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Data mekanik</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.mekanik.store') }}" method="POST" class="form-row align-items-end mb-4">
            @csrf
            <div class="form-group col-md-5 mb-md-0">
                <label for="nama_mekanik_baru">Nama mekanik</label>
                <input id="nama_mekanik_baru" type="text" name="nama_karyawan" class="form-control" required maxlength="255">
            </div>
            <div class="form-group col-md-5 mb-md-0">
                <label for="no_hp_mekanik_baru">Nomor HP</label>
                <input id="no_hp_mekanik_baru" type="text" name="no_hp" class="form-control" maxlength="20">
            </div>
            <div class="form-group col-md-2 mb-0">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-plus mr-1"></i>Tambah</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="thead-light"><tr><th>Nama</th><th>Nomor HP</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($mekaniks as $mekanik)
                        <tr>
                            <td>
                                <form id="ubah-mekanik-{{ $mekanik->id }}" action="{{ route('admin.mekanik.update', $mekanik) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <input form="ubah-mekanik-{{ $mekanik->id }}" type="text" name="nama_karyawan" class="form-control form-control-sm" value="{{ $mekanik->nama_karyawan }}" required maxlength="255">
                            </td>
                            <td><input form="ubah-mekanik-{{ $mekanik->id }}" type="text" name="no_hp" class="form-control form-control-sm" value="{{ $mekanik->no_hp }}" maxlength="20"></td>
                            <td class="text-nowrap">
                                <button form="ubah-mekanik-{{ $mekanik->id }}" type="submit" class="btn btn-sm btn-primary" title="Simpan perubahan"><i class="fas fa-save"></i></button>
                                <form action="{{ route('admin.mekanik.destroy', $mekanik) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data mekanik ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus mekanik"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">Belum ada data mekanik.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-warning">
        <h6 class="m-0 font-weight-bold text-dark">Antrean dan mekanik</h6>
    </div>
    <div class="card-body">
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

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Plat Nomor</th>
                        <th>Pemilik</th>
                        <th>Keluhan</th>
                        <th>Riwayat Kendaraan</th>
                        <th>Pilih Mekanik</th>
                        <th>Ubah Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antreans as $item)
                        <tr>
                            <td>
                                <form id="update-antrean-{{ $item->id }}" action="{{ route('antrean.update', $item->id) }}" method="POST">
                                    @csrf
                                </form>
                                <strong>{{ $item->plat_nomor }}</strong>
                            </td>
                            <td>{{ $item->kendaraan->nama_pemilik ?? '-' }}</td>
                            <td>{{ $item->keluhan }}</td>
                            <td>
                                @php($riwayat = $item->kendaraan?->antrean?->where('id', '!=', $item->id)->sortByDesc('id')->take(3))
                                @forelse($riwayat ?? [] as $riwayatItem)
                                    <div class="small mb-1">
                                        <strong>{{ $riwayatItem->created_at?->format('d/m/Y') }}</strong>
                                        <span class="text-muted">({{ $riwayatItem->status }})</span><br>
                                        {{ $riwayatItem->keluhan }}
                                        <br><span class="text-muted">Mekanik: {{ $riwayatItem->mekanik->nama_karyawan ?? 'Belum ditugaskan' }}</span>
                                    </div>
                                @empty
                                    <span class="text-muted small">Belum ada riwayat</span>
                                @endforelse
                            </td>
                            <td>
                                <select form="update-antrean-{{ $item->id }}" name="id_mekanik" class="form-control form-control-sm">
                                    <option value="">-- Pilih Mekanik --</option>
                                    @foreach($mekaniks as $mekanik)
                                        <option value="{{ $mekanik->id }}" {{ $item->id_mekanik == $mekanik->id ? 'selected' : '' }}>
                                            {{ $mekanik->nama_karyawan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="update-antrean-{{ $item->id }}" name="status" class="form-control form-control-sm">
                                    <option value="Antre" {{ $item->status == 'Antre' ? 'selected' : '' }}>Antre</option>
                                    <option value="Sedang Dikerjakan" {{ $item->status == 'Sedang Dikerjakan' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                                    <option value="Selesai" {{ $item->status == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                                </select>
                            </td>
                            <td>
                                <button form="update-antrean-{{ $item->id }}" type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada antrean.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
