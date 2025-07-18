<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 管理者アカウントを作成
        $this->createAdminUser();
    }

    /**
     * 管理者ユーザーを作成
     */
    private function createAdminUser(): void
    {
        $host = 'database';
        $dbname = 'blog_db';
        $username = 'blog_user';
        $password = 'blog_password';

        try {
            $pdo = new \PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // 既存の管理者チェック
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR role = 'admin' LIMIT 1");
            $stmt->execute(['admin@blog.local']);
            
            if ($stmt->fetch()) {
                echo "管理者ユーザーは既に存在します。\n";
                return;
            }

            // 管理者ユーザー作成
            $adminData = [
                'username' => 'admin',
                'email' => 'admin@blog.local',
                'password' => password_hash('admin123456', PASSWORD_DEFAULT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, role, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $adminData['username'],
                $adminData['email'],
                $adminData['password'],
                $adminData['role'],
                $adminData['created_at'],
                $adminData['updated_at']
            ]);

            echo "管理者ユーザーを作成しました:\n";
            echo "- ユーザー名: admin\n";
            echo "- メールアドレス: admin@blog.local\n";
            echo "- パスワード: admin123456\n";
            echo "- ロール: 管理者\n";

        } catch (\PDOException $e) {
            echo "エラー: " . $e->getMessage() . "\n";
        }
    }
}