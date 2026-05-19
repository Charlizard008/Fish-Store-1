// ======================================
// BLUE OCEAN FISH STORE JAVASCRIPT
// ======================================

// ===============================
// SHOPPING CART
// ===============================

let cart =
JSON.parse(localStorage.getItem("blueOceanCart"))
|| [];

// Load cart when page starts

window.onload = function(){

    updateCart();

};

// ===============================
// ADD TO CART
// ===============================

function addToCart(name,price){

    cart.push({
        name:name,
        price:price
    });

    saveCart();

    updateCart();

    showNotification(
        name + " added to cart!"
    );

}

// ===============================
// SAVE CART
// ===============================

function saveCart(){

    localStorage.setItem(
        "blueOceanCart",
        JSON.stringify(cart)
    );

}

// ===============================
// UPDATE CART
// ===============================

function updateCart(){

    const cartItems =
    document.getElementById("cart-items");

    const cartTotal =
    document.getElementById("cart-total");

    if(!cartItems || !cartTotal) return;

    cartItems.innerHTML = "";

    let total = 0;

    cart.forEach((item,index)=>{

        total += item.price;

        const div =
        document.createElement("div");

        div.className = "cart-item";

        div.innerHTML = `

            <div>

                <h4>${item.name}</h4>

                <p>RM ${item.price}</p>

            </div>

            <button
                class="remove-btn"
                onclick="removeFromCart(${index})">

                ✖

            </button>

        `;

        cartItems.appendChild(div);

    });

    cartTotal.textContent = total;

}

// ===============================
// REMOVE FROM CART
// ===============================

function removeFromCart(index){

    cart.splice(index,1);

    saveCart();

    updateCart();

    showNotification(
        "Item removed from cart."
    );

}

// ===============================
// OPEN CART
// ===============================

function openCart(){

    document.getElementById("cart")
    .classList.add("open");

}

// ===============================
// CLOSE CART
// ===============================

function closeCart(){

    document.getElementById("cart")
    .classList.remove("open");

}

// ===============================
// CHECKOUT
// ===============================

function checkout(){

    if(cart.length === 0){

        alert(
            "Your cart is empty!"
        );

        return;

    }

    let summary =
    "You are purchasing:\n\n";

    cart.forEach(item=>{

        summary +=
        `• ${item.name} - RM ${item.price}\n`;

    });

    const total =
    cart.reduce((sum,item)=>
    sum + item.price,0);

    summary +=
    `\nTotal: RM ${total}`;

    alert(
        summary +
        "\n\nThank you for shopping with Blue Ocean Fish Store!"
    );

    cart = [];

    saveCart();

    updateCart();

    closeCart();

}

// ===============================
// DARK MODE
// ===============================

function toggleMode(){

    document.body.classList.toggle(
        "dark-mode"
    );

    // Save preference

    if(document.body.classList.contains("dark-mode")){

        localStorage.setItem(
            "theme",
            "dark"
        );

    }
    else{

        localStorage.setItem(
            "theme",
            "light"
        );

    }

}

// ===============================
// LOAD THEME
// ===============================

const savedTheme =
localStorage.getItem("theme");

if(savedTheme === "dark"){

    document.body.classList.add(
        "dark-mode"
    );

}

// ===============================
// SEARCH SYSTEM
// ===============================

const searchInput =
document.getElementById("searchInput");

if(searchInput){

    searchInput.addEventListener(
        "keyup",
        function(){

            const value =
            this.value.toLowerCase();

            const products =
            document.querySelectorAll(
                ".product-card"
            );

            products.forEach(product=>{

                const text =
                product.innerText.toLowerCase();

                if(text.includes(value)){

                    product.style.display =
                    "block";

                }
                else{

                    product.style.display =
                    "none";

                }

            });

        }
    );

}

// ===============================
// WISHLIST BUTTON
// ===============================

function wishlist(){

    showNotification(
        "Added to wishlist ❤"
    );

}

// ===============================
// NEWSLETTER SUBSCRIBE
// ===============================

function subscribe(){

    const emailInput =
    document.querySelector(
        ".newsletter input"
    );

    if(!emailInput){

        return;

    }

    const email =
    emailInput.value.trim();

    if(email === ""){

        alert(
            "Please enter your email."
        );

        return;

    }

    alert(
        "Thank you for subscribing, " +
        email + "!"
    );

    emailInput.value = "";

}

// ===============================
// ADMIN LOGIN
// ===============================

function adminLogin(){

    const username =
    document.getElementById(
        "admin-username"
    ).value;

    const password =
    document.getElementById(
        "admin-password"
    ).value;

    if(
        username === "admin" &&
        password === "1234"
    ){

        alert(
            "Admin Login Successful!"
        );

        document.getElementById(
            "admin-panel"
        ).style.display = "block";

        document.getElementById(
            "login-modal"
        ).style.display = "none";

    }
    else{

        alert(
            "Wrong username or password!"
        );

    }

}

// ===============================
// OPEN LOGIN MODAL
// ===============================

function openLogin(){

    document.getElementById(
        "login-modal"
    ).style.display = "flex";

}

// ===============================
// CLOSE LOGIN MODAL
// ===============================

function closeLogin(){

    document.getElementById(
        "login-modal"
    ).style.display = "none";

}

// ===============================
// NOTIFICATION SYSTEM
// ===============================

function showNotification(message){

    const notification =
    document.createElement("div");

    notification.className =
    "notification";

    notification.innerText =
    message;

    document.body.appendChild(
        notification
    );

    setTimeout(()=>{

        notification.classList.add(
            "show"
        );

    },100);

    setTimeout(()=>{

        notification.classList.remove(
            "show"
        );

        setTimeout(()=>{

            notification.remove();

        },300);

    },2500);

}

// ===============================
// AUTO HERO TEXT SLIDER
// ===============================

const heroTexts = [

    "Rare • Exotic • Premium Aquarium Fish",

    "Healthy Fish Delivered Safely",

    "Build Your Dream Aquarium"

];

let heroIndex = 0;

const heroParagraph =
document.querySelector(".hero p");

if(heroParagraph){

    setInterval(()=>{

        heroIndex++;

        if(heroIndex >= heroTexts.length){

            heroIndex = 0;

        }

        heroParagraph.style.opacity = 0;

        setTimeout(()=>{

            heroParagraph.innerText =
            heroTexts[heroIndex];

            heroParagraph.style.opacity = 1;

        },400);

    },3500);

}

// ===============================
// SMOOTH FADE-IN ANIMATION
// ===============================

const observer =
new IntersectionObserver(entries=>{

    entries.forEach(entry=>{

        if(entry.isIntersecting){

            entry.target.classList.add(
                "show-section"
            );

        }

    });

},{
    threshold:0.15
});

document.querySelectorAll(
    ".feature-card, .product-card, .contact-card, .about-container"
).forEach(el=>{

    el.classList.add("hidden-section");

    observer.observe(el);

});

// ===============================
// BACK TO TOP BUTTON
// ===============================

const topBtn =
document.createElement("button");

topBtn.innerHTML =
"↑";

topBtn.id =
"topBtn";

document.body.appendChild(topBtn);

window.addEventListener(
    "scroll",
    ()=>{

        if(window.scrollY > 400){

            topBtn.style.display =
            "block";

        }
        else{

            topBtn.style.display =
            "none";

        }

    }
);

topBtn.onclick = function(){

    window.scrollTo({

        top:0,
        behavior:"smooth"

    });

};

// ===============================
// EXTRA STYLES FROM JS
// ===============================

const style =
document.createElement("style");

style.innerHTML = `

.notification{

    position:fixed;

    bottom:30px;
    right:30px;

    background:#00a8e8;

    color:white;

    padding:14px 22px;

    border-radius:14px;

    box-shadow:
    0 10px 25px rgba(0,0,0,0.2);

    opacity:0;

    transform:translateY(20px);

    transition:0.3s;

    z-index:99999;

}

.notification.show{

    opacity:1;

    transform:translateY(0);

}

.hidden-section{

    opacity:0;

    transform:translateY(50px);

    transition:1s;

}

.show-section{

    opacity:1;

    transform:translateY(0);

}

#topBtn{

    position:fixed;

    right:25px;
    bottom:25px;

    width:50px;
    height:50px;

    border:none;

    border-radius:50%;

    background:#00a8e8;

    color:white;

    font-size:22px;

    cursor:pointer;

    display:none;

    z-index:9999;

    box-shadow:
    0 5px 20px rgba(0,0,0,0.2);

}

/* LOGIN MODAL */

#login-modal{

    position:fixed;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:rgba(0,0,0,0.7);

    display:none;

    justify-content:center;
    align-items:center;

    z-index:999999;

}

.login-box{

    background:white;

    padding:35px;

    width:350px;

    border-radius:20px;

    text-align:center;

}

.login-box input{

    width:100%;

    padding:12px;

    margin-top:15px;

    border:1px solid #ccc;

    border-radius:10px;

}

.login-box button{

    width:100%;

    padding:12px;

    margin-top:15px;

    border:none;

    border-radius:10px;

    background:#00a8e8;

    color:white;

    cursor:pointer;

}

#admin-panel{

    display:none;

    margin-top:20px;

    padding:20px;

    background:#dff6ff;

    border-radius:15px;

}

`;

document.head.appendChild(style);