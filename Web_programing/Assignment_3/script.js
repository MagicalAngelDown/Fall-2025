(function () {
  'use strict';

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  // Currency formatter (USD)
  const fmt = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' });

  // Fallback prices if data-price is missing (keys match <option value> attributes)
  const FALLBACK_PRICES = {
    'widget-basic': 9.99,
    'widget-plus': 19.99,
    'widget-pro': 29.99
  };

  // Extract price from the selected <option>.
  // Priority: data-price attribute -> fallback map -> parse from label "($9.99)"
  function readSelectedPrice(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    if (!opt || !opt.value) return 0;

    // 1) data-price attribute (assignment requirement)
    const attr = opt.getAttribute('data-price');
    if (attr && !Number.isNaN(parseFloat(attr))) {
      return parseFloat(attr);
    }

    // 2) fallback map by option value
    if (FALLBACK_PRICES.hasOwnProperty(opt.value)) {
      return FALLBACK_PRICES[opt.value];
    }

    // 3) last resort: parse "($9.99)" from option text
    const m = opt.textContent.match(/\$\s*([0-9]+(?:\.[0-9]{1,2})?)/);
    return m ? parseFloat(m[1]) : 0;
  }

  // Render or update a live total next to the form.
  function ensureTotalUI(form) {
    let totalWrap = $('#total-wrap', form);
    if (!totalWrap) {
      totalWrap = document.createElement('div');
      totalWrap.id = 'total-wrap';
      totalWrap.className = 'total-wrap';
      totalWrap.innerHTML = `
        <div class="form-row">
          <span class="label">Current Total</span>
          <div>
            <output id="total" aria-live="polite" aria-atomic="true">—</output>
          </div>
        </div>
      `;
      // Insert just before the actions row
      const actions = $('.actions', form);
      actions ? form.insertBefore(totalWrap, actions) : form.appendChild(totalWrap);
    }
    return totalWrap;
  }

  // Create a hidden receipt area after the form (shown on submit)
  function ensureReceiptUI(form) {
    let receipt = $('#receipt');
    if (!receipt) {
      receipt = document.createElement('section');
      receipt.id = 'receipt';
      receipt.className = 'receipt';
      receipt.hidden = true;
      // Accessible heading for the receipt section
      receipt.innerHTML = `
        <h2 class="receipt__title">Receipt</h2>
        <div class="receipt__body"></div>
      `;
      form.insertAdjacentElement('afterend', receipt);
    }
    return receipt;
  }

  // Basic inline error utilities
  function clearErrors(form) {
    $$('.error-message', form).forEach(e => e.remove());
    $$('.error', form).forEach(el => el.classList.remove('error'));
  }

  function showError(inputEl, msg) {
    const row = inputEl.closest('.form-row') || inputEl.parentElement;
    if (!row) return;
    inputEl.classList.add('error');
    const p = document.createElement('p');
    p.className = 'error-message';
    p.role = 'alert';
    p.textContent = msg;
    row.appendChild(p);
  }

  function validate(form) {
    clearErrors(form);
    let ok = true;

    const product = $('#product', form);
    if (!product.value) {
      ok = false;
      showError(product, 'Please select a product.');
    }

    const qty = $('#quantity', form);
    const qVal = parseInt(qty.value, 10);
    if (Number.isNaN(qVal) || qVal < 1) {
      ok = false;
      showError(qty, 'Quantity must be 1 or more.');
    }

    // Example of required radio group (payment_type)
    const payment = $('input[name="payment_type"]:checked', form);
    if (!payment) {
      // attach to the first radio's container for visibility
      const firstRadio = $('input[name="payment_type"]', form);
      if (firstRadio) showError(firstRadio, 'Please select a payment method.');
      ok = false;
    }

    return ok;
  }

  // Compute and render total
  function updateTotal(form) {
    const productSel = $('#product', form);
    const quantityEl = $('#quantity', form);
    const totalOut = $('#total', form);

    const price = readSelectedPrice(productSel);
    const qty = parseInt(quantityEl.value, 10) || 0;
    const total = price * qty;

    if (totalOut) totalOut.textContent = qty > 0 ? fmt.format(total) : '—';
    return { price, qty, total };
  }

  function renderReceipt(form, calc) {
    const receipt = ensureReceiptUI(form);
    const body = $('.receipt__body', receipt);
    if (!body) return;

    const first = $('#first_name', form)?.value?.trim() || '';
    const last = $('#last_name', form)?.value?.trim() || '';
    const email = $('#email', form)?.value?.trim() || '';
    const phone = $('#phone', form)?.value?.trim() || '';
    const addr = $('#shipping_address', form)?.value?.trim() || '';

    const productSel = $('#product', form);
    const opt = productSel.options[productSel.selectedIndex];
    const productLabel = opt ? opt.textContent.replace(/\s*\([^\)]*\)\s*$/, '').trim() : '';

    const date = $('#order_date', form)?.value || '';
    const payment = $('input[name="payment_type"]:checked', form)?.value || '';

    // Build a simple receipt
    body.innerHTML = `
      <div class="receipt__grid">
        <div>
          <h3>Customer</h3>
          <p>${first} ${last}</p>
          <p>${email}</p>
          ${phone ? `<p>${phone}</p>` : ''}
          ${addr ? `<p class="addr">${addr.replace(/\n/g, '<br>')}</p>` : ''}
        </div>
        <div>
          <h3>Order</h3>
          <p><strong>Item:</strong> ${productLabel}</p>
          <p><strong>Unit Price:</strong> ${fmt.format(calc.price)}</p>
          <p><strong>Quantity:</strong> ${calc.qty}</p>
          <p><strong>Total:</strong> ${fmt.format(calc.total)}</p>
          ${date ? `<p><strong>Date:</strong> ${date}</p>` : ''}
          ${payment ? `<p><strong>Payment:</strong> ${payment.toUpperCase()}</p>` : ''}
        </div>
      </div>
    `;

    receipt.hidden = false;
    receipt.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // ---- Init ----------------------------------------------------------------
  document.addEventListener('DOMContentLoaded', () => {
    const form = $('#order-form');
    if (!form) return;

    // Make sure the "Current Total" UI exists
    ensureTotalUI(form);

    // Live updates
    $('#product', form)?.addEventListener('change', () => updateTotal(form));
    $('#quantity', form)?.addEventListener('input', () => updateTotal(form));

    // Initial render
    updateTotal(form);

    // Submit handler — prevent full page reload, validate, render receipt
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      if (!validate(form)) {
        // Keep total in sync even on invalid submit
        updateTotal(form);
        return;
      }
      const calc = updateTotal(form);
      renderReceipt(form, calc);
    });

    // Reset handler — clear receipt & errors and reset total
    form.addEventListener('reset', () => {
      clearErrors(form);
      const receipt = $('#receipt');
      if (receipt) receipt.hidden = true;
      // give the reset a tick to update inputs then recalc
      setTimeout(() => updateTotal(form), 0);
    });
  });

  // ---------------- Validation Helpers ----------------
  const alphaRegex = /^[A-Za-z]+(?:[ '-][A-Za-z]+)*$/;
  function isAlphaName(v) {
    return alphaRegex.test(v.trim());
  }
  function isEmail(v) {
    // Simple, robust email check (not overly strict)
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim());
  }
  function onlyDigits(v) {
    return v.replace(/[^\d]/g, '');
  }
  function isPhone(v) {
    // Accept 10+ digits (US-style), ignoring spaces, dashes, parentheses
    const d = onlyDigits(v);
    return d.length >= 10 && d.length <= 15;
  }
  function isPositiveInt(v) {
    return /^\d+$/.test(String(v)) && parseInt(v, 10) > 0;
  }
  function isSixteenDigits(v) {
    return onlyDigits(v).length === 16;
  }
  function isValidDateString(v) {
    // Our <select> contains formatted strings like "August 24, 2025"
    if (!v) return false;
    const t = Date.parse(v);
    return !Number.isNaN(t);
  }

  function validate(form) {
    clearErrors(form);
    let ok = true;

    // First/Last
    const first = $('#first_name', form);
    if (!first.value.trim() || !isAlphaName(first.value)) {
      ok = false;
      showError(first, 'Enter a valid first name (letters only).');
    }

    const last = $('#last_name', form);
    if (!last.value.trim() || !isAlphaName(last.value)) {
      ok = false;
      showError(last, 'Enter a valid last name (letters only).');
    }

    // Email
    const email = $('#email', form);
    if (!email.value.trim() || !isEmail(email.value)) {
      ok = false;
      showError(email, 'Enter a valid email address (e.g., name@example.com).');
    }

    // Phone
    const phone = $('#phone', form);
    if (!phone.value.trim() || !isPhone(phone.value)) {
      ok = false;
      showError(phone, 'Enter a valid phone number (digits only, at least 10).');
    }

    // Address
    const addr = $('#shipping_address', form);
    if (!addr.value.trim()) {
      ok = false;
      showError(addr, 'Shipping address is required.');
    }

    // Product
    const product = $('#product', form);
    if (!product.value) {
      ok = false;
      showError(product, 'Please select a product.');
    }

    // Quantity
    const qty = $('#quantity', form);
    if (!isPositiveInt(qty.value)) {
      ok = false;
      showError(qty, 'Quantity must be a whole number greater than 0.');
    }

    // Payment method (use a loop per assignment)
    const paymentRadios = $$('input[name="payment_type"]', form);
    let paymentSelected = false;
    for (const r of paymentRadios) {
      if (r.checked) { paymentSelected = true; break; }
    }
    if (!paymentSelected) {
      // attach error near first radio
      if (paymentRadios[0]) showError(paymentRadios[0], 'Please select a payment method.');
      ok = false;
    }

    // Card number — only if a payment method is chosen
    const card = $('#card_number', form);
    if (!card.value.trim() || !isSixteenDigits(card.value)) {
      ok = false;
      showError(card, 'Card number must be exactly 16 digits.');
    }

    // Date
    const dateSel = $('#order_date', form);
    const dateVal = dateSel.value;
    if (!dateVal || !isValidDateString(dateVal)) {
      ok = false;
      showError(dateSel, 'Please choose a valid order date.');
    }

    return ok;
  }

  // Attach realtime validators
  function attachRealtimeValidation(form) {
    const map = [
      ['#first_name',  (el)=> isAlphaName(el.value) ],
      ['#last_name',   (el)=> isAlphaName(el.value) ],
      ['#email',       (el)=> isEmail(el.value) ],
      ['#phone',       (el)=> isPhone(el.value) ],
      ['#shipping_address', (el)=> el.value.trim().length > 0 ],
      ['#product',     (el)=> !!el.value ],
      ['#quantity',    (el)=> isPositiveInt(el.value) ],
      ['#card_number', (el)=> isSixteenDigits(el.value) ],
      ['#order_date',  (el)=> !!el.value && isValidDateString(el.value) ],
    ];

    for (const [sel, fn] of map) {
      const el = $(sel, form);
      if (!el) continue;
      const evt = el.tagName === 'SELECT' ? 'change' : 'input';
      el.addEventListener(evt, () => {
        // Re-evaluate this field: if valid, remove its error styles/message
        const wasError = el.classList.contains('error');
        if (fn(el)) {
          // clear only this field's error message to avoid nuking others
          const row = el.closest('.form-row') || el.parentElement;
          if (row) {
            const msg = row.querySelector('.error-message');
            if (msg) msg.remove();
          }
          el.classList.remove('error');
        }
      });
    }

    // Payment radios — clear group error on change
    const radios = $$('input[name="payment_type"]', form);
    for (const r of radios) {
      r.addEventListener('change', () => {
        const row = r.closest('.form-row') || r.parentElement;
        if (row) {
          const msg = row.querySelector('.error-message');
          if (msg) msg.remove();
        }
        radios.forEach(el => el.classList.remove('error'));
      });
    }
  }

   // Realtime field validation
    attachRealtimeValidation(form);

})(); 
