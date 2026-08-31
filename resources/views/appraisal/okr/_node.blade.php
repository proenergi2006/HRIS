@php $progress = $o->derivedProgress(); @endphp
<div class="card mb-3" style="margin-left:{{ $depth * 2 }}rem">
  <div class="card-body py-3">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <span class="font-weight-bold">{{ $o->title }}</span>
        <span class="badge badge-{{ \App\Models\Appraisal\CompanyObjective::$statusBadges[$o->status] ?? 'secondary' }} ml-1">
          {{ \App\Models\Appraisal\CompanyObjective::$statusLabels[$o->status] ?? $o->status }}
        </span>
        <div class="small text-muted mt-1">
          {{ $o->periodLabel() }}
          @if($o->department) &middot; {{ $o->department->name }} @endif
          @if($o->owner) &middot; PIC: {{ $o->owner->name }} @endif
        </div>
        @if($o->description)<p class="small mb-0 mt-1">{{ $o->description }}</p>@endif
      </div>
      <div class="text-right" style="min-width:110px">
        @if($progress !== null)
          <div class="small font-weight-bold">{{ $progress }}%</div>
          <div class="progress" style="height:6px;width:100px">
            <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : 'bg-primary' }}" style="width:{{ min(100,$progress) }}%"></div>
          </div>
        @else
          <div class="small text-muted">Belum ada KPI tertaut</div>
        @endif
        <div class="mt-1">
          <a href="{{ route('appraisal.okr.edit', $o) }}" class="btn btn-xs btn-outline-warning"><i class="gd-pencil"></i></a>
          <a href="#" class="btn btn-xs btn-outline-danger" data-confirm="Hapus sasaran {{ $o->title }}? (turunannya juga akan lepas dari induk)" data-confirm-title="Hapus Sasaran" data-form="okr-del-{{ $o->id }}"><i class="gd-trash"></i></a>
          <form id="okr-del-{{ $o->id }}" method="POST" action="{{ route('appraisal.okr.destroy', $o) }}" class="d-none">@csrf @method('DELETE')</form>
        </div>
      </div>
    </div>
  </div>
</div>
@foreach($o->children as $child)
  @include('appraisal.okr._node', ['o' => $child, 'depth' => $depth + 1])
@endforeach
