<script>
(function () {
  var idx = 0;
  var amtFields = @json(array_keys(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS));

  function fmt(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
  }

  function rowTotal(row) {
    var sum = 0;
    row.querySelectorAll('.amt-input').forEach(function(inp) { sum += parseInt(inp.value) || 0; });
    return sum;
  }

  function updateTotals() {
    var grand = 0;
    var colSums = {};
    amtFields.forEach(function(f) { colSums[f] = 0; });

    document.querySelectorAll('#items-body tr').forEach(function(row) {
      var rt = rowTotal(row);
      row.querySelector('.row-total').textContent = fmt(rt);
      grand += rt;
      row.querySelectorAll('.amt-input').forEach(function(inp) {
        colSums[inp.dataset.field] = (colSums[inp.dataset.field] || 0) + (parseInt(inp.value) || 0);
      });
    });

    document.getElementById('grand-total').textContent = fmt(grand);
    document.querySelectorAll('.col-sum').forEach(function(el) {
      el.textContent = fmt(colSums[el.dataset.field] || 0);
    });
  }

  function addRow(data) {
    var tpl = document.getElementById('row-tpl').innerHTML.replace(/__IDX__/g, idx);
    var tbody = document.getElementById('items-body');
    tbody.insertAdjacentHTML('beforeend', tpl);
    var row = tbody.querySelector('tr[data-idx="' + idx + '"]');

    if (data) {
      row.querySelector('[data-col="patient_name"]').value   = data.patient_name || '';
      row.querySelector('[data-col="treatment_date"]').value = data.treatment_date || '';
      row.querySelector('[data-col="institution"]').value    = data.institution || '';
      row.querySelector('[data-col="diagnose"]').value       = data.diagnose || '';
      amtFields.forEach(function(f) {
        var inp = row.querySelector('[data-field="' + f + '"]');
        if (inp) inp.value = data[f] || 0;
      });
    }

    row.querySelectorAll('.amt-input').forEach(function(inp) {
      inp.addEventListener('input', updateTotals);
    });
    row.querySelector('.btn-remove-row').addEventListener('click', function() {
      if (document.querySelectorAll('#items-body tr').length <= 1) return;
      row.remove();
      updateTotals();
    });

    idx++;
    updateTotals();
  }

  document.getElementById('btn-add-row').addEventListener('click', function() { addRow(null); });

  // pre-fill existing or start with empty row
  if (window.__existingItems && window.__existingItems.length) {
    window.__existingItems.forEach(function(d) { addRow(d); });
  } else {
    addRow(null);
  }
})();
</script>
