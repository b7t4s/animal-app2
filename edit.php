<?php
    //データベースの接続情報
    define('DB_HOST','localhost');
    define('DB_USER','animal2');
    define('DB_PASS','animal20219');
    define('DB_NAME','animal-app2');


    //タイムゾーン設定
    date_default_timezone_set('Asia/Tokyo');

    //変数の初期化
    $current_date = null;
    $message = array();
    $message_array = array();
    $success_message = null;
    $error_message = array();
    $pdo = null;
    $stmt = null;
    $res = null;
    $option = null;

    session_start();

    //管理者としてログインしているか確認
    if(empty($_SESSION['admin_login']) || $_SESSION['admin_login'] !== true) {

        //ログインページへリダイレクト
        header("Location:./admin.php");
        exit;
    }

    //データベースに接続
    try{

        $option = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
        );
        $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST,DB_USER,DB_PASS,$option);

    }catch(PDOException $e) {

        //接続エラーの時エラー内容を取得する
        $error_message[] = $e->getMessage();
    }

    if(!empty($_GET['message_id'])) {

        //投稿を取得するコードが入る
        //SQL作成
        $stmt = $pdo->prepare("SELECT * FROM message_board WHERE id = :id");

        //値をセット
        $stmt->bindValue(':id',$_GET['meesage_id'],PDO::PARAM_INT);

        //SQLクエリの実行
        $stmt->execute();

        //表示するデータを取得
        $message_data = $stmt->fetch();

        //投稿データが取得できない時は管理ページに戻る
        if(empty($message_data)) {
            header("Location:./admin.php");
            exit;
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
    <h1>管理ページ(投稿の編集)</h1>
    <?php if(!empty($error_message)): ?>
        <ul class="error_message">
            <?php foreach($error_message as $value): ?>
                <li>・<?php echo $value; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post">
        <div>
            <label for="view_name">名前</label>
            <input id="view_name" type="text" name="view_name" value="<?php if(!empty($message_data['view_name'])) { echo $message_data['view_name']; }?>">
        </div>
        <div>
            <label for="message">投稿</label>
            <textarea id="message" name="message"><?php if(!empty($message_data['message'])) { echo $message_data['message']; }?></textarea>
        </div>
        <a class="btn_cancel" href="admin.php">キャンセル</a>
        <input type="submit" name="btn_submit" value="更新">
        <input type="hidden" name="message_id" value="<?php if(!empty($message_data['id'])) {echo $message_data['id']; }?>">
    </form>
</body>
</html>
