<?php

// CORS設定
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// プリフライトリクエストへの対応
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
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

    case '/api/articles':
        if ($method === 'GET') {
            handleGetArticles($pdo);
        } elseif ($method === 'POST') {
            handleCreateArticle($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/articles/published':
        if ($method === 'GET') {
            handleGetPublishedArticles($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case preg_match('/^\/api\/articles\/(\d+)$/', $path, $matches) ? true : false:
        $articleId = $matches[1];
        if ($method === 'GET') {
            handleGetArticle($pdo, $articleId);
        } elseif ($method === 'PUT') {
            handleUpdateArticle($pdo, $articleId);
        } elseif ($method === 'DELETE') {
            handleDeleteArticle($pdo, $articleId);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/categories':
        if ($method === 'GET') {
            handleGetCategories($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/articles/search':
        if ($method === 'GET') {
            handleSearchArticles($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/purchases':
        if ($method === 'POST') {
            handlePurchaseArticle($pdo);
        } elseif ($method === 'GET') {
            handleGetPurchases($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case '/api/purchases/status':
        if ($method === 'POST') {
            handleGetMultiplePurchaseStatus($pdo);
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
        }
        break;

    case preg_match('/^\/api\/articles\/(\d+)\/purchase-status$/', $path, $matches) ? true : false:
        $articleId = $matches[1];
        if ($method === 'GET') {
            handleGetPurchaseStatus($pdo, $articleId);
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
            'path' => $path,
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
                'GET /api/articles',
                'POST /api/articles',
                'GET /api/articles/published',
                'GET /api/articles/{id}',
                'PUT /api/articles/{id}',
                'DELETE /api/articles/{id}',
                'GET /api/categories',
                'GET /api/articles/search',
                'POST /api/purchases',
                'GET /api/purchases',
                'POST /api/purchases/status',
                'GET /api/articles/{id}/purchase-status',
                'GET /api/test',
            ],
        ]);
        break;
}

// ユーザー登録処理
function handleRegister($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    $errors = [];
    if (empty($input['username'])) {
        $errors['username'] = ['ユーザー名は必須です'];
    }
    if (empty($input['email'])) {
        $errors['email'] = ['メールアドレスは必須です'];
    }
    if (empty($input['password'])) {
        $errors['password'] = ['パスワードは必須です'];
    }
    if (strlen($input['password'] ?? '') < 8) {
        $errors['password'] = ['パスワードは8文字以上である必要があります'];
    }
    if ($input['password'] !== $input['password_confirmation']) {
        $errors['password_confirmation'] = ['パスワード確認が一致しません'];
    }

    // メール・ユーザー名の重複チェック
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$input['email'], $input['username']]);
    if ($stmt->fetch()) {
        $errors['email'] = ['このメールアドレスまたはユーザー名は既に使用されています'];
    }

    if (! empty($errors)) {
        http_response_code(422);
        echo json_encode(['message' => '入力内容に誤りがあります。', 'errors' => $errors]);

        return;
    }

    // ユーザー作成
    $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
    $role = $input['role'] ?? 'user';

    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())');
    $stmt->execute([$input['username'], $input['email'], $hashedPassword, $role]);

    $userId = $pdo->lastInsertId();

    // ユーザー情報取得
    $stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = ?');
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
        'token' => $token,
    ]);
}

// ログイン処理
function handleLogin($pdo)
{
    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    if (empty($input['email']) || empty($input['password'])) {
        http_response_code(422);
        echo json_encode(['message' => 'メールアドレスとパスワードは必須です。']);

        return;
    }

    // ユーザー認証
    $stmt = $pdo->prepare('SELECT id, username, email, password, role, created_at FROM users WHERE email = ?');
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $user || ! password_verify($input['password'], $user['password'])) {
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
        'token' => $token,
    ]);
}

// ログアウト処理
function handleLogout($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    // トークン削除
    $stmt = $pdo->prepare('DELETE FROM personal_access_tokens WHERE token = ?');
    $stmt->execute([hash('sha256', $token)]);

    echo json_encode(['message' => 'ログアウトしました。']);
}

// 認証済みユーザー情報取得
function handleGetUser($pdo)
{
    $token = getBearerToken();
    if (! $token) {
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

    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    echo json_encode(['user' => $user]);
}

// Bearerトークン取得
function getBearerToken()
{
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }

    return null;
}

// 記事取得処理（一覧）
function handleGetArticles($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    // 管理者は全記事、一般ユーザーは自分の記事のみ取得
    if ($user['role'] === 'admin') {
        $stmt = $pdo->prepare('
            SELECT a.*, u.username as author_username, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.created_at DESC
        ');
        $stmt->execute();
    } else {
        $stmt = $pdo->prepare('
            SELECT a.*, u.username as author_username, c.name as category_name
            FROM articles a
            LEFT JOIN users u ON a.user_id = u.id
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC
        ');
        $stmt->execute([$user['id']]);
    }

    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['articles' => $articles]);
}

// 記事取得処理（詳細）
function handleGetArticle($pdo, $articleId)
{
    $stmt = $pdo->prepare('
        SELECT a.*, u.username as author_username, c.name as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
    ');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $article) {
        http_response_code(404);
        echo json_encode(['message' => '記事が見つかりません。']);

        return;
    }

    echo json_encode(['article' => $article]);
}

// 記事作成処理
function handleCreateArticle($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    $errors = [];
    if (empty($input['title'])) {
        $errors['title'] = ['タイトルは必須です'];
    }
    if (empty($input['content'])) {
        $errors['content'] = ['内容は必須です'];
    }
    if (empty($input['category_id'])) {
        $errors['category_id'] = ['カテゴリは必須です'];
    }

    if (! empty($errors)) {
        http_response_code(422);
        echo json_encode(['message' => '入力内容に誤りがあります。', 'errors' => $errors]);

        return;
    }

    // 記事作成
    $stmt = $pdo->prepare('
        INSERT INTO articles (user_id, title, content, category_id, status, is_premium, price, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ');
    $stmt->execute([
        $user['id'],
        $input['title'],
        $input['content'],
        $input['category_id'],
        $input['status'] ?? 'draft',
        $input['is_premium'] ?? 0,
        $input['price'] ?? 0,
    ]);

    $articleId = $pdo->lastInsertId();

    // 作成した記事を取得
    $stmt = $pdo->prepare('
        SELECT a.*, u.username as author_username, c.name as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
    ');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode([
        'message' => '記事が作成されました。',
        'article' => $article,
    ]);
}

// 記事更新処理
function handleUpdateArticle($pdo, $articleId)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    // 記事の存在確認と権限チェック
    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $article) {
        http_response_code(404);
        echo json_encode(['message' => '記事が見つかりません。']);

        return;
    }

    // 管理者または記事の作成者のみ編集可能
    if ($user['role'] !== 'admin' && $article['user_id'] != $user['id']) {
        http_response_code(403);
        echo json_encode(['message' => 'この記事を編集する権限がありません。']);

        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    $errors = [];
    if (empty($input['title'])) {
        $errors['title'] = ['タイトルは必須です'];
    }
    if (empty($input['content'])) {
        $errors['content'] = ['内容は必須です'];
    }
    if (empty($input['category_id'])) {
        $errors['category_id'] = ['カテゴリは必須です'];
    }

    if (! empty($errors)) {
        http_response_code(422);
        echo json_encode(['message' => '入力内容に誤りがあります。', 'errors' => $errors]);

        return;
    }

    // 記事更新
    $stmt = $pdo->prepare('
        UPDATE articles 
        SET title = ?, content = ?, category_id = ?, status = ?, is_premium = ?, price = ?, updated_at = NOW()
        WHERE id = ?
    ');
    $stmt->execute([
        $input['title'],
        $input['content'],
        $input['category_id'],
        $input['status'] ?? $article['status'],
        $input['is_premium'] ?? $article['is_premium'],
        $input['price'] ?? $article['price'],
        $articleId,
    ]);

    // 更新した記事を取得
    $stmt = $pdo->prepare('
        SELECT a.*, u.username as author_username, c.name as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.id = ?
    ');
    $stmt->execute([$articleId]);
    $updatedArticle = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'message' => '記事が更新されました。',
        'article' => $updatedArticle,
    ]);
}

// 記事削除処理
function handleDeleteArticle($pdo, $articleId)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    // 記事の存在確認と権限チェック
    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $article) {
        http_response_code(404);
        echo json_encode(['message' => '記事が見つかりません。']);

        return;
    }

    // 管理者または記事の作成者のみ削除可能
    if ($user['role'] !== 'admin' && $article['user_id'] != $user['id']) {
        http_response_code(403);
        echo json_encode(['message' => 'この記事を削除する権限がありません。']);

        return;
    }

    // 記事削除
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
    $stmt->execute([$articleId]);

    echo json_encode(['message' => '記事が削除されました。']);
}

// 公開記事一覧取得（認証不要）
function handleGetPublishedArticles($pdo)
{
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;

    // 総記事数を取得
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM articles a
        WHERE a.status = 'published'
    ");
    $countStmt->execute();
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 記事一覧を取得（ページネーション）
    $stmt = $pdo->prepare("
        SELECT a.*, u.username as author_username, c.name as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'articles' => $articles,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalCount,
            'total_pages' => ceil($totalCount / $limit),
            'has_more' => $page < ceil($totalCount / $limit),
        ],
    ]);
}

// カテゴリ一覧取得
function handleGetCategories($pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM categories ORDER BY name ASC');
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['categories' => $categories]);
}

// 記事検索機能
function handleSearchArticles($pdo)
{
    $searchQuery = $_GET['q'] ?? '';
    $categoryId = $_GET['category'] ?? '';
    $status = $_GET['status'] ?? 'published';
    $freeOnly = isset($_GET['free_only']) && $_GET['free_only'] === '1';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, min(50, intval($_GET['limit']))) : 10;
    $offset = ($page - 1) * $limit;

    // 基本のクエリ（カウント用）
    $countQuery = '
        SELECT COUNT(*) as total
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = ?
    ';

    // 基本のクエリ（結果取得用）
    $query = '
        SELECT a.*, u.username as author_username, c.name as category_name
        FROM articles a
        LEFT JOIN users u ON a.user_id = u.id
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = ?
    ';

    $params = [$status];

    // 検索クエリがある場合のフィルタリング
    if (! empty($searchQuery)) {
        $countQuery .= ' AND (a.title LIKE ? OR a.content LIKE ? OR u.username LIKE ?)';
        $query .= ' AND (a.title LIKE ? OR a.content LIKE ? OR u.username LIKE ?)';
        $searchParam = "%{$searchQuery}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // カテゴリフィルタリング
    if (! empty($categoryId)) {
        $countQuery .= ' AND a.category_id = ?';
        $query .= ' AND a.category_id = ?';
        $params[] = $categoryId;
    }

    // 無料記事のみフィルタリング
    if ($freeOnly) {
        $countQuery .= ' AND a.is_premium = 0';
        $query .= ' AND a.is_premium = 0';
    }

    // 総件数を取得
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 結果を取得（ページネーション）
    $query .= " ORDER BY a.created_at DESC LIMIT {$limit} OFFSET {$offset}";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'articles' => $articles,
        'search_query' => $searchQuery,
        'category_id' => $categoryId,
        'free_only' => $freeOnly,
        'pagination' => [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalCount,
            'total_pages' => ceil($totalCount / $limit),
            'has_more' => $page < ceil($totalCount / $limit),
        ],
    ]);
}

// トークンからユーザー情報を取得
function getUserFromToken($pdo, $token)
{
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.email, u.role, u.created_at 
        FROM users u 
        JOIN personal_access_tokens t ON u.id = t.tokenable_id 
        WHERE t.token = ? AND t.tokenable_type = 'App\\\\Models\\\\User'
    ");
    $stmt->execute([hash('sha256', $token)]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// 記事購入処理
function handlePurchaseArticle($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // バリデーション
    if (empty($input['article_id'])) {
        http_response_code(422);
        echo json_encode(['message' => '記事IDは必須です。']);

        return;
    }

    $articleId = $input['article_id'];

    // 記事の存在確認
    $stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ? AND status = "published"');
    $stmt->execute([$articleId]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    if (! $article) {
        http_response_code(404);
        echo json_encode(['message' => '記事が見つかりません。']);

        return;
    }

    // 有料記事でない場合はエラー
    if (! $article['is_premium']) {
        http_response_code(422);
        echo json_encode(['message' => 'この記事は無料記事です。']);

        return;
    }

    // 既に購入済みかチェック
    $stmt = $pdo->prepare('SELECT id FROM purchases WHERE user_id = ? AND article_id = ?');
    $stmt->execute([$user['id'], $articleId]);
    if ($stmt->fetch()) {
        http_response_code(422);
        echo json_encode(['message' => 'この記事は既に購入済みです。']);

        return;
    }

    // 購入処理
    $stmt = $pdo->prepare('
        INSERT INTO purchases (user_id, article_id, purchase_date, payment_method, amount, status, created_at, updated_at)
        VALUES (?, ?, NOW(), "mock_payment", ?, "completed", NOW(), NOW())
    ');
    $stmt->execute([$user['id'], $articleId, $article['price']]);

    $purchaseId = $pdo->lastInsertId();

    // 購入情報を取得
    $stmt = $pdo->prepare('
        SELECT p.*, a.title as article_title, a.price as article_price, u.username as buyer_username
        FROM purchases p
        LEFT JOIN articles a ON p.article_id = a.id
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.id = ?
    ');
    $stmt->execute([$purchaseId]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(201);
    echo json_encode([
        'message' => '記事を購入しました。',
        'purchase' => $purchase,
    ]);
}

// 購入履歴取得
function handleGetPurchases($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    $stmt = $pdo->prepare('
        SELECT p.*, a.title as article_title, a.price as article_price, u.username as author_username
        FROM purchases p
        LEFT JOIN articles a ON p.article_id = a.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE p.user_id = ?
        ORDER BY p.created_at DESC
    ');
    $stmt->execute([$user['id']]);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['purchases' => $purchases]);
}

// 購入状態確認
function handleGetPurchaseStatus($pdo, $articleId)
{
    $token = getBearerToken();
    if (! $token) {
        http_response_code(401);
        echo json_encode(['message' => '認証が必要です。']);

        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        http_response_code(401);
        echo json_encode(['message' => '無効なトークンです。']);

        return;
    }

    // 購入済みかチェック
    $stmt = $pdo->prepare('SELECT * FROM purchases WHERE user_id = ? AND article_id = ?');
    $stmt->execute([$user['id'], $articleId]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'purchased' => (bool) $purchase,
        'purchase_info' => $purchase,
    ]);
}

// 複数記事の購入状態確認
function handleGetMultiplePurchaseStatus($pdo)
{
    $token = getBearerToken();
    if (! $token) {
        // 未ログインの場合は空の購入状態を返す
        echo json_encode(['purchase_status' => []]);
        return;
    }

    $user = getUserFromToken($pdo, $token);
    if (! $user) {
        echo json_encode(['purchase_status' => []]);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $articleIds = $input['article_ids'] ?? [];

    if (empty($articleIds)) {
        echo json_encode(['purchase_status' => []]);
        return;
    }

    // 購入済み記事をまとめて取得
    $placeholders = str_repeat('?,', count($articleIds) - 1) . '?';
    $stmt = $pdo->prepare("
        SELECT article_id, purchase_date, amount, status
        FROM purchases 
        WHERE user_id = ? AND article_id IN ($placeholders)
    ");
    
    $params = array_merge([$user['id']], $articleIds);
    $stmt->execute($params);
    $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 記事IDをキーとした連想配列に変換
    $purchaseStatus = [];
    foreach ($purchases as $purchase) {
        $purchaseStatus[$purchase['article_id']] = [
            'purchased' => true,
            'purchase_date' => $purchase['purchase_date'],
            'amount' => $purchase['amount'],
            'status' => $purchase['status']
        ];
    }

    // 購入していない記事IDにはfalseを設定
    foreach ($articleIds as $articleId) {
        if (!isset($purchaseStatus[$articleId])) {
            $purchaseStatus[$articleId] = [
                'purchased' => false
            ];
        }
    }

    echo json_encode(['purchase_status' => $purchaseStatus]);
}

// データベーステーブル作成（初回実行時）
function createTables($pdo)
{
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

    $pdo->exec('
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
    ');

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) UNIQUE NOT NULL,
            description TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            category_id INT NULL,
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
            is_premium BOOLEAN DEFAULT FALSE,
            price DECIMAL(10,2) DEFAULT 0.00,
            view_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
        )
    ");

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            article_id INT NOT NULL,
            purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            payment_method VARCHAR(50) DEFAULT "mock_payment",
            amount DECIMAL(10,2) NOT NULL,
            status ENUM("completed", "pending", "cancelled") DEFAULT "completed",
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
            UNIQUE KEY unique_user_article (user_id, article_id)
        )
    ');

    // デフォルトカテゴリの作成
    $pdo->exec("
        INSERT IGNORE INTO categories (name, description) VALUES 
        ('テクノロジー', 'プログラミング、AI、ソフトウェア開発などの技術記事'),
        ('ビジネス', 'ビジネス戦略、マーケティング、起業などの記事'),
        ('ライフスタイル', '健康、趣味、日常生活に関する記事'),
        ('教育', '学習方法、スキルアップ、キャリア開発の記事'),
        ('エンターテイメント', '映画、音楽、ゲーム、アートに関する記事')
    ");
}

// テーブル作成を実行
createTables($pdo);
