<?php

require_once './dbc.php';
$files = getAllFile();

?>
<!-- ①フォームの説明 -->
<!-- ②$_FILEの確認 -->
<!-- ③バリデーション -->
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>アップロードフォーム</title>
    <style>
    img {
        width: 200px;
    }
    </style>
</head>
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

.file-up {
    margin-bottom: 10px;
}

.submit {
    text-align: right;
}

.btn {
    display: inline-block;
    border-radius: 3px;
    font-size: 18px;
    background: #67c5ff;
    border: 2px solid #67c5ff;
    padding: 5px 10px;
    color: #fff;
    cursor: pointer;
}
</style>

<body>
    <form enctype="multipart/form-data" action="./file_upload.php" method="POST">
        <div class="file-up">
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
            <input name="img" type="file" accept="image/*" />
        </div>
        <div>
            <textarea name="caption" placeholder="キャプション（140文字以下）" id="caption"></textarea>
        </div>
        <div class="submit">
            <input type="submit" value="送信" class="btn" />
        </div>
    </form> 
    <div>
        <!-- ここでDBに登録した画像パスから画像を表示している -->
        <?php foreach ($files as $file): ?>
        <img src="<?php echo "{$file['file_path']}"; ?>" alt="">
        <p><?php echo h("{$file['caption']}"); ?></p>
        <?php endforeach;?>
    </div>
</body>

</html>