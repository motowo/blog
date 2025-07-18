import React, { useState, useEffect } from 'react';
import axios from 'axios';

interface ArticleDetailProps {
  articleId: string;
  user: any;
  onBack: () => void;
}

const ArticleDetail: React.FC<ArticleDetailProps> = ({ articleId, user, onBack }) => {
  const [article, setArticle] = useState<any>(null);
  const [isLoading, setIsLoading] = useState(true);
  const [purchaseStatus, setPurchaseStatus] = useState<any>(null);
  const [isPurchasing, setIsPurchasing] = useState(false);
  const [showPurchaseModal, setShowPurchaseModal] = useState(false);

  useEffect(() => {
    fetchArticle();
    if (user) {
      fetchPurchaseStatus();
    }
  }, [articleId, user]);

  const fetchArticle = async () => {
    try {
      const response = await axios.get(`http://localhost:8000/api/articles/${articleId}`);
      setArticle(response.data.article);
    } catch (error) {
      console.error('記事の取得に失敗しました:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const fetchPurchaseStatus = async () => {
    try {
      const token = localStorage.getItem('token');
      const response = await axios.get(`http://localhost:8000/api/articles/${articleId}/purchase-status`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      setPurchaseStatus(response.data);
    } catch (error) {
      console.error('購入状態の取得に失敗しました:', error);
    }
  };

  const handlePurchase = async () => {
    setIsPurchasing(true);
    try {
      const token = localStorage.getItem('token');
      const response = await axios.post('http://localhost:8000/api/purchases', {
        article_id: articleId
      }, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      });
      
      alert('記事を購入しました！');
      setShowPurchaseModal(false);
      fetchPurchaseStatus(); // 購入状態を更新
    } catch (error: any) {
      console.error('購入に失敗しました:', error);
      alert(error.response?.data?.message || '購入に失敗しました');
    } finally {
      setIsPurchasing(false);
    }
  };

  const formatPrice = (price: number) => {
    return new Intl.NumberFormat('ja-JP').format(price);
  };

  const getCategoryIcon = (categoryName: string) => {
    const icons = {
      'テクノロジー': '💻',
      'ビジネス': '💼',
      'ライフスタイル': '🌱',
      '教育': '📚',
      'エンターテイメント': '🎬'
    };
    return icons[categoryName] || '📄';
  };

  if (isLoading) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  if (!article) {
    return (
      <div className="min-h-screen bg-gray-100 flex items-center justify-center">
        <div className="text-center">
          <p className="text-gray-600 text-lg">記事が見つかりません</p>
          <button
            onClick={onBack}
            className="mt-4 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            戻る
          </button>
        </div>
      </div>
    );
  }

  const canReadFullArticle = !article.is_premium || (purchaseStatus && purchaseStatus.purchased);

  return (
    <div className="min-h-screen bg-gray-100">
      <header className="bg-white shadow">
        <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between items-center py-4">
            <button
              onClick={onBack}
              className="text-gray-400 hover:text-gray-600 flex items-center"
            >
              <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 19l-7-7 7-7" />
              </svg>
              戻る
            </button>
            <h1 className="text-lg font-semibold text-gray-900">記事詳細</h1>
            <div></div>
          </div>
        </div>
      </header>

      <main className="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <article className="bg-white rounded-lg shadow-lg overflow-hidden">
          <div className="p-8">
            <div className="mb-6">
              <div className="flex items-center space-x-3 text-sm text-gray-500 mb-4">
                <span>by {article.author_username}</span>
                <span>•</span>
                <span>{new Date(article.created_at).toLocaleDateString('ja-JP')}</span>
                <span>•</span>
                <span className="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs flex items-center gap-1">
                  <span>{getCategoryIcon(article.category_name)}</span>
                  <span>{article.category_name}</span>
                </span>
                <span>•</span>
                {article.is_premium ? (
                  <span className="bg-purple-100 text-purple-800 px-2 py-1 rounded-full text-xs">
                    有料 ¥{formatPrice(parseInt(article.price))}
                  </span>
                ) : (
                  <span className="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                    無料
                  </span>
                )}
              </div>
              <h1 className="text-3xl font-bold text-gray-900 mb-4">{article.title}</h1>
            </div>

            <div className="prose max-w-none">
              {canReadFullArticle ? (
                <div className="text-gray-700 leading-relaxed">
                  {article.content.split('\n').map((paragraph, index) => (
                    <p key={index} className="mb-4">{paragraph}</p>
                  ))}
                </div>
              ) : (
                <div>
                  <div className="text-gray-700 leading-relaxed mb-6">
                    {article.content.length > 200 
                      ? article.content.substring(0, 200) + '...'
                      : article.content
                    }
                  </div>
                  <div className="bg-gradient-to-t from-white to-transparent h-20 -mt-6 mb-6"></div>
                  <div className="text-center border-2 border-dashed border-gray-300 rounded-lg p-8">
                    <div className="mb-4">
                      <svg className="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      </svg>
                      <p className="text-lg font-semibold text-gray-900">この記事は有料記事です</p>
                      <p className="text-gray-600 mt-2">続きを読むには購入が必要です</p>
                    </div>
                    {user ? (
                      <button
                        onClick={() => setShowPurchaseModal(true)}
                        className="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 font-semibold"
                      >
                        ¥{formatPrice(parseInt(article.price))} で購入する
                      </button>
                    ) : (
                      <p className="text-gray-600">購入するにはログインが必要です</p>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>
        </article>
      </main>

      {/* 購入確認モーダル */}
      {showPurchaseModal && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 className="text-lg font-semibold text-gray-900 mb-4">記事を購入しますか？</h3>
            <div className="mb-4">
              <p className="text-gray-700 mb-2">記事: {article.title}</p>
              <p className="text-xl font-bold text-purple-600">¥{formatPrice(parseInt(article.price))}</p>
            </div>
            <div className="bg-yellow-50 border border-yellow-200 rounded-md p-3 mb-4">
              <p className="text-sm text-yellow-800">
                ⚠️ これはモック決済です。実際の料金は発生しません。
              </p>
            </div>
            <div className="flex justify-end space-x-3">
              <button
                onClick={() => setShowPurchaseModal(false)}
                className="px-4 py-2 text-gray-600 hover:text-gray-800"
                disabled={isPurchasing}
              >
                キャンセル
              </button>
              <button
                onClick={handlePurchase}
                disabled={isPurchasing}
                className="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 disabled:opacity-50"
              >
                {isPurchasing ? '購入中...' : '購入する'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};

export default ArticleDetail;