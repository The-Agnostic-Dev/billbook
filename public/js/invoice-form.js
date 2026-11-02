// Live line totals and invoice totals on the create / edit invoice form.
(() => {
  const table = document.getElementById('line-items');
  if (!table) return;

  const body = table.querySelector('tbody');
  const template = document.getElementById('line-template');
  const totals = document.getElementById('form-totals');
  const vatRate = parseFloat(table.dataset.vatRate) || 0;
  const currency = new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' });
  let nextIndex = Date.now();

  const value = (row, selector) => parseFloat(row.querySelector(selector)?.value) || 0;
  const round2 = (amount) => Math.round(amount * 100) / 100;
  const show = (key, amount) => {
    totals.querySelector(`[data-total="${key}"]`).textContent = currency.format(amount);
  };

  function recalculate() {
    let gross = 0;
    let subtotal = 0;

    body.querySelectorAll('tr.line').forEach((row) => {
      const lineGross = value(row, '.qty') * value(row, '.price');
      const lineTotal = round2(lineGross * (1 - value(row, '.discount') / 100));
      row.querySelector('.line-total').textContent = currency.format(lineTotal);
      gross += lineGross;
      subtotal += lineTotal;
    });

    const vat = round2(gross * vatRate);
    show('gross', round2(gross));
    show('discount', round2(gross - subtotal));
    show('vat', vat);
    show('total', round2(subtotal + vat));
  }

  function addLine() {
    const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
    body.insertAdjacentHTML('beforeend', html);
    body.lastElementChild.querySelector('input[type="text"]').focus();
    recalculate();
  }

  function removeLine() {
    const rows = body.querySelectorAll('tr.line');
    if (rows.length <= 1) return;
    rows[rows.length - 1].remove();
    recalculate();
  }

  document.getElementById('add-line').addEventListener('click', addLine);
  body.addEventListener('input', recalculate);
  body.addEventListener('click', (event) => {
    if (event.target.closest('.remove-line')) removeLine();
  });

  recalculate();
})();
