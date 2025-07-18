import React, { useState } from 'react';
import ArticleForm from './ArticleForm';
import ArticleList from './ArticleList';

interface ArticleManagementProps {
  user: any;
  onBack: () => void;
}

const ArticleManagement: React.FC<ArticleManagementProps> = ({ user, onBack }) => {
  const [currentView, setCurrentView] = useState<'list' | 'create' | 'edit'>('list');
  const [editingArticle, setEditingArticle] = useState<any>(null);
  const [refreshTrigger, setRefreshTrigger] = useState(0);

  const handleCreateNew = () => {
    setEditingArticle(null);
    setCurrentView('create');
  };

  const handleEdit = (article: any) => {
    setEditingArticle(article);
    setCurrentView('edit');
  };

  const handleFormSuccess = () => {
    setCurrentView('list');
    setEditingArticle(null);
    setRefreshTrigger(prev => prev + 1); // リストを再読み込み
  };

  const handleCancel = () => {
    setCurrentView('list');
    setEditingArticle(null);
  };

  if (currentView === 'create' || currentView === 'edit') {
    return (
      <ArticleForm
        onSuccess={handleFormSuccess}
        onCancel={handleCancel}
        article={editingArticle}
        isEdit={currentView === 'edit'}
      />
    );
  }

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-6">
            <div className="flex items-center space-x-4">
              <button
                onClick={onBack}
                className="text-gray-400 hover:text-gray-600"
              >
                ← 戻る
              </button>
              <h1 className="text-3xl font-bold text-gray-900">
                記事管理
              </h1>
            </div>
            <div className="flex items-center space-x-4">
              <span className="text-sm text-gray-700">
                {user.username} さん
                <span className="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                  {user.role === 'admin' ? '管理者' : '投稿ユーザー'}
                </span>
              </span>
              <button
                onClick={handleCreateNew}
                className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                新しい記事を投稿
              </button>
            </div>
          </div>
        </div>
      </header>

      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          <div className="bg-white rounded-lg shadow p-6">
            <ArticleList
              onEdit={handleEdit}
              onRefresh={refreshTrigger}
            />
          </div>
        </div>
      </main>
    </div>
  );
};

export default ArticleManagement;