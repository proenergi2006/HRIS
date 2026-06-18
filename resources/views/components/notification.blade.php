{{-- Validation errors — tetap inline agar user tahu field mana yang salah --}}
@if ($errors->any())
<div class="alert alert-danger alert-dismissible mb-4" role="alert">
    <button type="button" class="close" data-dismiss="alert" aria-label="Tutup">
        <span aria-hidden="true">&times;</span>
    </button>
    <div class="font-weight-bold mb-1">Mohon perbaiki kesalahan berikut:</div>
    <ul class="mb-0 pl-4">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
{{-- Flash messages (status/error/warning/info) dihandle via toast di layout --}}
