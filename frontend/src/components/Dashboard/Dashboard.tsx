import React from 'react';

interface DashboardProps {
  user: any;
  onLogout: () => void;
}

const Dashboard: React.FC<DashboardProps> = ({ user, onLogout }) => {
  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    onLogout();
  };

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <h1 className="text-3xl font-bold text-gray-900">
              {user.role === 'admin' ? '管理者ダッシュボード' : 'ユーザーダッシュボード'}
            </h1>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-700">
                {user.username} さん
                <span className="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                  {user.role === 'admin' ? '管理者' : '投稿ユーザー'}
                </span>
              </span>
              <button
                onClick={handleLogout}
                className="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
              >
                ログアウト
              </button>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {/* ユーザー情報カード */}
            <div className="bg-white rounded-lg shadow p-6">
              <h3 className="text-lg font-semibold text-gray-900 mb-4">ユーザー情報</h3>
              <div className="space-y-2">
                <p><span className="font-medium">ユーザー名:</span> {user.username}</p>
                <p><span className="font-medium">メールアドレス:</span> {user.email}</p>
                <p><span className="font-medium">ユーザータイプ:</span> {user.role === 'admin' ? '管理者' : '投稿ユーザー'}</p>
                <p><span className="font-medium">登録日:</span> {new Date(user.created_at).toLocaleDateString('ja-JP')}</p>
              </div>
            </div>

            {/* 機能カード */}
            {user.role === 'admin' ? (
              <>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">ユーザー管理</h3>
                  <p className="text-gray-600 mb-4">登録ユーザーの管理を行います</p>
                  <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" disabled>
                    ユーザー一覧 (開発中)
                  </button>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">記事管理</h3>
                  <p className="text-gray-600 mb-4">全記事の管理を行います</p>
                  <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" disabled>
                    記事一覧 (開発中)
                  </button>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">収益管理</h3>
                  <p className="text-gray-600 mb-4">サイト収益の管理を行います</p>
                  <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" disabled>
                    収益確認 (開発中)
                  </button>
                </div>
              </>
            ) : (
              <>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">記事投稿</h3>
                  <p className="text-gray-600 mb-4">新しい記事を投稿します</p>
                  <button className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50" disabled>
                    記事投稿 (開発中)
                  </button>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">記事管理</h3>
                  <p className="text-gray-600 mb-4">自分の記事を管理します</p>
                  <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" disabled>
                    記事一覧 (開発中)
                  </button>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">収益状況</h3>
                  <p className="text-gray-600 mb-4">収益の確認を行います</p>
                  <button className="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50" disabled>
                    収益確認 (開発中)
                  </button>
                </div>
              </>
            )}
          </div>

          {/* 認証成功メッセージ */}
          <div className="mt-8 bg-green-50 border border-green-200 rounded-lg p-4">
            <div className="flex">
              <div className="flex-shrink-0">
                <svg className="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                </svg>
              </div>
              <div className="ml-3">
                <h3 className="text-sm font-medium text-green-800">
                  🎉 ユーザー認証機能が正常に動作しています！
                </h3>
                <div className="mt-2 text-sm text-green-700">
                  <p>ユーザー登録・ログイン・ログアウト機能が実装されました。</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Dashboard;