@extends('layouts.app')

@section('content')
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
                        <th>Jasa</th>
                        <th>Sparepart</th>
                        <th>Petugas Kasir</th>
                        <th>Total Biaya (Rp)</th>
                        <th>Status Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        <tr>
                            <td>
                                <form id="bayar-transaksi-{{ $transaksi->id }}" action="{{ route('kasir.bayar', $transaksi->id) }}" method="POST">
                                    @csrf
                                </form>
                                TRX-00{{ $transaksi->id }}
                            </td>
                            <td><strong>{{ $transaksi->antrean->plat_nomor ?? '-' }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $transaksi->antrean->status ?? '-' }}</span></td>
                            <td>
                                <select form="bayar-transaksi-{{ $transaksi->id }}" name="jasa_ids[]" class="form-control form-control-sm" multiple size="3">
                                    @foreach($jasas as $jasa)
                                        <option value="{{ $jasa->id }}" {{ $transaksi->jasaDetails->contains('jasa_id', $jasa->id) ? 'selected' : '' }}>
                                            {{ $jasa->nama_jasa }} (Rp {{ number_format($jasa->harga, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="bayar-transaksi-{{ $transaksi->id }}" name="sparepart_ids[]" class="form-control form-control-sm" multiple size="3">
                                    @foreach($spareparts as $sparepart)
                                        <option value="{{ $sparepart->id }}" {{ $transaksi->sparepartDetails->contains('sparepart_id', $sparepart->id) ? 'selected' : '' }}>
                                            {{ $sparepart->nama_barang }} (Rp {{ number_format($sparepart->harga, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="bayar-transaksi-{{ $transaksi->id }}" name="id_kasir" class="form-control form-control-sm" required>
                                    <option value="">-- Pilih Kasir --</option>
                                    @foreach($kasirs as $kasir)
                                        <option value="{{ $kasir->id }}" {{ $transaksi->id_kasir == $kasir->id ? 'selected' : '' }}>
                                            {{ $kasir->nama_karyawan }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input form="bayar-transaksi-{{ $transaksi->id }}" type="number" name="total_biaya" class="form-control form-control-sm" value="{{ $transaksi->total_biaya }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran == 'Lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                <button form="bayar-transaksi-{{ $transaksi->id }}" type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> Proses Bayar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">Belum ada transaksi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
