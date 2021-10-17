<?php 

//管理ページのログインパスワード
define('PASSWORD','adminPassword');

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

if(!empty($_GET['btn_logout'])) {
    unset($_SESSION['admin_login']);
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

if(!empty($_POST['btn_submit'])) {

    if(!empty($_POST['admin_password']) && $_POST['admin_password'] === PASSWORD) {
        $_SESSION['admin_login'] = true;
    }else{
        $error_message[] = 'ログインに失敗しました。';
    } 
}

if(!empty($pdo)) {

     //メッセージのデータを取得する
     $sql = "SELECT * FROM message_board ORDER BY post_date DESC";
     $message_array = $pdo->query($sql);   
}

//データベースの接続を閉じる
$pdo = null;

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wan.chibi 管理ページ</title>
    <style>
        body {
            padding: 30px;
            margin: 0 auto;
            width: 50%;
        }

        textarea {
            width: 98%;
            height: 60px;
        }
        .info p {
            display: inline-block;
            line-height: 1.6em;
            font-size: 86%;
        } 
        input[name=btn_logout] {
            margin: 40px;
            background-color: #666;
        }
        input[name=btn_logout]:hover {
             background-color: #777;
        }
        /*-----------------------------------
        掲示板エリア
        -----------------------------------*/
        article {
            margin-top: 20px;
            padding: 20px;
            border-radius: 10px;
            background: #fff;
        }
        article.reply {
            position: relative;
            margin-top: 15px;
            margin-left: 30px;
        }
        article.reply::before {
            position: absolute;
            top: -10px;
            left: 20px;
            display: block;
            content: "";
            border-top: none;
            border-left: 7px solid #f7f7f7;
            border-right: 7px solid #f7f7f7;
            border-bottom: 10px solid #fff;
        }
            .info {
                margin-bottom: 10px;
            }
            .info h2 {
                display: inline-block;
                margin-right: 10px;
                color: #222;
                line-height: 1.6em;
                font-size: 86%;
            }
            .info time {
                color: #999;
                line-height: 1.6em;
                font-size: 72%;
            }
            article p {
                color: #555;
                font-size: 86%;
                line-height: 1.6em;
            }
        @media only screen and (max-width: 1000px) {
            body {
                padding: 30px 5%;
            }
            input[type="text"] {
                width: 100%;
            }
            textarea {
                width: 100%;
                max-width: 100%;
                height: 70px;
            }
        } 
    </style>
</head>
<body>
    <h1>管理ページ</h1>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
     <section>
        <?php if(!empty($_SESSION['admin_login'])&& $_SESSION['admin_login'] === true): ?>

        <!--download.phpを呼び出す為のボタンを設置-->
        <form method="get" action="./download.php">
            <select name="limit">
                <option value="">全て</option>
                <option value="10">10件</option>
                <option value="30">30件</option>
            </select>
            <input type="submit" name="btn_download" value="ダウンロード">
        </form>

        <?php if(!empty($message_array)): ?>
        <?php foreach($message_array as $value): ?>
        <article>
            <div class="info">
                <h2><?php echo htmlspecialchars($value['view_name'],ENT_QUOTES,'UTF-8'); ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
                <p><a href="edit.php?message_id=<?php echo $value['id']; ?>">編集</a><a href="delete.php?message_id=<?php echo $value['id']; ?>">削除</a></p>
            </div>
            <p><?php echo nl2br(htmlspecialchars($value['message'],ENT_QUOTES,'UTF-8')); ?></p>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>

        <form method="get" action="">
            <input type="submit" name="btn_logout" value="ログアウト">
        </form>

        <?php else: ?>
        <!--ここにログインフォームが入る-->
        <form method="post">
            <div>
                <label for="admin_password">ログインパスワード</label>
                <input id="admin_password" type="password" name="admin_password" value="">
            </div>
            <input type="submit" name="btn_submit" value="ログイン">
        </form>  
         <?php endif; ?>
     </section>
</body>
</html>
