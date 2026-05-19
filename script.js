// ===== Shopping Cart Logic =====
let cart = [];

// Add item to cart
function addToCart(name, price) {
  cart.push({ name, price });
  updateCart();
}

// Update cart display
function updateCart() {
  const cartItems = document.getElementById('cart-items');
  const cartTotal = document.getElementById('cart-total');
  cartItems.innerHTML = '';
  let total = 0;

  cart.forEach((item, index) => {
    total += item.price;
    const div = document.createElement('div');
    div.className = 'cart-item';
    div.innerHTML = `
      <span>${item.name}</span>
      <span>RM ${item.price}</span>
      <button onclick="removeFromCart(${index})" class="remove-btn">✖</button>
    `;
    cartItems.appendChild(div);
  });

  cartTotal.textContent = total;
}

// Remove item from cart
function removeFromCart(index) {
  cart.splice(index, 1);
  updateCart();
}

// Close cart
function closeCart() {
  document.getElementById('cart').classList.remove('open');
}

// ===== Dark/Light Mode =====
function toggleMode() {
  document.body.classList.toggle('dark-mode');
}

// ===== Checkout =====
function checkout() {
  if (cart.length === 0) {
    alert("Your cart is empty!");
    return;
  }
  let summary = "You are purchasing:\n";
  cart.forEach(item => {
    summary += `- ${item.name}: RM ${item.price}\n`;
  });
  summary += `\nTotal: RM ${cart.reduce((sum, item) => sum + item.price, 0)}`;
  alert(summary + "\n\nThank you for shopping with Blue Ocean!");
  cart = [];
  updateCart();
}
