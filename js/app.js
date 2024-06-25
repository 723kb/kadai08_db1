// 選択した写真をプレビュー表示する関数
function previewFile() {
  const fileInput = document.getElementById('picture');
  const preview = document.getElementById('preview');
  // 選択されたファイルを取得
  const file = fileInput.files[0];
  // FileReaderを使用して内容を読み込み
  if (file) {
    const reader = new FileReader();

    reader.onload = function (event) {
      preview.src = event.target.result;
      preview.classList.remove('hidden'); // プレビューを表示する
    }

    reader.readAsDataURL(file);
  } else {
    preview.src = '';
    preview.classList.add('hidden'); // ファイルがない場合はプレビューを隠す
  }
}
// ページ読み込み時に初期化するために呼び出し
previewFile();

// メッセージの文字数を監視する処理
const messageTextarea = document.getElementById('message');
const messageError = document.getElementById('messageError');

messageTextarea.addEventListener('input', function () {
  if (this.value.length > 140) {
    messageError.classList.remove('hidden');
  } else {
    messageError.classList.add('hidden');
  }
});

// 検索欄をクリアする処理
function clearSearch() {
  document.getElementById('search').value = '';
  window.location.href = window.location.pathname;
}

// 昇順ボタンのクリックイベントを監視し、changeOrder関数を呼び出す
document.getElementById('ascButton').addEventListener('click', function () {
  changeOrder('asc');
});
// 降順ボタンのクリックイベントを監視し、changeOrder関数を呼び出す
document.getElementById('descButton').addEventListener('click', function () {
  changeOrder('desc');
});

// リストの並び順を変更する関数
function changeOrder(order) {
  const searchInput = document.getElementById('search');
  // 検索キーワードを取得 なければは空文字
  const searchValue = searchInput ? searchInput.value : '';
  // 現在のスクロール位置を取得
  const scrollPosition = window.scrollY;
  // 現在のURLをURLオブジェクトとして取得
  const currentUrl = new URL(window.location.href);
  // URLのクエリパラメータ「order」を設定
  currentUrl.searchParams.set('order', order);
  // URLのクエリパラメータ「search」を設定
  currentUrl.searchParams.set('search', searchValue);
  // URLのクエリパラメータ「scroll」を設定
  currentUrl.searchParams.set('scroll', scrollPosition);
  // 更新されたURLにリダイレクト
  window.location.href = currentUrl.toString();
}

// ページ読み込み時に実行する処理
window.addEventListener('load', function () {
  // URLのクエリパラメータを取得するためのURLSearchParamsオブジェクトを作成
  const urlParams = new URLSearchParams(window.location.search);
  // URLのクエリパラメータ「scroll」を取得
  const scrollPosition = urlParams.get('scroll');

  if (scrollPosition) {
    window.scrollTo(0, parseInt(scrollPosition)); // スクロール位置を設定
  }
});

// 検索押すとトップに移動する 解決できない
// 検索ボタンのクリックイベントを監視し、changeOrder関数を呼び出す
document.getElementById('searchButton').addEventListener('click', function () {
  const searchInput = document.getElementById('search');
  // 検索キーワードを取得
  const searchValue = searchInput ? searchInput.value : '';
  // 現在のスクロール位置を取得
  const scrollPosition = window.scrollY;
  // 現在のURLをURLオブジェクトとして取得
  const currentUrl = new URL(window.location.href);
  // URLのクエリパラメータ「search」を設定
  currentUrl.searchParams.set('search', searchValue);
  // URLのクエリパラメータ「scroll」を設定
  currentUrl.searchParams.set('scroll', scrollPosition);
  // 更新されたURLにリダイレクト
  window.location.href = currentUrl.toString();
});

// ページ読み込み時に実行する処理
window.addEventListener('load', function () {
  // URLのクエリパラメータを取得するためのURLSearchParamsオブジェクトを作成
  const urlParams = new URLSearchParams(window.location.search);
  // URLのクエリパラメータ「scroll」を取得
  const scrollPosition = urlParams.get('scroll');

  if (scrollPosition) {
    window.scrollTo(0, parseInt(scrollPosition)); // スクロール位置を設定
  }
});

// モーダル表示のトリガー要素に対するクリックイベントを監視
document.querySelectorAll('.picture-modal-trigger').forEach(trigger => {
  trigger.addEventListener('click', function () {
    // クリックされた要素から画像データを取得
    const imgSrc = this.getAttribute('data-img-src');

    // 新しいdiv要素を作成し、クラスとスタイルを設定
    const modalDiv = document.createElement('div');
    modalDiv.classList.add('border', 'rounded-md', 'overflow-hidden', 'w-full', 'h-auto', 'picture-modal-trigger');
    modalDiv.style.position = 'fixed';
    modalDiv.style.top = '50%';
    modalDiv.style.left = '50%';
    modalDiv.style.transform = 'translate(-50%, -50%)';
    modalDiv.style.zIndex = '9999';
    modalDiv.innerHTML = `
      <div class="modal-content relative w-full max-w-screen-md max-h-screen-md mx-auto">
          <span class="close absolute top-2 right-2 cursor-pointer text-2xl">&times;</span>
          <img src="${imgSrc}" alt="写真" class="w-full h-auto max-h-full object-contain">
      </div>
  `;

    // モーダルをページに追加
    document.body.appendChild(modalDiv);

    // モーダルのクローズ処理（クリックイベント）
    modalDiv.querySelector('.close').addEventListener('click', function () {
      modalDiv.remove(); // モーダルを削除
    });
  });
});