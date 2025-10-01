(function () {
  'use strict';

  // Helpers
  const $ = (sel) => document.querySelector(sel);
  const currency = new Intl.NumberFormat(undefined, { style: 'currency', currency: 'USD' });

  const els = {
    error: $('#cart-error'),
    loading: $('#loading'),
    list: $('#cart-list'),
    rows: $('#cart-rows'),
    empty: $('#empty'),
    summaryItems: $('#summary-items'),
    summarySubtotal: $('#summary-subtotal'),
    summaryTotal: $('#summary-total'),
  };

  /**
   * Render a single cart row
   * @param {Object} item
   * @param {string} item.name
   * @param {number} item.price
   * @param {number} item.quantity
   */
  function renderRow(item) {
    const row = document.createElement('div');
    row.className = 'cart-item';

    const name = document.createElement('div');
    name.textContent = item.name ?? '(Unnamed item)';
    name.title = item.name ?? 'Unnamed item';
    row.appendChild(name);

    const price = document.createElement('div');
    price.className = 'right';
    const priceVal = Number(item.price);
    price.textContent = Number.isFinite(priceVal) ? currency.format(priceVal) : '—';
    row.appendChild(price);

    const qty = document.createElement('div');
    qty.className = 'right';
    const qtyVal = Number(item.quantity);
    qty.textContent = Number.isFinite(qtyVal) ? qtyVal : '—';
    row.appendChild(qty);

    const total = document.createElement('div');
    total.className = 'right';
    const lineTotal = (Number.isFinite(priceVal) && Number.isFinite(qtyVal)) ? priceVal * qtyVal : NaN;
    total.textContent = Number.isFinite(lineTotal) ? currency.format(lineTotal) : '—';
    row.appendChild(total);

    return { row, lineTotal: Number.isFinite(lineTotal) ? lineTotal : 0, qty: Number.isFinite(qtyVal) ? qtyVal : 0 };
  }

  /**
   * Render the cart list and summary
   * @param {Array} items
   */
  function renderCart(items) {
    // Reset UI
    els.rows.innerHTML = '';
    els.error.style.display = 'none';
    els.loading.classList.add('hidden');

    if (!Array.isArray(items) || items.length === 0) {
      els.list.classList.add('hidden');
      els.empty.classList.remove('hidden');
      els.summaryItems.textContent = '0';
      els.summarySubtotal.textContent = currency.format(0);
      els.summaryTotal.textContent = currency.format(0);
      return;
    }

    els.list.classList.remove('hidden');
    els.empty.classList.add('hidden');

    let subtotal = 0;
    let totalItems = 0;

    for (const item of items) {
      const { row, lineTotal, qty } = renderRow(item);
      els.rows.appendChild(row);
      subtotal += lineTotal;
      totalItems += qty;
    }

    // Summary
    els.summaryItems.textContent = String(totalItems);
    els.summarySubtotal.textContent = currency.format(subtotal);
    els.summaryTotal.textContent = currency.format(subtotal);
  }

  /**
   * Display a fetch error message gracefully
   * @param {Error|string} err
   */
  function showError(err) {
    els.loading.classList.add('hidden');
    els.list.classList.add('hidden');
    els.empty.classList.add('hidden');
    els.error.textContent = `Could not load cart data: ${err?.message || err}`;
    els.error.style.display = 'block';
  }

  // Fetch on load
  window.addEventListener('DOMContentLoaded', async () => {
    try {
      const res = await fetch('cart_data.json', { cache: 'no-store' });
      if (!res.ok) throw new Error(`${res.status} ${res.statusText}`);
      const data = await res.json();
      renderCart(data);
    } catch (err) {
      showError(err);
      console.error(err);
    }
  });
})();