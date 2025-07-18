<?php

/**
 * 管理者ユーザー作成スクリプト
 *
 * 使用方法: php create_admin.php
 */
echo "管理者ユーザー作成スクリプトを実行します...\n\n";

// データベース接続設定
$host = 'database';
$dbname = 'blog_db';
$username = 'blog_user';
$password = 'blog_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ データベースに接続しました\n";

    // 既存の管理者チェック
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE role = 'admin'");
    $stmt->execute();
    $existingAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (! empty($existingAdmins)) {
        echo "⚠️  既存の管理者ユーザーが見つかりました:\n";
        foreach ($existingAdmins as $admin) {
            echo "   - ID: {$admin['id']}, ユーザー名: {$admin['username']}, メール: {$admin['email']}\n";
        }
        echo "\n新しい管理者を作成しますか？ (y/N): ";
        $answer = trim(fgets(STDIN));
        if (strtolower($answer) !== 'y') {
            echo "管理者作成をキャンセルしました。\n";
            exit(0);
        }
    }

    // 管理者ユーザー作成
    $adminUsers = [
        [
            'username' => 'admin',
            'email' => 'admin@blog.local',
            'password' => 'admin123456',
            'role' => 'admin',
        ],
        [
            'username' => 'manager',
            'email' => 'manager@blog.local',
            'password' => 'manager123456',
            'role' => 'admin',
        ],
    ];

    foreach ($adminUsers as $adminData) {
        // 重複チェック
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$adminData['email'], $adminData['username']]);

        if ($stmt->fetch()) {
            echo "⚠️  {$adminData['username']} は既に存在するためスキップします\n";

            continue;
        }

        // ユーザー作成
        $hashedPassword = password_hash($adminData['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('
            INSERT INTO users (username, email, password, role, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ');

        $stmt->execute([
            $adminData['username'],
            $adminData['email'],
            $hashedPassword,
            $adminData['role'],
        ]);

        echo "✅ 管理者ユーザーを作成しました:\n";
        echo "   - ユーザー名: {$adminData['username']}\n";
        echo "   - メールアドレス: {$adminData['email']}\n";
        echo "   - パスワード: {$adminData['password']}\n";
        echo "   - ロール: 管理者\n\n";
    }

    echo "🎉 管理者ユーザーの作成が完了しました！\n\n";
    echo "ログイン方法:\n";
    echo "1. http://localhost にアクセス\n";
    echo "2. 上記の管理者アカウントでログイン\n";
    echo "3. 管理者ダッシュボードが表示されます\n";

} catch (PDOException $e) {
    echo '❌ データベースエラー: '.$e->getMessage()."\n";
    exit(1);
} catch (Exception $e) {
    echo '❌ エラー: '.$e->getMessage()."\n";
    exit(1);
}
