<?php
header('Content-Type: application/json');
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// GET - Retrieve products
if ($method === 'GET') {
    if ($action === 'list') {
        $sql = "SELECT * FROM products ORDER BY created_at DESC";
        $result = $conn->query($sql);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $products]);
    } elseif ($action === 'get' && isset($_GET['id'])) {
        $product_id = intval($_GET['id']);
        $sql = "SELECT * FROM products WHERE product_id = $product_id";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
        }
    }
}

// POST - Create product
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $name = $conn->real_escape_string($data['name'] ?? '');
    $description = $conn->real_escape_string($data['description'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock_quantity = intval($data['stock_quantity'] ?? 0);
    
    if (empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $sql = "INSERT INTO products (name, description, price, stock_quantity) 
            VALUES ('$name', '$description', $price, $stock_quantity)";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Product created', 'product_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating product']);
    }
}

// PUT - Update product
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $product_id = intval($data['product_id'] ?? 0);
    $name = $conn->real_escape_string($data['name'] ?? '');
    $description = $conn->real_escape_string($data['description'] ?? '');
    $price = floatval($data['price'] ?? 0);
    $stock_quantity = intval($data['stock_quantity'] ?? 0);
    
    if ($product_id <= 0 || empty($name) || $price <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid input']);
        exit;
    }
    
    $sql = "UPDATE products SET name='$name', description='$description', price=$price, stock_quantity=$stock_quantity 
            WHERE product_id=$product_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Product updated']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating product']);
    }
}

// DELETE - Delete product
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $product_id = intval($data['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        exit;
    }
    
    $sql = "DELETE FROM products WHERE product_id=$product_id";
    
    if ($conn->query($sql)) {
        echo json_encode(['success' => true, 'message' => 'Product deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting product']);
    }
}

$conn->close();
?>
