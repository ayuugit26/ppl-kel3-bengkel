@extends('layouts.app')

@section('content')
<style>
    @media print {
        @page { margin: 8mm; }
        body * { visibility: hidden !important; }
        #struk-cetak, #struk-cetak * { visibility: visible !important; }
        #struk-cetak { position: absolute; inset: 0 auto auto 0; width: 100%; border: 0 !important; box-shadow: none !important; }
        .no-print { display: none !important; }
    }
</style>

<div class="container" id="struk-cetak">
    <div class="card mx-auto" style="max-width: 520px;">
        <div class="card-body">
            <div class="text-center border-bottom pb-3 mb-3">
                <h3 class="mb-1">Sentosa Motor</h3>
                <div class="text-muted">Struk Pembayaran Servis</div>
                <div class="small">{{ $transaksi->updated_at->format('d/m/Y H:i') }}</div>
                <div class="small">TRX-{{ str_pad((string) $transaksi->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>

            <div class="mb-3">
                <div>Nama pelanggan: <strong>{{ $transaksi->antrean->kendaraan->nama_pemilik ?? '-' }}</strong></div>
                <div>Plat nomor: <strong>{{ $transaksi->antrean->plat_nomor ?? '-' }}</strong></div>
                <div>Kasir: {{ $transaksi->kasir->nama_karyawan ?? '-' }}</div>
            </div>

            <h6>Rincian Jasa</h6>
            @forelse($transaksi->jasaDetails as $detail)
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $detail->jasa->nama_jasa ?? 'Jasa dihapus' }} x{{ $detail->jumlah }}</span>
                    <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="small text-muted mb-2">Tidak ada jasa.</div>
            @endforelse

            <h6 class="mt-3">Rincian Sparepart</h6>
            @forelse($transaksi->sparepartDetails as $detail)
                <div class="d-flex justify-content-between small mb-1">
                    <span>{{ $detail->sparepart->nama_barang ?? 'Sparepart dihapus' }} x{{ $detail->jumlah }}</span>
                    <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                </div>
            @empty
                <div class="small text-muted mb-2">Tidak ada sparepart.</div>
            @endforelse

            <hr>
            <div class="d-flex justify-content-between"><strong>Total</strong><strong>Rp {{ number_format($transaksi->total_biaya, 0, ',', '.') }}</strong></div>
            <div class="d-flex justify-content-between"><span>Uang diterima</span><span>Rp {{ number_format($transaksi->uang_dibayar ?? 0, 0, ',', '.') }}</span></div>
            <div class="d-flex justify-content-between"><span>Kembalian</span><span>Rp {{ number_format(max(0, $transaksi->uang_dibayar - $transaksi->total_biaya), 0, ',', '.') }}</span></div>
            <div class="text-center border-top mt-3 pt-3 small">Terima kasih telah mempercayakan servis kepada kami.</div>
        </div>
    </div>

    <div class="text-center mt-3 no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fas fa-print mr-1"></i>Cetak Struk</button>
        <a href="{{ route('bengkel.kasir') }}" class="btn btn-light ml-1"><i class="fas fa-arrow-left mr-1"></i>Kembali ke Kasir</a>
    </div>
</div>
@endsection
