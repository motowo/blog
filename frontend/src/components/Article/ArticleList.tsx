import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface Article {
  id: number;
  title: string;
  content: string;
  status: string;
  is_premium: boolean;
  price: number;
  category_name: string;
  author_username: string;
  created_at: string;
  updated_at: string;
}

interface ArticleListProps {
  onEdit: (article: Article) => void;
  onRefresh: number;
}

const ArticleList: React.FC<ArticleListProps> = ({ onEdit, onRefresh }) => {
  const [articles, setArticles] = useState<Article[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string>('');

  useEffect(() => {
    fetchArticles();
  }, [onRefresh]);

  const fetchArticles = async () => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem('token');
      const response = await axios.get('http://localhost:8000/api/articles', {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      setArticles(response.data.articles);
      setError('');
    } catch (error: any) {
      setError(error.response?.data?.message || '記事の取得に失敗しました');
    } finally {
      setIsLoading(false);
    }
  };

  const handleDelete = async (articleId: number) => {
    if (!confirm('この記事を削除してもよろしいですか？')) {
      return;
    }

    try {
      const token = localStorage.getItem('token');
      await axios.delete(`http://localhost:8000/api/articles/${articleId}`, {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      });
      
      // 記事リストから削除
      setArticles(prev => prev.filter(article => article.id !== articleId));
    } catch (error: any) {
      alert(error.response?.data?.message || '記事の削除に失敗しました');
    }
  };

  const getStatusLabel = (status: string) => {
    switch (status) {
      case 'draft': return '下書き';
      case 'published': return '公開';
      case 'archived': return 'アーカイブ';
      default: return status;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'draft': return 'bg-yellow-100 text-yellow-800';
      case 'published': return 'bg-green-100 text-green-800';
      case 'archived': return 'bg-gray-100 text-gray-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  if (isLoading) {
    return (
      <div className="text-center py-8">
        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p className="mt-2 text-gray-600">記事を読み込み中...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="text-center py-8">
        <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
          {error}
        </div>
        <button
          onClick={fetchArticles}
          className="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
        >
          再試行
        </button>
      </div>
    );
  }

  if (articles.length === 0) {
    return (
      <div className="text-center py-12">
        <p className="text-gray-600 text-lg">まだ記事がありません</p>
        <p className="text-gray-500 text-sm mt-2">新しい記事を投稿してみましょう！</p>
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <h3 className="text-lg font-semibold text-gray-900 mb-4">
        記事一覧 ({articles.length}件)
      </h3>
      
      {articles.map((article) => (
        <div key={article.id} className="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
          <div className="flex justify-between items-start mb-3">
            <div className="flex-1">
              <h4 className="text-xl font-semibold text-gray-900 mb-2">
                {article.title}
              </h4>
              <div className="flex items-center space-x-3 text-sm text-gray-600 mb-3">
                <span className={`px-2 py-1 rounded-full text-xs ${getStatusColor(article.status)}`}>
                  {getStatusLabel(article.status)}
                </span>
                <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                  {article.category_name}
                </span>
                {article.is_premium && (
                  <span className="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                    有料 ¥{article.price}
                  </span>
                )}
              </div>
              <p className="text-gray-700 mb-3">
                {article.content.length > 150 
                  ? `${article.content.substring(0, 150)}...` 
                  : article.content
                }
              </p>
              <div className="text-sm text-gray-500">
                作成: {new Date(article.created_at).toLocaleDateString('ja-JP')} | 
                更新: {new Date(article.updated_at).toLocaleDateString('ja-JP')}
              </div>
            </div>
            
            <div className="flex flex-col space-y-2 ml-4">
              <button
                onClick={() => onEdit(article)}
                className="bg-blue-600 text-white px-3 py-1 text-sm rounded-md hover:bg-blue-700"
              >
                編集
              </button>
              <button
                onClick={() => handleDelete(article.id)}
                className="bg-red-600 text-white px-3 py-1 text-sm rounded-md hover:bg-red-700"
              >
                削除
              </button>
            </div>
          </div>
        </div>
      ))}
    </div>
  );
};

export default ArticleList;