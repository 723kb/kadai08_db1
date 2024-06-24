<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>データ登録</title>
    <link href="css/output.css" rel="stylesheet">
    <!-- font-awesome 読み込み -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts 読み込み -->
    <link href="https://fonts.googleapis.com/css2?family=M+PLUS+Rounded+1c:wght@400;500;700&display=swap" rel="stylesheet">
</head>

<body class="bg-[#B5D9DB] flex flex-col justify-center items-center">

    <!-- Header[Start] -->
    <header class="w-screen h-28 bg-[#B5D9DB] flex justify-center items-center">
        <h1 class="text-center">なんでも掲示板</h1>
    </header>
    <!-- Header[End] -->

    <!-- Main[Start] -->
    <div class="min-h-screen w-5/6 flex flex-col  items-center bg-[#F1F6F5] rounded-lg">

        <!-- Posting area[Start] -->
        <form method="POST" action="index.php" enctype="multipart/form-data" id="myForm" class="w-full flex flex-col justify-center items-center m-2">
            <div class="w-full flex flex-col justify-center m-2">
                <div class="p-4">
                    <label for="name">名前：</label>
                    <input type="text" name="name" id="name" placeholder="テストちゃん" class="w-full h-11 p-2 border rounded-md">
                </div>
                <div class="p-4">
                    <label for="message">内容：</label>
                    <textArea name="message" id="message" placeholder="140字以内で内容を入力してください。" rows="4" cols="40" class="w-full p-2 border rounded-md"></textArea>
                    <div id="messageError" class="text-red-500 text-lg mt-1 hidden">内容は140文字以内で入力してください</div>
                </div>
                <div class="pb-4 px-4">
                    <label for="picture">写真：</label>
                    <div class="flex justify-center items-center">
                        <input type="file" name="picture" id="picture" accept="image/*" onchange="previewFile()" class="w-full h-11 py-2 my-2">
                        <!-- accept="image/*" 画像ファイルのみを許可 -->
                        <button type="submit" class="w-1/6 border border-slate-200 hover:bg-[#93CCCA] rounded-md p-2 my-2"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
                <div class="flex justify-center">
                    <img src="" id="preview" class="hidden max-w-100% max-h-[300px]" alt="選択した画像のプレビュー">
                </div>
            </div>
        </form>
        <!-- Posting area[End] -->

        <!-- Search area[Start] -->
        <form method="GET" action="" class="w-full flex flex-row justify-around items-center border m-2">
            <div class="w-2/3 p-4">
                <label for="search">内容検索:</label>
                <input type="text" name="search" placeholder="キーワードで内容を検索" class="w-full h-11 p-2 m-2 border rounded-md" id="search" value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES) ?>">
            </div>
            <div class="w-1/3 flex justify-around items-end pt-4">
                <button type="button" class="w-1/4 border border-slate-200 rounded-md hover:bg-[#FAEAB1] p-2 m-2" onclick="clearSearch()">
                    <i class="fas fa-search "></i>
                </button>
                <button type="button" class="w-1/4 border border-slate-200 rounded-md hover:bg-[#D1D1D1] p-2 m-2" onclick="clearSearch()"><i class="fas fa-times-circle"></i></button>
            </div>
        </form>
        <!-- Search area[End] -->

        <!-- Display area[Start] -->
        <div class="w-full m-4">
            <h2 class="text-center mb-4">投稿一覧</h2>
            <!-- ソートボタン -->
            <div class="w-1/2 flex justify-around mx-auto">
                <button type="button" name="order" id="ascButton" value="asc" class="w-1/5 border border-slate-200 rounded-md hover:bg-[#FFC4C4] p-2 m-2">
                    <i class="fas fa-sort-amount-up"></i> 昇順 </button>
                <button type="button" name="order" id="descButton" value="desc" class="w-1/5 border border-slate-200 rounded-md hover:bg-[#AAC4FF] p-2 m-2">
                    <i class="fas fa-sort-amount-down"></i> 降順 </button>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <?php
                // DB接続
                try {
                    $pdo = new PDO('mysql:dbname=gs_board;charset=utf8;host=localhost', 'root', '');
                } catch (PDOException $e) {
                    exit('DBConnectError:' . $e->getMessage());
                }

                // データ登録処理
                if ($_SERVER['REQUEST_METHOD'] == 'POST') {  // POSTで送信されたか確認
                    if (
                        // $_POST['name']$_POST['message']がセットされていないor空文字(=未入力)ならtrue
                        !isset($_POST['name']) || $_POST['name'] === '' ||
                        !isset($_POST['message']) || $_POST['message'] === ''
                    ) { // 上記どちらかがtrueならexitを実行
                        exit('名前または内容が入力されていません');
                    }

                    // メッセージが140文字を超えている場合はエラーとして処理を中断する
                    if (mb_strlen($_POST['message']) > 140) {
                        exit('内容は140文字以内で入力してください');
                    }

                    $name = $_POST['name'];
                    $message = $_POST['message'];
                    $picture = null;  // $pictureの初期化

                    // ファイルアップロード処理
                    // issetで$_FILESにpictureのファイルが送信されたか確認
                    // $_FILES['picture']['error']はエラーコードを示す変数 UPLOAD_ERR_OKはphpの定数
                    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                        // $_FILES['picture']['tmp_name'] 一時的なファイルパス 内容を読み込み→代入
                        $picture = file_get_contents($_FILES['picture']['tmp_name']);
                    } elseif ($_FILES['picture']['error'] !== UPLOAD_ERR_NO_FILE) {
                        exit('写真のアップロードに失敗しました');
                    }
                    // データベースに保存
                    $stmt = $pdo->prepare('INSERT INTO board_msg(id, name, message, picture, date) VALUES(NULL, :name, :message, :picture, now())');
                    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                    $stmt->bindValue(':message', $message, PDO::PARAM_STR);

                    // 写真がアップロードされている場合のみバインドする
                    if ($picture !== null) {
                        $stmt->bindValue(':picture', $picture, PDO::PARAM_LOB);
                    } else {
                        $stmt->bindValue(':picture', null, PDO::PARAM_NULL);
                    }
                    $status = $stmt->execute();

                    echo '<script>window.location.href = "' . $_SERVER['PHP_SELF'] . '";</script>';  // ヘッダーロケーションだとエラー解消できなかった
                    exit();
                }

                // 検索処理 (POSTではなくGETが一般的 キャッシュ可 ブクマ共有可 クエリの透過性)
                // searchの値があればその値、なければ空文字を代入
                $searchWord = isset($_GET['search']) ? $_GET['search'] : '';

                // クエリの並び順を取得
                $order = isset($_GET['order']) ? $_GET['order'] : 'desc'; // デフォルトは降順

                if ($searchWord) {  // $searchWordが空でない場合
                    $stmt = $pdo->prepare("SELECT * FROM board_msg WHERE message LIKE :searchWord ORDER BY date $order");  // :searchWordで曖昧検索し降順で取得
                    $stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
                } else {  // $searchWordが空の場合
                    $stmt = $pdo->prepare("SELECT * FROM board_msg ORDER BY date $order");
                }  // テーブル内の全データを降順で取得
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);  // 連想配列で取得し配列に格納

                // 検索結果の表示
                foreach ($results as $row) {
                    echo '<div class="border rounded-md p-2 m-2 bg-white flex flex-col">';
                    echo '<p><strong>名前：</strong>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p><strong>内容：</strong>' . nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) . '</p>';

                    // 写真部分にクラスとデータ属性を設定
                    echo '<div class="border rounded-md overflow-hidden w-full h-auto picture-modal-trigger"';
                    if (!empty($row['picture'])) {
                        echo ' data-img-src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '"'; // モーダルに表示する画像データ
                    }
                    echo '>';

                    // pictureが空でなければbase64エンコードされた画像データを表示
                    if (!empty($row['picture'])) {
                        echo '<img src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '" alt="写真" class="w-full h-auto">';
                    }
                    echo '</div>';

                    echo '<p class="mt-auto"><strong>日付：</strong>' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
        <!-- Main[End] -->
    </div>

    <!-- Footer[Start] -->
    <footer class="w-screen h-28 bg-[#B5D9DB] flex justify-center items-center">
        <h1 class="text-center">2024©なっちゃん</h1>
    </footer>
    <!-- Footer[End] -->

    <script>
        function clearSearch() {
            document.getElementById('search').value = '';
            window.location.href = window.location.pathname;
        }

        // 選択した写真をプレビュー表示する関数
        function previewFile() {
            const fileInput = document.getElementById('picture');
            const preview = document.getElementById('preview');
            // 選択されたファイルを取得
            const file = fileInput.files[0];
            // FileReaderを使用して内容を読み込み
            if (file) {
                const reader = new FileReader();

                reader.onload = function(event) {
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

        messageTextarea.addEventListener('input', function() {
            if (this.value.length > 140) {
                messageError.classList.remove('hidden');
            } else {
                messageError.classList.add('hidden');
            }
        });

        // 昇順ボタンのクリックイベントを監視し、changeOrder関数を呼び出す
        document.getElementById('ascButton').addEventListener('click', function() {
            changeOrder('asc');
        });
        // 降順ボタンのクリックイベントを監視し、changeOrder関数を呼び出す
        document.getElementById('descButton').addEventListener('click', function() {
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
        window.addEventListener('load', function() {
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
            trigger.addEventListener('click', function() {
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
                modalDiv.querySelector('.close').addEventListener('click', function() {
                    modalDiv.remove(); // モーダルを削除
                });
            });
        });
    </script>

</body>

</html>