<?php
header('Content-Type: application/json');
require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// POST - Register user
if ($method === 'POST' && $action === 'register') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = $conn->real_escape_string($data['username'] ?? '');
    $email = $conn->real_escape_string($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }
    
    // Check if user exists
    $check_sql = "SELECT user_id FROM users WHERE username = '$username' OR email = '$email'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Insert user
    $insert_sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
    
    if ($conn->query($insert_sql)) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        echo json_encode([
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $conn->insert_id,
            'username' => $username
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error registering user']);
    }
}

// POST - Login user
elseif ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $username = $conn->real_escape_string($data['username'] ?? '');
    $password = $data['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username and password required']);
        exit;
    }
    
    $sql = "SELECT user_id, username, email, password FROM users WHERE username = '$username' OR email = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit;
    }
    
    // Set session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user['user_id'],
        'username' => $user['username']
    ]);
}

// POST - Logout user
elseif ($method === 'POST' && $action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout successful']);
}

// GET - Check if user is logged in
elseif ($method === 'GET' && $action === 'check') {
    if (isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => true,
            'logged_in' => true,
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email']
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'logged_in' => false
        ]);
    }
}

$conn->close();
?>
