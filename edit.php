<?php
    //データベースの接続情報
    define('DB_HOST','localhost');
    define('DB_USER','animal2');
    define('DB_PASS','animal20219');
    define('DB_NAME','animal-app2');


    //タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');

    //変数の初期化
    $view_name = null;
    $message = array();
    $message_data = null;
    $error_message = array();
    $pdo = null;
    $stmt = null;
    $res = null;
    $option = null;

    session_start();

    //管理者としてログインしているか確認
    if(empty($_SESSION['admin_login'])|| $_SESSION['admin_login'] !== true) {

        //ログインページへリダイレクト
        header("Location:./admin.php");
        exit;
    }

    //データベースに接続
    try{

        //セキュリティ対策の一環としてオプションの指定を追記する
        $option = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
        );
        //データベースに接続
        $pdo = new PDO('mysql:charset=utf8;dbname='.DB_NAME.';host='.DB_HOST,DB_USER,DB_PASS,$option);
    }catch(PDOException $e) {

        //接続エラーのときエラー内容を取得する
        $error_message[] = $e->getMessage();
    }

    if(!empty($_GET['message_id'])&& empty($_POST['message_id'])) {

        //SQL作成
        $stmt = $pdo->prepare("SELECT * FROM message_board WHERE id = :id");

        //値をセット
        $stmt->bindValue(':id',$_GET['message_id'],PDO::PARAM_INT);

        //SQLクエリの実行
        $stmt->execute();

        //表示するデータを取得
        $message_data = $stmt->fetch();

        //投稿データが取得できない時は管理ページに戻る
        if(empty($message_data)) {
            header("Location:./admin.php");
            exit;
        }

    } elseif (!empty($_POST['message_id'])) {

        //ここに編集内容を保存する処理が入る
        //空白除去
        $view_name = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u','',$_POST['view_name']);
        $message = preg_replace('/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u','',$_POST['message']);

        //表示名の入力チェック
        if(empty($view_name)) {
            $error_message[] = '表示名を入力してください。';
        }

        //メッセージの入力チェック
        if(empty($message)) {
            $error_message[] = 'メッセージを入力してください。';
        }else{

            //文字数を確認
            if(100 < mb_strlen($message,'UTF-8')) {
                $error_message[] = 'ひと言メッセージは１００文字以内で入力してください。';
            }
        }

        if(empty($error_message)) {

            //ここにデータベースに保存する処理が入る
            //トランザクション開始
            $pdo->beginTransaction();

            try {
                
                //SQL作成
                $stmt = $pdo->prepare("UPDATE message_board SET view_name = :view_name,message = :message WHERE id = :id");

                //値をセット
                $stmt->bindParam(':view_name',$view_name,PDO::PARAM_STR);
                $stmt->bindParam(':message',$message,PDO::PARAM_STR);
                $stmt->bindValue(':id',$_POST['message_id'],PDO::PARAM_INT);

                //SQLクエリの実行
                $stmt->execute();

                //コミット
                $res = $pdo->commit();

            }catch(Exception $e) {
                //エラーが発生した時はロールバック
                $pdo->rollBack();
            }

            //更新に成功したら一覧に戻る
            if($res) {
                header("Location:./admin.php");
                exit;
            }
        }
    }
    
        //データベースの接続を閉じる
        $stmt = null;
        $pdo = null;


?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wan.chibi 管理ページ(投稿の編集)</title>
    <style>
        .btn_cancel {
            display: inline_block;
            margin-right: 10px; 
            padding: 10px 20px;
            color: #555;
            font-size: 86%;
            border-radius: 5px;
            border: 1px solid #999;
        }
        .btn_cancel:hover {
            color: #999;
            border-color: #999;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <h1>ひと言掲示板 管理ページ(投稿の編集)</h1>
        <?php if( !empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
            　   <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        <!-- ここにメッセージの入力フォームを設置 -->
        <form method="post">
            <div>
                <label for="view_name">表示名</label>
                <input id="view_name" type="text" name="view_name" value="<?php if(!empty($message_data['view_name'])) {echo $message_data['view_name'];} elseif(!empty($view_name)) {echo htmlspecialchars($view_name,ENT_QUOTES,'UTF-8');}?>">
            </div>
            <div>
                <label for="message">ひと言メッセージ</label>
                <textarea id="message" name="message"><?php if(!empty($message_data['message'])) {echo $message_data['message'];} elseif(!empty($message)) {echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');} ?></textarea>
            </div>
                <a class="btn_cancel" href="admin.php">キャンセル</a>
                <input type="submit" name="btn_submit" value="更新">
                <input type="hidden" name="message_id" value="<?php if(!empty($message_data['id'])) {echo $message_data['id'];} elseif(!empty($_POST['message_id'])) {echo htmlspecialchars($_POST['message_id'],ENT_QUOTES,'UTF-8');}?>">
        </form>
    </body>
</html>
