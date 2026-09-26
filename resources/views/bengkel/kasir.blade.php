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
                        <th>Total Biaya</th>
                        <th>Uang Diterima</th>
                        <th>Status Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $transaksi)
                        <tr>
                            <td>
                                <form id="bayar-transaksi-{{ $transaksi->id }}" action="{{ route('kasir.bayar', $transaksi->id) }}" method="POST" class="form-bayar">
                                    @csrf
                                </form>
                                TRX-00{{ $transaksi->id }}
                            </td>
                            <td><strong>{{ $transaksi->antrean->plat_nomor ?? '-' }}</strong></td>
                            <td><span class="badge badge-secondary">{{ $transaksi->antrean->status ?? '-' }}</span></td>
                            <td>
                                <select form="bayar-transaksi-{{ $transaksi->id }}" name="jasa_ids[]" class="form-control form-control-sm" multiple size="3" data-harga-select>
                                    @foreach($jasas as $jasa)
                                        <option value="{{ $jasa->id }}" data-harga="{{ $jasa->harga }}" {{ $transaksi->jasaDetails->contains('jasa_id', $jasa->id) ? 'selected' : '' }}>
                                            {{ $jasa->nama_jasa }} (Rp {{ number_format($jasa->harga, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select form="bayar-transaksi-{{ $transaksi->id }}" name="sparepart_ids[]" class="form-control form-control-sm" multiple size="3" data-harga-select>
                                    @foreach($spareparts as $sparepart)
                                        <option value="{{ $sparepart->id }}" data-harga="{{ $sparepart->harga }}" {{ $transaksi->sparepartDetails->contains('sparepart_id', $sparepart->id) ? 'selected' : '' }}>
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
                                <span id="total-bayar-transaksi-{{ $transaksi->id }}" class="font-weight-bold" data-total-display="{{ $transaksi->id }}">Rp {{ number_format($transaksi->jasaDetails->sum('subtotal') + $transaksi->sparepartDetails->sum('subtotal'), 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <input form="bayar-transaksi-{{ $transaksi->id }}" type="number" name="uang_dibayar" class="form-control form-control-sm" value="{{ $transaksi->uang_dibayar }}" min="0" step="0.01" required>
                            </td>
                            <td>
                                @if($transaksi->status_pembayaran == 'Lunas')
                                    <span class="badge badge-success">Lunas</span>
                                @else
                                    <span class="badge badge-danger">Belum Bayar</span>
                                @endif
                            </td>
                            <td>
                                <button form="bayar-transaksi-{{ $transaksi->id }}" type="submit" class="btn btn-sm btn-success mb-1">
                                    <i class="fas fa-check"></i> Proses Bayar
                                </button>
                                @if($transaksi->status_pembayaran === 'Lunas')
                                    <a href="{{ route('kasir.struk', $transaksi) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-print"></i> Cetak Struk
                                    </a>
                                @endif
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

<script>
    document.querySelectorAll('.form-bayar').forEach((form) => {
        const pilihanHarga = document.querySelectorAll(`select[form="${form.id}"][data-harga-select]`);
        const totalTampilan = document.querySelector(`[data-total-display="${form.id.replace('bayar-transaksi-', '')}"]`);
        const uangDiterima = document.querySelector(`input[form="${form.id}"][name="uang_dibayar"]`);

        const hitungTotal = () => {
            const total = [...pilihanHarga].reduce((jumlah, pilihan) => {
                return jumlah + [...pilihan.selectedOptions].reduce((subtotal, opsi) => {
                    return subtotal + Number(opsi.dataset.harga || 0);
                }, 0);
            }, 0);

            totalTampilan.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
            uangDiterima.min = total;
        };

        pilihanHarga.forEach((pilihan) => pilihan.addEventListener('change', hitungTotal));
        hitungTotal();
    });
</script>
@endsection
