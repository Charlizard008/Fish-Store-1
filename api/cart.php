<?php
header('Content-Type: application/json');
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];

// GET - Retrieve cart items
if ($method === 'GET' && $action === 'list') {
    $sql = "SELECT c.cart_id, c.product_id, c.quantity, p.name, p.price, p.stock_quantity 
            FROM cart c 
            JOIN products p ON c.product_id = p.product_id 
            WHERE c.user_id = $user_id";
    $result = $conn->query($sql);
    
    $cart_items = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $row['subtotal'] = $row['price'] * $row['quantity'];
        $total += $row['subtotal'];
        $cart_items[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $cart_items,
        'total' => $total,
        'item_count' => count($cart_items)
    ]);
}

// POST - Add item to cart
elseif ($method === 'POST' && $action === 'add') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $product_id = intval($data['product_id'] ?? 0);
    $quantity = intval($data['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    // Check if product exists and has stock
    $check_sql = "SELECT stock_quantity FROM products WHERE product_id = $product_id";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }
    
    $product = $check_result->fetch_assoc();
    if ($product['stock_quantity'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }
    
    // Check if item already in cart
    $exist_sql = "SELECT cart_id, quantity FROM cart WHERE user_id = $user_id AND product_id = $product_id";
    $exist_result = $conn->query($exist_sql);
    
    if ($exist_result->num_rows > 0) {
        // Update quantity
        $cart_item = $exist_result->fetch_assoc();
        $new_quantity = $cart_item['quantity'] + $quantity;
        $update_sql = "UPDATE cart SET quantity = $new_quantity WHERE cart_id = " . $cart_item['cart_id'];
        $conn->query($update_sql);
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        // Insert new item
        $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)";
        if ($conn->query($insert_sql)) {
            echo json_encode(['success' => true, 'message' => 'Item added to cart']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error adding to cart']);
        }
    }
}

// PUT - Update cart item quantity
elseif ($method === 'PUT' && $action === 'update') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $cart_id = intval($data['cart_id'] ?? 0);
    $quantity = intval($data['quantity'] ?? 0);
    
    if ($cart_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $sql = "UPDATE cart SET quantity = $quantity WHERE cart_id = $cart_id AND user_id = $user_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Cart updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating cart']);
    }
}

// DELETE - Remove item from cart
elseif ($method === 'DELETE' && $action === 'remove') {
    $data = json_decode(file_get_contents("php://input"), true);
    $cart_id = intval($data['cart_id'] ?? 0);
    
    if ($cart_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid cart ID']);
        exit;
    }
    
    $sql = "DELETE FROM cart WHERE cart_id = $cart_id AND user_id = $user_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing item']);
    }
}

// DELETE - Clear entire cart
elseif ($method === 'DELETE' && $action === 'clear') {
    $sql = "DELETE FROM cart WHERE user_id = $user_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error clearing cart']);
    }
}

$conn->close();
?>
