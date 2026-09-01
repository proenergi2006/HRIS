@php $active = $active ?? ''; @endphp
<ul class="nav nav-pills my-4">
  <li class="nav-item"><a class="nav-link {{ $active === 'benchmarks' ? 'active' : '' }}" href="{{ route('hr.compensation.benchmarks') }}">Benchmark Eksternal</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'comparison' ? 'active' : '' }}" href="{{ route('hr.compensation.comparison') }}">Perbandingan Pasar</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'grades' ? 'active' : '' }}" href="{{ route('hr.compensation.grades') }}">Struktur Gaji Internal</a></li>
  <li class="nav-item"><a class="nav-link {{ $active === 'grade-position' ? 'active' : '' }}" href="{{ route('hr.compensation.grade-position') }}">Posisi dalam Band</a></li>
</ul>
