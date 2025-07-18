<?php
// CORS設定
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

// プリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

// Content-Type設定
header('Content-Type: application/json');

// データベース接続設定
$host = 'database';
$dbname = 'blog_db';
$username = 'blog_user';
$password = 'blog_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// リクエストメソッドとパスの取得
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// APIルーティング
switch ($path) {
    case '/api/register':
        if ($method === 'POST') {
            handleRegister($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/login':
        if ($method === 'POST') {
            handleLogin($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/logout':
        if ($method === 'POST') {
            handleLogout($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/user':
        if ($method === 'GET') {
            handleGetUser($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;
        
    case '/api/test':
        echo json_encode([
            'message' => 'API is working',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $method,
            'path' => $path
        ]);
        break;
        
    default:
        echo json_encode([
            'message' => 'Blog API Server',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'available_endpoints' => [
                'POST /api/register',
                'POST /api/login', 
                'POST /api/logout',
                'GET /api/user',
                'GET /api/test'
            ]
        ]);
        break;
}

// ユーザー登録処理
function handleRegister($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // バリデーション
    $errors = [];
    if (empty($input['username'])) $errors['username'] = ['ユーザー名は必須です'];
    if (empty($input['email'])) $errors['email'] = ['メールアドレスは必須です'];
    if (empty($input['password'])) $errors['password'] = ['パスワードは必須です'];
    if (strlen($input['password'] ?? '') < 8) $errors['password'] = ['パスワードは8文字以上である必要があります'];
    if ($input['password'] !== $input['password_confirmation']) $errors['password_confirmation'] = ['パスワード確認が一致しません'];
    
    // メール・ユーザー名の重複チェック
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$input['email'], $input['username']]);
    if ($stmt->fetch()) {
        $errors['email'] = ['このメールアドレスまたはユーザー名は既に使用されています'];
    }
    
    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['message' => '入力内容に誤りがあります。', 'errors' => $errors]);
        return;
    }
    
    // ユーザー作成
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    $role = $input['role'] ?? 'user';
    
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$input['username'], $input['email'], $hashedPassword, $role]);
    
    $userId = $pdo->lastInsertId();
    
    // ユーザー情報取得
    $stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // トークン生成
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, created_at, updated_at) VALUES ('App\\\\Models\\\\User', ?, 'auth_token', ?, NOW(), NOW())");
    $stmt->execute([$userId, hash('sha256', $token)]);
    
    http_response_code(201);
    echo json_encode([
        'message' => 'ユーザー登録が完了しました。',
        'user' => $user,
        'token' => $token
    ]);
}

// ログイン処理
function handleLogin($pdo) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // バリデーション
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(422);
        echo json_encode(['message' => 'メールアドレスとパスワードは必須です。']);
        return;
    }
    
    // ユーザー認証
    $stmt = $pdo->prepare("SELECT id, username, email, password, role, created_at FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($input['password'], $user['password'])) {
        http_response_code(401);
        echo json_encode(['message' => 'メールアドレスまたはパスワードが正しくありません。']);
        return;
    }
    
    // パスワードを除去
    unset($user['password']);
    
    // トークン生成
    $token = bin2hex(random_bytes(32));
    $stmt = $pdo->prepare("INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, created_at, updated_at) VALUES ('App\\\\Models\\\\User', ?, 'auth_token', ?, NOW(), NOW())");
    $stmt->execute([$user['id'], hash('sha256', $token)]);
    
    echo json_encode([
        'message' => 'ログインしました。',
        'user' => $user,
        'token' => $token
    ]);
}

// ログアウト処理
function handleLogout($pdo) {
    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);
        return;
    }
    
    // トークン削除
    $stmt = $pdo->prepare("DELETE FROM personal_access_tokens WHERE token = ?");
    $stmt->execute([hash('sha256', $token)]);
    
    echo json_encode(['message' => 'ログアウトしました。']);
}

// 認証済みユーザー情報取得
function handleGetUser($pdo) {
    $token = getBearerToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);
        return;
    }
    
    // トークンからユーザー取得
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role, u.created_at 
        FROM users u 
        JOIN personal_access_tokens t ON u.id = t.tokenable_id 
        WHERE t.token = ? AND t.tokenable_type = 'App\\\\Models\\\\User'
    ");
    $stmt->execute([hash('sha256', $token)]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);
        return;
    }
    
    echo json_encode(['user' => $user]);
}

// Bearerトークン取得
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

// データベーステーブル作成（初回実行時）
function createTables($pdo) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) UNIQUE NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('user', 'admin') DEFAULT 'user',
            profile_image_url VARCHAR(255) NULL,
            bio TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personal_access_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            tokenable_type VARCHAR(255) NOT NULL,
            tokenable_id INT NOT NULL,
            name VARCHAR(255) NOT NULL,
            token VARCHAR(64) UNIQUE NOT NULL,
            abilities TEXT NULL,
            last_used_at TIMESTAMP NULL,
            expires_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
}

// テーブル作成を実行
createTables($pdo);