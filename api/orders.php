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

// GET - Retrieve user's orders
if ($method === 'GET' && $action === 'list') {
    $sql = "SELECT o.order_id, o.total_amount, o.status, o.created_at 
            FROM orders o 
            WHERE o.user_id = $user_id 
            ORDER BY o.created_at DESC";
    $result = $conn->query($sql);
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $orders]);
}

// GET - Retrieve order details with items
elseif ($method === 'GET' && $action === 'details' && isset($_GET['order_id'])) {
    $order_id = intval($_GET['order_id']);
    
    // Get order info
    $order_sql = "SELECT * FROM orders WHERE order_id = $order_id AND user_id = $user_id";
    $order_result = $conn->query($order_sql);
    
    if ($order_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    $order = $order_result->fetch_assoc();
    
    // Get order items
    $items_sql = "SELECT oi.*, p.name FROM order_items oi 
                  LEFT JOIN products p ON oi.product_id = p.product_id 
                  WHERE oi.order_id = $order_id";
    $items_result = $conn->query($items_sql);
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    
    echo json_encode(['success' => true, 'data' => $order]);
}

// POST - Create order from cart
elseif ($method === 'POST' && $action === 'create') {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get cart items
        $cart_sql = "SELECT c.product_id, c.quantity, p.price, p.stock_quantity 
                     FROM cart c 
                     JOIN products p ON c.product_id = p.product_id 
                     WHERE c.user_id = $user_id";
        $cart_result = $conn->query($cart_sql);
        
        if ($cart_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }
        
        $total_amount = 0;
        $order_items = [];
        
        // Validate stock and calculate total
        while ($item = $cart_result->fetch_assoc()) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception('Insufficient stock for product ID ' . $item['product_id']);
            }
            $item['subtotal'] = $item['price'] * $item['quantity'];
            $total_amount += $item['subtotal'];
            $order_items[] = $item;
        }
        
        // Create order
        $insert_order_sql = "INSERT INTO orders (user_id, total_amount, status) 
                             VALUES ($user_id, $total_amount, 'pending')";
        
        if (!$conn->query($insert_order_sql)) {
            throw new Exception('Error creating order');
        }
        
        $order_id = $conn->insert_id;
        
        // Insert order items and update stock
        foreach ($order_items as $item) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            
            // Insert order item
            $insert_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                                VALUES ($order_id, $product_id, $quantity, $price)";
            if (!$conn->query($insert_item_sql)) {
                throw new Exception('Error creating order item');
            }
            
            // Update product stock
            $update_stock_sql = "UPDATE products SET stock_quantity = stock_quantity - $quantity 
                                 WHERE product_id = $product_id";
            if (!$conn->query($update_stock_sql)) {
                throw new Exception('Error updating stock');
            }
        }
        
        // Clear cart
        $clear_cart_sql = "DELETE FROM cart WHERE user_id = $user_id";
        if (!$conn->query($clear_cart_sql)) {
            throw new Exception('Error clearing cart');
        }
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order created successfully',
            'order_id' => $order_id,
            'total_amount' => $total_amount
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

// PUT - Update order status (admin only)
elseif ($method === 'PUT' && $action === 'update_status') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $order_id = intval($data['order_id'] ?? 0);
    $status = $conn->real_escape_string($data['status'] ?? '');
    
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if ($order_id <= 0 || !in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $sql = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Order status updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating order']);
    }
}

$conn->close();
?>
