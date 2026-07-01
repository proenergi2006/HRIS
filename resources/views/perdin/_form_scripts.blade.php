<script>
(function () {
  const rupiah = (n) => 'Rp ' + (parseInt(n || 0, 10)).toLocaleString('id-ID');
  const cats   = window.__perdinCategories || {};

  // ── Budget ────────────────────────────────────────────────────────────────
  const budgetBody = document.getElementById('budget-body');
  let bIdx = 0;

  function catOptions(selected) {
    return Object.entries(cats).map(([v, l]) =>
      `<option value="${v}" ${v === selected ? 'selected' : ''}>${l}</option>`).join('');
  }

  function addBudgetRow(data) {
    data = data || {};
    const i = bIdx++;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <select name="budget[${i}][category]" class="form-control form-control-sm" required>
          ${catOptions(data.category || 'transportasi')}
        </select>
      </td>
      <td><input type="text" name="budget[${i}][item_name]" class="form-control form-control-sm"
                 value="${(data.item_name || '').replace(/"/g, '&quot;')}" placeholder="Nama item" required></td>
      <td>
        <select name="budget[${i}][handled_by]" class="form-control form-control-sm">
          <option value="self" ${data.handled_by === 'ga' ? '' : 'selected'}>Sendiri</option>
          <option value="ga" ${data.handled_by === 'ga' ? 'selected' : ''}>By GA</option>
        </select>
      </td>
      <td><input type="number" name="budget[${i}][qty]" class="form-control form-control-sm text-right b-qty"
                 min="1" value="${parseInt(data.qty || 1, 10)}" required></td>
      <td><input type="number" name="budget[${i}][unit_cost]" class="form-control form-control-sm text-right b-cost"
                 min="0" value="${parseInt(data.unit_cost || 0, 10)}" required></td>
      <td class="text-right align-middle b-total">Rp 0</td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-xs btn-outline-danger b-del"><i class="gd-trash icon-text"></i></button>
      </td>`;
    budgetBody.appendChild(tr);
    recalcBudget();
  }

  function recalcBudget() {
    let totalAll = 0, totalSelf = 0;
    budgetBody.querySelectorAll('tr').forEach(tr => {
      const qty  = parseInt(tr.querySelector('.b-qty').value || 0, 10);
      const cost = parseInt(tr.querySelector('.b-cost').value || 0, 10);
      const sub  = qty * cost;
      tr.querySelector('.b-total').textContent = rupiah(sub);
      totalAll += sub;
      const handled = tr.querySelector('select[name$="[handled_by]"]').value;
      if (handled === 'self') totalSelf += sub;
    });
    document.getElementById('total-all').textContent  = rupiah(totalAll);
    document.getElementById('total-self').textContent = rupiah(totalSelf);
  }

  budgetBody.addEventListener('input', recalcBudget);
  budgetBody.addEventListener('change', recalcBudget);
  budgetBody.addEventListener('click', (e) => {
    if (e.target.closest('.b-del')) { e.target.closest('tr').remove(); recalcBudget(); }
  });
  document.getElementById('btn-add-budget').addEventListener('click', () => addBudgetRow());

  // ── Itinerary ───────────────────────────────────────────────────────────
  const itiBody = document.getElementById('iti-body');
  let iIdx = 0;

  function renumber() {
    itiBody.querySelectorAll('tr').forEach((tr, idx) => {
      tr.querySelector('.i-no').textContent = idx + 1;
    });
  }

  function addItiRow(data) {
    data = data || {};
    const i = iIdx++;
    const tz = data.timezone || 'WIB';
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="text-center align-middle i-no"></td>
      <td><input type="date" name="itinerary[${i}][travel_date]" class="form-control form-control-sm"
                 value="${data.travel_date || ''}"></td>
      <td><input type="time" name="itinerary[${i}][time_start]" class="form-control form-control-sm"
                 value="${data.time_start || ''}"></td>
      <td><input type="time" name="itinerary[${i}][time_end]" class="form-control form-control-sm"
                 value="${data.time_end || ''}"></td>
      <td>
        <select name="itinerary[${i}][timezone]" class="form-control form-control-sm">
          <option value="WIB" ${tz === 'WIB' ? 'selected' : ''}>WIB</option>
          <option value="WITA" ${tz === 'WITA' ? 'selected' : ''}>WITA</option>
          <option value="WIT" ${tz === 'WIT' ? 'selected' : ''}>WIT</option>
        </select>
      </td>
      <td><input type="text" name="itinerary[${i}][description]" class="form-control form-control-sm"
                 value="${(data.description || '').replace(/"/g, '&quot;')}" placeholder="Keterangan kegiatan"></td>
      <td class="text-center align-middle">
        <button type="button" class="btn btn-xs btn-outline-danger i-del"><i class="gd-trash icon-text"></i></button>
      </td>`;
    itiBody.appendChild(tr);
    renumber();
  }

  itiBody.addEventListener('click', (e) => {
    if (e.target.closest('.i-del')) { e.target.closest('tr').remove(); renumber(); }
  });
  document.getElementById('btn-add-iti').addEventListener('click', () => addItiRow());

  // ── Seed ──────────────────────────────────────────────────────────────────
  if (Array.isArray(window.__perdinBudget) && window.__perdinBudget.length) {
    window.__perdinBudget.forEach(addBudgetRow);
  } else {
    addBudgetRow();
  }

  if (Array.isArray(window.__perdinItinerary) && window.__perdinItinerary.length) {
    window.__perdinItinerary.forEach(addItiRow);
  } else {
    addItiRow();
  }
})();
</script>
