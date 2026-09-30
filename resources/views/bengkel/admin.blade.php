@extends('layouts.app')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 bg-dark text-white">
        <h6 class="m-0 font-weight-bold">Pengguna</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST" class="form-row align-items-end mb-4">
            @csrf
            <div class="form-group col-md-3"><label for="user_name">Nama</label><input id="user_name" name="name" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-3"><label for="user_email">Email</label><input id="user_email" type="email" name="email" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-2"><label for="user_password">Kata sandi</label><input id="user_password" type="password" name="password" class="form-control" minlength="8" required></div>
            <div class="form-group col-md-2"><label for="user_role">Role</label><select id="user_role" name="role" class="form-control" required><option value="admin">Admin</option><option value="mekanik">Mekanik</option><option value="pelanggan">Pelanggan</option></select></div>
            <div class="form-group col-md-2"><label for="user_phone">Nomor HP</label><input id="user_phone" name="no_hp" class="form-control" maxlength="20"></div>
            <div class="form-group col-12 mb-0"><button type="submit" class="btn btn-dark"><i class="fas fa-user-plus mr-1"></i>Tambah pengguna</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="thead-light"><tr><th>Nama</th><th>Email</th><th>Role</th><th>Kata sandi baru</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <form id="update-user-{{ $user->id }}" action="{{ route('admin.users.update', $user) }}" method="POST">@csrf @method('PUT')</form>
                                <input form="update-user-{{ $user->id }}" name="name" class="form-control form-control-sm" value="{{ $user->name }}" maxlength="255" required>
                            </td>
                            <td><input form="update-user-{{ $user->id }}" type="email" name="email" class="form-control form-control-sm" value="{{ $user->email }}" required></td>
                            <td><span class="badge badge-secondary">{{ ucfirst($user->role->value) }}</span></td>
                            <td><input form="update-user-{{ $user->id }}" type="password" name="password" class="form-control form-control-sm" minlength="8" autocomplete="new-password" placeholder="Biarkan kosong"></td>
                            <td class="text-nowrap">
                                <button form="update-user-{{ $user->id }}" type="submit" class="btn btn-sm btn-primary" title="Simpan pengguna"><i class="fas fa-save"></i></button>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus pengguna"><i class="fas fa-trash"></i></button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada akun pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Data mekanik</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.mekanik.store') }}" method="POST" class="form-row align-items-end mb-4">
            @csrf
            <div class="form-group col-md-3 mb-md-0">
                <label for="nama_mekanik_baru">Nama mekanik</label>
                <input id="nama_mekanik_baru" type="text" name="nama_karyawan" class="form-control" required maxlength="255">
            </div>
            <div class="form-group col-md-3 mb-md-0">
                <label for="email_mekanik_baru">Email login</label>
                <input id="email_mekanik_baru" type="email" name="email" class="form-control" required maxlength="255">
            </div>
            <div class="form-group col-md-2 mb-md-0">
                <label for="password_mekanik_baru">Kata sandi</label>
                <input id="password_mekanik_baru" type="password" name="password" class="form-control" required minlength="8">
            </div>
            <div class="form-group col-md-2 mb-md-0">
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
    <div class="card-header py-3 bg-primary text-white">
        <h6 class="m-0 font-weight-bold">Penerimaan servis</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('antrean.store') }}" method="POST" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-2"><label for="admin_pelanggan">Akun Pelanggan</label><select id="admin_pelanggan" name="customer_user_id" class="form-control"><option value="">Tanpa akun</option>@foreach($customers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }}</option>@endforeach</select></div>
            <div class="form-group col-md-2"><label for="admin_plat">Plat nomor</label><input id="admin_plat" name="plat_nomor" class="form-control" maxlength="20" required></div>
            <div class="form-group col-md-2"><label for="admin_pemilik">Nama pemilik</label><input id="admin_pemilik" name="nama_pemilik" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-2"><label for="admin_jenis">Jenis kendaraan</label><select id="admin_jenis" name="jenis_kendaraan" class="form-control" required><option value="Motor">Motor</option><option value="Mobil">Mobil</option></select></div>
            <div class="form-group col-md-2"><label for="admin_tipe">Merk dan tipe</label><input id="admin_tipe" name="merk_tipe" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-3"><label for="admin_keluhan">Keluhan awal</label><input id="admin_keluhan" name="keluhan" class="form-control" required></div>
            <div class="form-group col-md-1"><button type="submit" class="btn btn-primary btn-block" title="Buat antrean"><i class="fas fa-plus"></i></button></div>
        </form>
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
                        <th>Kode antrean</th>
                        <th>Plat Nomor</th>
                        <th>Pemilik</th>
                        <th>Keluhan</th>
                        <th>Riwayat Kendaraan</th>
                        <th>Pilih Mekanik</th>
                        <th>Status servis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($antreans as $item)
                        <tr>
                            <td><strong>{{ $item->kode_antrean }}</strong></td>
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
                            <td><span class="badge badge-info">{{ $item->status }}</span></td>
                            <td>
                                <button form="update-antrean-{{ $item->id }}" type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i> Simpan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada antrean.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
