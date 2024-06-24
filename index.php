<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>データ登録</title>
    <link href="css/output.css" rel="stylesheet">
</head>

<body class=" bg-red-200 flex flex-col justify-center items-center">

    <!-- Header[Start] -->
    <header class="w-screen h-28 bg-red-200 flex justify-center items-center">
        <h1 class="text-center">なんでも掲示板</h1>
    </header>
    <!-- Header[End] -->
    <div class="min-h-screen w-4/5 flex flex-col  items-center bg-slate-100 rounded-lg">

        <!-- Main[Start] -->
        <!-- Posting area[Start] -->
        <form method="POST" action="index.php" enctype="multipart/form-data" class="w-full flex flex-col justify-center items-center border m-2">
            <div class="w-full flex flex-col justify-cente m-2">
                <div class="p-4">
                    <label for="name">名前：</label>
                    <input type="text" name="name" id="name" class="w-full h-11 border rounded-md">
                </div>
                <div class="p-4">
                    <label for="message">内容：</label>
                    <textArea name="message" id="message" rows="4" cols="40" class="w-full border rounded-md"></textArea>
                </div>
                <div class="p-4">
                    <label for="picture">写真：</label>
                    <input type="file" name="picture" id="picture" class="w-full h-11">
                </div>
            </div>
            <div class="flex justify-end">
                <input type="submit" value="送信" class=" border hover:bg-blue-300 rounded-md p-2 m-2">
            </div>
        </form>
        <!-- Posting area[End] -->

        <!-- Search area[Start] -->
        <form method="GET" action="" class="w-full border m-4 p-4">
            <div class="flex justify-center items-center">
                <label for="search">内容検索:</label>
                <input type="text" name="search" class="w-60 h-11 border rounded-md" id="search" value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES) ?>">
            </div>
            <div class="flex justify-around m-4">
                <button type="submit" class="border rounded-md hover:bg-red-200">検索</button>
                <button type="button" class="border rounded-md hover:bg-green-200" onclick="clearSearch()">クリア</button>
            </div>
        </form>
        <!-- Search area[End] -->

        <!-- Display area[Start] -->
        <div class="w-full m-4">
            <h2 class="text-center">投稿一覧</h2>
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

                $name = $_POST['name'];
                $message = $_POST['message'];
                $picture = null;  // $pictureの初期化

                // ファイルアップロード処理
                // issetで$_FILESにpictureのファイルが送信されたか確認
                // $_FILES['picture']['error']はエラーコードを示す変数 UPLOAD_ERR_OKはphpの定数
                if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {  // $_FILES['picture']['tmp_name'] 一時的なファイルパス 内容を読み込み→代入
                    $picture = file_get_contents($_FILES['picture']['tmp_name']);
                }

                // データベースに保存
                $stmt = $pdo->prepare('INSERT INTO board_msg(id, name, message, picture, date) VALUES(NULL, :name, :message, :picture, now())');
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':message', $message, PDO::PARAM_STR);
                // ファイルの内容を直接バインドする
                $file_content = file_get_contents($_FILES['picture']['tmp_name']);
                $stmt->bindValue(':picture', $file_content, PDO::PARAM_LOB);
                $status = $stmt->execute();

                if ($status === false) {
                    $error = $stmt->errorInfo();
                    exit('ErrorMessage:' . $error[2]);
                } else {  // 成功すれば現在のページにリダイレクト
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                }
            }

            // 検索処理 (POSTではなくGETが一般的 キャッシュ可 ブクマ共有可 クエリの透過性)
            // searchの値があればその値、なければ空文字を代入
            $searchWord = isset($_GET['search']) ? $_GET['search'] : '';
            if ($searchWord) {  // $searchWordが空でない場合
                $stmt = $pdo->prepare("SELECT * FROM board_msg WHERE message LIKE :searchWord ORDER BY date DESC");  // :searchWordで曖昧検索し降順で取得
                $stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
            } else {  // $searchWordが空の場合
                $stmt = $pdo->prepare("SELECT * FROM board_msg ORDER BY date DESC");
            }  // テーブル内の全データを降順で取得
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);  // 連想配列で取得し配列に格納

            // 検索結果の表示
            foreach ($results as $row) {
                echo '<div class="border rounded-md p-2 m-2 bg-white">';
                echo '<p><strong>名前：</strong>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<p><strong>内容：</strong>' . nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) . '</p>';
                // pictureが空でなければbase64エンコードされた画像データを表示
                if (!empty($row['picture'])) {
                    echo '<p><strong>写真：</strong><br><img src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '" alt="写真" class="w-64"></p>';
                }
                echo '<p><strong>日付：</strong>' . htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') . '</p>';
                echo '</div>';
            }
            ?>
        </div>
        <!-- Main[End] -->
    </div>

    <!-- Footer[Start] -->
    <footer class="w-screen h-28 bg-red-200 flex justify-center items-center">
        <h1 class="text-center">2024©なっちゃん</h1>
    </footer>
    <!-- Footer[End] -->

    <script>
        function clearSearch() {
            document.getElementById('search').value = '';
            window.location.href = window.location.pathname;
        }
    </script>
</body>

</html>