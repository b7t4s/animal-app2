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
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

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

    // 空白除去
	$view_name = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['view_name']);
	$message = preg_replace( '/\A[\p{C}\p{Z}]++|[\p{C}\p{Z}]++\z/u', '', $_POST['message']);

    //名前の入力チェック
    if(empty($view_name)) {
        $error_message[] = '名前を入力してください。';
    }else{

        //セッションに表示名を保存
        $_SESSION['view_name'] = $view_name;
    }

    //メッセージの入力チェック
    if(empty($message)) {
        $error_message[] = 'メッセージを入力してください。';      
    }else{

        //文字数を確認
        if(100 < mb_strlen($message,'UTF-8')) {
            $error_message[] = 'メッセージは１００文字以内で入力してください。';
        }
    }

    if(empty($error_message)) {

        //書き込み日時を取得
        $current_date = date("Y-m-d H:i:s");

        //トランザクション開始
        $pdo->beginTransaction();

        try{

        //SQL作成
        $stmt = $pdo->prepare("INSERT INTO message_board(view_name,message,post_date)VALUES(:view_name,:message,:current_date)");

        //値のセット
        $stmt->bindParam(':view_name',$view_name,PDO::PARAM_STR);
        $stmt->bindParam(':message',$message,PDO::PARAM_STR);
        $stmt->bindParam(':current_date',$current_date,PDO::PARAM_STR);

        //SQLクエリの実行
        $res = $stmt->execute();

        //コミット
        $res = $pdo->commit();

        }catch(Exception $e) {

            //エラーが発生した時はロールバック
            $pdo->rollBack();
        }

        if($res) {
            $_SESSION['success_message'] = 'メッセージを書き込みました。';
        }else{
            $error_message[] = '書き込みに失敗しました。';
        }

        //プリペアドステートメントを削除
        $stmt = null;

        header('Location:./');
        exit;
    }
}

if(!empty($pdo)) {

    //メッセージのデータを取得する
    $sql = "SELECT view_name,message,post_date FROM message_board ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);
}

//データベースの接続を閉じる
$pdo = null;

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">

    <!-- オリジナルCSS -->
    <link rel="stylesheet" href="./css/style.css">

    <title>wan.chibi</title>
    
    <link href="images/favicon.ico" rel="icon" type="image/x-icon" />
</head>
<body>
    <!-- ■ ヘッダーエリア -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <!--<a class="navbar-brand">--><img src="images/logo2.png" id="logo"><!--</a>-->
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="#">ホーム</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">マイページ</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- ■ カルーセルエリア -->
    <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item carousel-item-ex active">
                <img src="images/wan.chibi.png" class="d-block w-100 img-fluid" alt="写真">
            </div>
            <div class="carousel-item carousel-item-ex">
                <img src="images/207494.jpg" class="d-block w-100 img-fluid" alt="写真">
            </div>
            <div class="carousel-item carousel-item-ex">
                <img src="images/1.png" class="d-block w-100 img-fluid" alt="写真">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleFade" role="button" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleFade" role="button" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </a>
    </div>

        <?php if(empty($_POST['btn_submit'])&& !empty($_SESSION['success_message'])): ?>
            <p class="success_message"><?php echo htmlspecialchars($_SESSION['success_message'],ENT_QUOTES,'UTF-8'); ?></p>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
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
            <input id="view_name" type="text" name="view_name" value="<?php if(!empty($_SESSION['view_name'])){ echo htmlspecialchars($_SESSION['view_name'],ENT_QUOTES,'UTF-8'); } ?>">
        </div>
        <div>
            <label for="message">投稿</label>
            <textarea id="message" name="message"><?php if(!empty($message)) { echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');} ?></textarea>
        </div>
        <input type="submit" name="btn_submit" value="書き込む">
     </form>
     <hr>
     <section>
        <?php if(!empty($message_array)): ?>
        <?php foreach($message_array as $value): ?>
        <article>
            <div class="info">
                <h2><?php echo htmlspecialchars($value['view_name'],ENT_QUOTES,'UTF-8'); ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            </div>
            <p><?php echo nl2br(htmlspecialchars($value['message'],ENT_QUOTES,'UTF-8')); ?></p>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
     </section>

      <!-- ■ フッターエリア -->
    <footer>
        <div class="container">
            <p class="text-center">© 2021 Copyright: アニマルAPP</p>
        </div>
    </footer>

    <!-- Optional JavaScript; choose one of the two! -->

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW"
        crossorigin="anonymous"></script>

</body>
</html>
