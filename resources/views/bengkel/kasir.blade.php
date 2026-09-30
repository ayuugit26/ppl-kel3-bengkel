@extends('layouts.app')

@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white"><h6 class="mb-0 font-weight-bold">Penerimaan servis</h6></div>
    <div class="card-body">
        <form action="{{ route('antrean.store') }}" method="POST" class="form-row align-items-end">
            @csrf
            <div class="form-group col-md-2"><label for="kasir_plat">Plat nomor</label><input id="kasir_plat" name="plat_nomor" class="form-control" maxlength="20" required></div>
            <div class="form-group col-md-2"><label for="kasir_pemilik">Nama pemilik</label><input id="kasir_pemilik" name="nama_pemilik" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-2"><label for="kasir_jenis">Jenis</label><select id="kasir_jenis" name="jenis_kendaraan" class="form-control" required><option value="Motor">Motor</option><option value="Mobil">Mobil</option></select></div>
            <div class="form-group col-md-2"><label for="kasir_tipe">Merk dan tipe</label><input id="kasir_tipe" name="merk_tipe" class="form-control" maxlength="255" required></div>
            <div class="form-group col-md-3"><label for="kasir_keluhan">Keluhan awal</label><input id="kasir_keluhan" name="keluhan" class="form-control" required></div>
            <div class="form-group col-md-1"><button type="submit" class="btn btn-primary btn-block" title="Buat antrean"><i class="fas fa-plus"></i></button></div>
        </form>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 bg-success text-white">
        <h6 class="m-0 font-weight-bold">Pembayaran servis</h6>
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
                        <th>ID Transaksi</th>
                        <th>Plat Nomor</th>
                        <th>Status Servis</th>
                        <th>Jasa tercatat mekanik</th>
                        <th>Sparepart tercatat mekanik</th>
                        <th>Total Biaya</th>
                        <th>Petugas Kasir</th>
                        <th>Metode / Uang Diterima</th>
                        <th>Status Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        <tr>
                            <td>
                                <form id="bayar-transaksi-{{ $transaksi->id }}" action="{{ route('kasir.bayar', $transaksi) }}" method="POST">
                                    @csrf
                                </form>
                                {{ $transaksi->antrean->kode_antrean ?? 'TRX-'.str_pad((string) $transaksi->id, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td><strong>{{ $transaksi->antrean->plat_nomor ?? '-' }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $transaksi->antrean->status ?? '-' }}</span></td>
                            <td>
                                @forelse($transaksi->jasaDetails as $detail)
                                    <div>{{ $detail->jumlah }}x {{ $detail->jasa->nama_jasa }} <small class="text-muted">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</small></div>
                                @empty
                                    <span class="text-muted">Belum dicatat</span>
                                @endforelse
                            </td>
                            <td>
                                @forelse($transaksi->sparepartDetails as $detail)
                                    <div>{{ $detail->jumlah }}x {{ $detail->sparepart->nama_barang }} <small class="text-muted">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</small></div>
                                @empty
                                    <span class="text-muted">Belum dicatat</span>
                                @endforelse
                            </td>
                            <td>
                                <span class="font-weight-bold">Rp {{ number_format($transaksi->jasaDetails->sum('subtotal') + $transaksi->sparepartDetails->sum('subtotal'), 0, ',', '.') }}</span>
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran === 'Lunas')
                                    {{ $transaksi->kasir->nama_karyawan ?? auth()->user()->name }}
                                @else
                                    {{ auth()->user()->name }}
                                @endif
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran === 'Lunas')
                                    {{ $transaksi->metode_pembayaran }}
                                @else
                                    <select form="bayar-transaksi-{{ $transaksi->id }}" name="metode_pembayaran" class="form-control form-control-sm mb-1" required>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Non-Tunai">Non-Tunai</option>
                                    </select>
                                    <input form="bayar-transaksi-{{ $transaksi->id }}" type="number" name="uang_dibayar" class="form-control form-control-sm" placeholder="Uang diterima" min="0" step="0.01">
                                @endif
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran === 'Lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-warning">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran === 'Lunas')
                                    <a href="{{ route('kasir.struk', $transaksi) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-print"></i> Cetak Struk
                                    </a>
                                @else
                                    <button form="bayar-transaksi-{{ $transaksi->id }}" type="submit" class="btn btn-sm btn-success" {{ $transaksi->jasaDetails->isEmpty() && $transaksi->sparepartDetails->isEmpty() ? 'disabled' : '' }}>
                                        <i class="fas fa-check"></i> Proses Bayar
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Belum ada antrean yang selesai dikerjakan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
