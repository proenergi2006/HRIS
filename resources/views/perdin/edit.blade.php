@extends('layouts.grain')
@section('title', 'Edit Permohonan Perjalanan Dinas')

@section('content')
@include('components.notification')
<div class="mb-3">
  <a href="{{ route('perdin.show', $perdin) }}" class="btn btn-outline-secondary btn-sm">
    <i class="gd-angle-left mr-1"></i> Kembali
  </a>
</div>

<form method="POST" action="{{ route('perdin.update', $perdin) }}" id="perdin-form">
@csrf
@method('PUT')
@include('perdin._form', ['editing' => true])
</form>
@endsection

@section('scripts')
@include('perdin._form_scripts')
@endsection
