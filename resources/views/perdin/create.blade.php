@extends('layouts.grain')
@section('title', 'Buat Permohonan Perjalanan Dinas')

@section('content')
@include('components.notification')
<div class="mb-3">
  <a href="{{ route('perdin.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<form method="POST" action="{{ route('perdin.store') }}" id="perdin-form">
@csrf
@include('perdin._form')
</form>
@endsection

@section('scripts')
@include('perdin._form_scripts')
@endsection
