@extends('layouts.grain')
@section('title', 'QR Code Whistleblower')

@section('content')
<div class="card mb-3 mb-md-4">
    <div class="card-body">
        <nav class="d-none d-md-block" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('whistleblower.admin.index') }}">Whistleblower</a></li>
                <li class="breadcrumb-item active">QR Code</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="h3 mb-1">QR Code Pengaduan</div>
                <p class="text-muted mb-4">Scan untuk membuka formulir pengaduan whistleblower.</p>

                {{-- QR Code --}}
                <div class="d-inline-block p-4 border rounded bg-white shadow-sm mb-4" id="qr-container">
                    {!! $qr !!}
                    <div class="mt-3 font-weight-bold" style="font-size:.85rem;color:#1a3a5c;letter-spacing:.5px;">
                        SALURAN PENGADUAN<br>PT. PRO ENERGI
                    </div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2">URL form pengaduan:</small>
                    <code class="d-block p-2 bg-light border rounded">{{ $url }}</code>
                </div>

                <button onclick="window.print()" class="btn btn-primary mr-2">
                    <i class="gd-printer icon-text"></i> Cetak QR Code
                </button>
                <a href="{{ route('whistleblower.admin.index') }}" class="btn btn-outline-secondary">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
@media print {
    body * { visibility: hidden !important; }
    #qr-container, #qr-container * { visibility: visible !important; }
    #qr-container {
        position: fixed;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        border: 2px solid #000 !important;
        padding: 24px !important;
    }
}
</style>
@endsection
