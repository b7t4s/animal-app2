<?php 

//メッセージを保存するファイルのパス設定
define('FILENAME', './message.txt');

//タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

//変数の初期化
$current_date = null;
$data = null;
$file_handle = null;
$split_data = null;
$message = array();
$message_array = array();
$success_message = null;
$error_message = array();
$clean = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

//データベースに接続
try{

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname=animal-app2;host=localhost','animal2','animal20219',$option);

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
        $error_message[] = '表示名を入力してください。';
    }else{
        $clean['view_name'] = htmlspecialchars($_POST['view_name'],ENT_QUOTES,"UTF-8");
        $clean['view_name'] = preg_replace( '/\\r\\n|\\n|\\r/', '', $clean['view_name']);
    }

    //メッセージの入力チェック
    if(empty($message)) {
        $error_message[] = 'メッセージを入力してください。';      
    }else{
        $clean['message'] = htmlspecialchars($_POST['message'],ENT_QUOTES,"UTF-8");
        $clean['message'] = preg_replace( '/\\r\\n|\\n|\\r/', '<br>', $clean['message']);
    }

    if(empty($error_message)) {
        /*コメントアウト
        if($file_handle = fopen( FILENAME,"a")) {
            //書き込み日時を取得
            $current_date = date("Y-m-d H:i:s");
    
            //書き込むデータを作成
            $data = "'".$clean['view_name']."','".$clean['message']."','".$current_date."'\n";
    
            //書き込み
            fwrite($file_handle,$data);
    
            //ファイルを閉じる
            fclose($file_handle);
    
            $success_message = 'メッセージを書き込みました。';
        }  
        ここまでコメントアウト*/

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
            $success_message = 'メッセージを書き込みました。';
        }else{
            $error_message[] = '書き込みに失敗しました。';
        }

        //プリペアドステートメントを削除
        $stmt = null;
    }
}

if(empty($error_message)) {

    //メッセージのデータを取得する
    $sql = "SELECT view_name,message,post_date FROM message_board ORDER BY post_date DESC";
    $message_array = $pdo->query($sql);
}

//データベースの接続を閉じる
$pdo = null;

/*コメントアウトする
if($file_handle = fopen(FILENAME,'r')) {
    while($data = fgets($file_handle)) {

        $split_data = preg_split('/\'/',$data);

        $message = array(
            'view_name' => $split_data[1],
            'message' => $split_data[3],
            'post_date' => $split_data[5]
        );
       array_unshift($message_array,$message);
    }

    //ファイルを閉じる
    fclose($file_handle);
}
ここまでコメントアウト*/

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wan.chibi</title>
</head>
<body>
    <form method="post">
        <?php if(!empty($success_message)): ?>
            <p class="success_message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        <?php if(!empty($error_message)): ?>
            <ul class="error_message">
                <?php foreach($error_message as $value): ?>
                    <li>・<?php echo $value; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div>
            <label for="view_name">名前</label>
            <input id="view_name" type="text" name="view_name" value="">
        </div>
        <div>
            <label for="message">投稿</label>
            <textarea id="message" name="message"></textarea>
        </div>
        <input type="submit" name="btn_submit" value="書き込む">
     </form>
     <hr>
     <section>
        <?php if(!empty($message_array)): ?>
        <?php foreach($message_array as $value): ?>
        <article>
            <div class="info">
                <h2><?php echo $value['view_name']; ?></h2>
                <time><?php echo date('Y年m月d日 H:i', strtotime($value['post_date'])); ?></time>
            </div>
            <p><?php echo nl2br($value['message']); ?></p>
        </article>
        <?php endforeach; ?>
        <?php endif; ?>
     </section>
</body>
</html>