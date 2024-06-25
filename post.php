<!-- Main[Start] -->
<div class="min-h-screen w-5/6 flex flex-col  items-center bg-[#F1F6F5] rounded-lg">

  <!-- Posting area[Start] -->
  <form method="POST" action="index.php" enctype="multipart/form-data" id="myForm" class="w-full flex flex-col justify-center items-center m-2">
    <div class="w-full flex flex-col justify-center m-2">
      <div class="p-4">
        <label for="name" class="text-sm sm:text-base md:text-lg lg:text-xl">名前：</label>
        <input type="text" name="name" id="name" placeholder="テストちゃん" class="w-full h-11 p-2 border rounded-md">
      </div>
      <div class="p-4">
        <label for="message" class="text-sm sm:text-base md:text-lg lg:text-xl">内容：</label>
        <textArea name="message" id="message" placeholder="140字以内で内容を入力してください。" rows="4" cols="40" class="w-full p-2 border rounded-md"></textArea>
        <div id="messageError" class="text-red-500 text-lg mt-1 hidden">内容は140文字以内で入力してください</div>
      </div>
      <div class="pb-4 px-4">
        <label for="picture" class="text-sm sm:text-base md:text-lg lg:text-xl">写真：</label>
        <div class="flex flex-col sm:flex-row justify-center items-center">
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
      <label for="search" class="text-sm sm:text-base md:text-lg lg:text-xl">内容検索:</label>
      <input type="text" name="search" placeholder="キーワードで内容を検索" class="w-full h-11 p-2 border rounded-md" id="search" value="<?= htmlspecialchars(isset($_GET['search']) ? $_GET['search'] : '', ENT_QUOTES) ?>">
    </div>
    <div class="w-1/3 flex justify-around items-end pt-4">
      <button type="submit" id="searchButton" class="w-1/4 border border-slate-200 rounded-md hover:bg-[#FAEAB1] p-2 m-2">
        <i class="fas fa-search "></i>
      </button>
      <button type="button" class="w-1/4 border border-slate-200 rounded-md hover:bg-[#D1D1D1] p-2 m-2" onclick="clearSearch()"><i class="fas fa-times-circle"></i></button>
    </div>
  </form>
  <!-- Search area[End] -->

  <!-- Display area[Start] -->
  <div class="w-full m-4">
    <h2 class="text-md sm:text-lg md:text-xl lg:text-2xl text-center mb-4 font-mochiy-pop-one">Posts</h2>
    <!-- ソートボタン -->
    <div class="w-1/2 flex justify-around mx-auto">
      <button type="button" name="order" id="ascButton" value="asc" class="w-1/5 border border-slate-200 rounded-md hover:bg-[#FFC4C4] p-2 m-2">
        <i class="fas fa-sort-amount-up"></i>
      </button>
      <button type="button" name="order" id="descButton" value="desc" class="w-1/5 border border-slate-200 rounded-md hover:bg-[#AAC4FF] p-2 m-2">
        <i class="fas fa-sort-amount-down"></i>
      </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
      <?php
      require_once('funcs.php');

      // DB接続
      try {
        $pdo = new PDO($dsn, $user, $password);
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
        echo '<p class="text-sm sm:text-base lg:text-lg"><strong class="text-base sm:text-lg lg:text-xl">名前：</strong>' . h($row['name']) . '</p>';
        echo '<p class="text-sm sm:text-base lg:text-lg mt-2"><strong class="text-base sm:text-lg lg:text-xl">内容：</strong>' . nl2br(h($row['message'])) . '</p>';

        // 写真部分にクラスとデータ属性を設定
        echo '<div class="rounded-md overflow-hidden w-full h-auto max-w-full max-h-96 picture-modal-trigger"';
        if (!empty($row['picture'])) {
          echo ' data-img-src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '"'; // モーダルに表示する画像データ
        }
        echo '>';

        // pictureが空でなければbase64エンコードされた画像データを表示
        if (!empty($row['picture'])) {
          echo '<img src="data:image/jpeg;base64,' . base64_encode($row['picture']) . '" alt="写真" class="w-full h-auto max-w-full max-h-[90vh] object-contain">';
        }
        echo '</div>';
        echo '<p class="mt-auto text-sm sm:text-base lg:text-lg"><strong class="text-base sm:text-lg lg:text-xl">日付：</strong>' . h($row['date']) . '</p>';
        echo '</div>';
      }

      ?>
    </div>
  </div>
  <!-- Main[End] -->
</div>