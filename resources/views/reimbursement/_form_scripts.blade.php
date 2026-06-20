<script>
(function () {
  var idx       = 0;
  var amtFields = @json(array_keys(\App\Models\Reimbursement\ReimbursementItem::AMOUNT_FIELDS));
  var textFields = ['patient_name', 'treatment_date', 'institution', 'diagnose'];

  function fmt(n) {
    return 'Rp ' + Number(n).toLocaleString('id-ID');
  }

  function rowTotal(row) {
    var sum = 0;
    amtFields.forEach(function(f) {
      var inp = row.querySelector('[data-col="' + f + '"]');
      if (inp) sum += parseInt(inp.value) || 0;
    });
    return sum;
  }

  function updateTotals() {
    var grand   = 0;
    var colSums = {};
    amtFields.forEach(function(f) { colSums[f] = 0; });

    document.querySelectorAll('#items-body tr').forEach(function(row) {
      var rt = rowTotal(row);
      row.querySelector('.row-total').textContent = fmt(rt);
      grand += rt;
      amtFields.forEach(function(f) {
        var inp = row.querySelector('[data-col="' + f + '"]');
        colSums[f] += inp ? (parseInt(inp.value) || 0) : 0;
      });
    });

    document.getElementById('grand-total').textContent = fmt(grand);
    document.querySelectorAll('.col-sum').forEach(function(el) {
      el.textContent = fmt(colSums[el.dataset.field] || 0);
    });
  }

  function buildRow(rowIdx) {
    var h = '<tr data-idx="' + rowIdx + '">';
    h += '<td><input type="text"   name="items[' + rowIdx + '][patient_name]"   data-col="patient_name"   class="form-control form-control-sm" required></td>';
    h += '<td><input type="date"   name="items[' + rowIdx + '][treatment_date]" data-col="treatment_date" class="form-control form-control-sm" max="{{ now()->format('Y-m-d') }}" required></td>';
    h += '<td><input type="text"   name="items[' + rowIdx + '][institution]"    data-col="institution"    class="form-control form-control-sm" required></td>';
    h += '<td><input type="text"   name="items[' + rowIdx + '][diagnose]"       data-col="diagnose"       class="form-control form-control-sm"></td>';
    amtFields.forEach(function(f) {
      h += '<td><input type="number" name="items[' + rowIdx + '][' + f + ']" data-col="' + f + '" class="form-control form-control-sm amt-input text-right" min="0" value="0"></td>';
    });
    h += '<td class="text-right font-weight-bold row-total align-middle">Rp 0</td>';
    h += '<td class="text-center align-middle"><button type="button" class="btn btn-xs btn-outline-danger btn-remove-row"><i class="gd-minus"></i></button></td>';
    h += '</tr>';
    return h;
  }

  function addRow(data) {
    var tbody = document.getElementById('items-body');
    tbody.insertAdjacentHTML('beforeend', buildRow(idx));
    var row = tbody.querySelector('tr[data-idx="' + idx + '"]');

    if (data) {
      textFields.concat(amtFields).forEach(function(col) {
        var inp = row.querySelector('[data-col="' + col + '"]');
        if (inp && data[col] !== undefined && data[col] !== null) {
          inp.value = data[col];
        }
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

  if (window.__existingItems && window.__existingItems.length) {
    window.__existingItems.forEach(function(d) { addRow(d); });
  } else {
    addRow(null);
  }
})();
</script>
