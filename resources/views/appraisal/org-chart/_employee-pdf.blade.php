{{-- Recursive: render 1 karyawan + (kalau ada) bawahan langsungnya, versi DomPDF. --}}
@php $reports = $reportsByManager->get($employee->id, collect()); @endphp
<span class="emp-chip">{{ $employee->name }}</span>
@if($reports->isNotEmpty())
  <div class="emp-reports">
    @foreach($reports as $child)
      @include('appraisal.org-chart._employee-pdf', ['employee' => $child, 'reportsByManager' => $reportsByManager])
    @endforeach
  </div>
@endif
