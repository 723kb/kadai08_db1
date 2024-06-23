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
        <form method="POST" action="index.php" class="w-full flex flex-col justify-center items-center border m-2">
            <div class="w-full flex flex-col justify-center border-emerald-50 m-2">
                <div class="p-4">
                    <label for="name">名前：</label>
                    <input type="text" name="name" id="name" class="w-full h-11 border rounded-md">
                </div>
                <div class="p-4">
                    <label for="message">内容：</label>
                    <textArea name="message" id="message" rows="4" cols="40" class="w-full border rounded-md"></textArea>
                </div>
            </div>
            <div class="flex justify-end">
                <input type="submit" value="送信" class=" border hover:bg-blue-300 rounded-md p-2 m-2">
            </div>
        </form>

        <!-- データ検索 -->
            <form method="GET" action=""class="w-full border m-4 p-4">
                <div class="flex justify-center items-center">
                    <label for="search">内容検索:</label>
                    <input type="text" name="search" class="w-60 h-11 border rounded-md" id="search" value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES) ?>">
                </div>
                <div class="flex justify-around m-4">
                <button type="submit" class="border rounded-md hover:bg-red-200">検索</button>
                <button type="button" class="border rounded-md hover:bg-green-200" onclick="clearSearch()">クリア</button>
                </div>
            </form>

        <!-- データ表示 -->
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
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $name = $_POST['name'];
                $message = $_POST['message'];

                $stmt = $pdo->prepare('INSERT INTO board_msg(id, name, message, date) VALUES(NULL, :name, :message, now() )');
                $stmt->bindValue(':name', $name, PDO::PARAM_STR);
                $stmt->bindValue(':message', $message, PDO::PARAM_STR);
                $status = $stmt->execute();

                if ($status === false) {
                    $error = $stmt->errorInfo();
                    exit('ErrorMessage:' . $error[2]);
                } else {
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit();
                }
            }

            // 検索処理
            $searchWord = isset($_GET['search']) ? $_GET['search'] : '';
            if ($searchWord) {
                $stmt = $pdo->prepare("SELECT * FROM board_msg WHERE message LIKE :searchWord ORDER BY date DESC");
                $stmt->bindValue(':searchWord', '%' . $searchWord . '%', PDO::PARAM_STR);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM board_msg ORDER BY date DESC");
            }
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $row) {
                echo '<div class="border rounded-md p-2 m-2 bg-white">';
                echo '<p><strong>名前：</strong>' . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<p><strong>内容：</strong>' . nl2br(htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8')) . '</p>';
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