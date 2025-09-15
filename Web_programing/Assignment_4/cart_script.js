// ---------- Data (Arrays & Objects)
// Product list (array of product objects)
const products = [
    {
        name: "Myriad Hoodie",
        price: 39.99,
        description: "Light‑weight hoodie with a soft fleece lining.",
    },
    {
        name: "Aurora Hoodie",
        price: 39.99,
        description: "Cozy mid‑weight hoodie with a soft fleece lining.",
    },
    {
        name: "Infinity Mug",
        price: 12.5,
        description: "Ceramic 12oz mug, dishwasher safe.",
    },
    {
        name: "Pixel Socks",
        price: 9.0,
        description: "Breathable cotton socks with pixel pattern.",
    },
    {
        name: "Nebula Tee",
        price: 19.99,
        description: "Classic fit t‑shirt with nebula print.",
    },
    {
        name: "Glow Sticker Pack",
        price: 6.0,
        description: "Set of 6 glow‑in‑the‑dark vinyl stickers.",
    },
];

// Cart (array of cart item objects)
// Each item: { name: string, price: number, quantity: number }
let cart = [];

// ---------- Helpers
const $ = (sel) => document.querySelector(sel);
const fmt = new Intl.NumberFormat(undefined, { style: "currency", currency: "USD" });

function findCartItemIndex(name) {
  return cart.findIndex((item) => item.name === name);
}

function calcTotals() {
  const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
  const totalCost = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
  return { totalItems, totalCost };
}

// ---------- Rendering: Products
function renderProducts() {
  const list = $("#product-list");
  list.innerHTML = "";

  products.forEach((p) => {
    const card = document.createElement("article");
    card.className = "product-card";
    card.setAttribute("role", "listitem");

    card.innerHTML = `
      <h3 class="product-name">${p.name}</h3>
      <p class="product-desc">${p.description}</p>
      <div class="row">
        <span class="price">${fmt.format(p.price)}</span>
        <button class="btn add-btn" data-name="${p.name}">Add to Cart</button>
      </div>
    `;

    list.appendChild(card);
  });
}

// ---------- Cart Operations
function addToCart(name) {
  const product = products.find((p) => p.name === name);
  if (!product) return;

  const idx = findCartItemIndex(name);
  if (idx >= 0) {
    cart[idx].quantity += 1;
  } else {
    cart.push({ name: product.name, price: product.price, quantity: 1 });
  }
  renderCart();
}

function removeFromCart(name) {
  cart = cart.filter((item) => item.name !== name);
  renderCart();
}

function updateQuantity(name, qty) {
  const idx = findCartItemIndex(name);
  if (idx < 0) return;
  const newQty = Number.isFinite(qty) && qty > 0 ? Math.floor(qty) : 1;
  cart[idx].quantity = newQty;
  renderCart();
}

function clearCart() {
  cart = [];
  renderCart();
}

// ---------- Rendering: Cart & Summary
function renderCart() {
  const body = $("#cart-body");
  const table = $("#cart-table");
  const empty = $("#cart-empty");

  body.innerHTML = "";

  if (cart.length === 0) {
    table.hidden = true;
    empty.hidden = false;
  } else {
    table.hidden = false;
    empty.hidden = true;

    cart.forEach((item) => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${item.name}</td>
        <td class="right">${fmt.format(item.price)}</td>
        <td class="center">
          <input
            type="number"
            class="qty-input"
            min="1"
            step="1"
            value="${item.quantity}"
            data-name="${item.name}"
            aria-label="Quantity for ${item.name}"
          />
        </td>
        <td class="right">${fmt.format(item.price * item.quantity)}</td>
        <td class="center">
          <button class="btn btn-danger remove-btn" data-name="${item.name}" aria-label="Remove ${item.name}">✕</button>
        </td>`;
      body.appendChild(tr);
    });
  }

  const { totalItems, totalCost } = calcTotals();
  $("#summary-items").textContent = String(totalItems);
  $("#summary-cost").textContent = fmt.format(totalCost);
}

// ---------- Event Listeners (Delegation)
function attachEvents() {
  // Add to cart (from products list)
  $("#product-list").addEventListener("click", (e) => {
    const btn = e.target.closest(".add-btn");
    if (!btn) return;
    addToCart(btn.dataset.name);
  });

  // Quantity change & remove (inside cart table)
  $("#cart-table").addEventListener("input", (e) => {
    const input = e.target.closest(".qty-input");
    if (!input) return;
    updateQuantity(input.dataset.name, parseInt(input.value, 10));
  });

  $("#cart-table").addEventListener("click", (e) => {
    const btn = e.target.closest(".remove-btn");
    if (!btn) return;
    removeFromCart(btn.dataset.name);
  });

  // Clear cart
  $("#clear-cart").addEventListener("click", clearCart);
}

// ---------- Init
function init() {
  renderProducts();
  renderCart();
  attachEvents();
}

document.addEventListener("DOMContentLoaded", init);