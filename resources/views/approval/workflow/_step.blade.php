@php
    $cond = $s ? ($s->conditions[0] ?? null) : null;
@endphp
<div class="wf-step border rounded p-3 mb-3 bg-light">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="badge badge-dark">Step {{ is_numeric($i) ? $i + 1 : '' }}</span>
    <button type="button" class="btn btn-xs btn-outline-danger" onclick="removeStep(this)"><i class="gd-trash"></i></button>
  </div>
  <div class="form-row">
    <div class="form-group col-md-5 mb-2">
      <label class="small mb-1">Tipe Approver</label>
      <select name="steps[{{ $i }}][approver_type]" class="form-control form-control-sm" onchange="toggleApproverRef(this)" required>
        @foreach($approverTypes as $val => $label)
          <option value="{{ $val }}" @selected($s && $s->approver_type === $val)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
    <div class="form-group col-md-5 mb-2 ref-position" style="display:{{ $s && $s->approver_type === 'specific_position' ? '' : 'none' }}">
      <label class="small mb-1">Jabatan</label>
      <select name="steps[{{ $i }}][approver_position_id]" class="form-control form-control-sm">
        <option value="">—</option>
        @foreach($positions as $p)<option value="{{ $p->id }}" @selected($s && $s->approver_position_id == $p->id)>{{ $p->name }}</option>@endforeach
      </select>
    </div>
    <div class="form-group col-md-5 mb-2 ref-role" style="display:{{ $s && $s->approver_type === 'specific_role' ? '' : 'none' }}">
      <label class="small mb-1">Role</label>
      <select name="steps[{{ $i }}][approver_role]" class="form-control form-control-sm">
        <option value="">—</option>
        @foreach($roles as $r)<option value="{{ $r }}" @selected($s && $s->approver_role === $r)>{{ $r }}</option>@endforeach
      </select>
    </div>
    <div class="form-group col-md-2 mb-2">
      <label class="small mb-1">Eskalasi (hari)</label>
      <input type="number" name="steps[{{ $i }}][escalate_after_days]" class="form-control form-control-sm" min="1" max="90" value="{{ $s->escalate_after_days ?? '' }}">
    </div>
  </div>
  <div class="form-row">
    <div class="form-group col-md-4 mb-0">
      <label class="small mb-1">Kondisi — field</label>
      <input type="text" name="steps[{{ $i }}][condition_field]" class="form-control form-control-sm" placeholder="mis. total_days" value="{{ $cond['field'] ?? '' }}">
    </div>
    <div class="form-group col-md-3 mb-0">
      <label class="small mb-1">operator</label>
      <select name="steps[{{ $i }}][condition_operator]" class="form-control form-control-sm">
        @foreach(['=','!=','>','>=','<','<=','in'] as $op)<option value="{{ $op }}" @selected(($cond['operator'] ?? '') === $op)>{{ $op }}</option>@endforeach
      </select>
    </div>
    <div class="form-group col-md-5 mb-0">
      <label class="small mb-1">nilai</label>
      <input type="text" name="steps[{{ $i }}][condition_value]" class="form-control form-control-sm" placeholder="kosongkan = selalu berlaku" value="{{ is_array($cond['value'] ?? null) ? implode(',', $cond['value']) : ($cond['value'] ?? '') }}">
    </div>
  </div>
</div>
