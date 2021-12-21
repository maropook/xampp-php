<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=mydb;charset=utf8", "root", "");
    //ファイルアップロードがあったとき
    if (isset($_FILES['upfile']['error']) && is_int($_FILES['upfile']['error']) && $_FILES["upfile"]["name"] !== "") {
        //画像・動画をバイナリデータにする．
        $raw_data = file_get_contents($_FILES['upfile']['tmp_name']);
        //拡張子を見る
        $tmp = pathinfo($_FILES["upfile"]["name"]);
        $extension = $tmp["extension"];
        if ($extension === "jpg" || $extension === "jpeg" || $extension === "JPG" || $extension === "JPEG") {
            $extension = "jpeg";
        } elseif ($extension === "png" || $extension === "PNG") {
            $extension = "png";
        } elseif ($extension === "gif" || $extension === "GIF") {
            $extension = "gif";
        } elseif ($extension === "mp4" || $extension === "MP4") {
            $extension = "mp4";
        } else {
            echo "非対応ファイルです．<br/>";
            echo ("<a href=\"index.php\">戻る</a><br/>");
            exit(1);
        }
        //DBに格納するファイルネーム設定
        //サーバー側の一時的なファイルネームと取得時刻を結合した文字列にsha256をかける．
        $date = getdate();
        $fname = $_FILES["upfile"]["tmp_name"];
        //$fname = hash("sha256", $fname);
        $name = $_POST['name'];
        $content = nl2br($_POST['content']);
        //画像・動画をDBに格納．
        $sql = "INSERT INTO movie(fname, extension, raw_data, name, content) VALUES (:fname, :extension, :raw_data, :name, :content);";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->bindValue(":fname", $fname, PDO::PARAM_STR);
        $stmt->bindValue(":extension", $extension, PDO::PARAM_STR);
        $stmt->bindValue(":raw_data", $raw_data, PDO::PARAM_STR);
        $stmt->execute();
    } elseif (isset($_POST['delete'])) {
        $id = $_POST['id'];
        $sth = $pdo->prepare("delete from movie where id = :id");
        $sth->bindValue(':id', $id, PDO::PARAM_INT);
        $sth->execute();
    } elseif (isset($_POST['search'])) {
        $dsn = "mysql:host=localhost;dbname=mydb;charset=utf8";
        $username = "root";
        $password = "";
        $options = [];
        $pdo = new PDO($dsn, $username, $password, $options);
        if (@$_POST["name_search"] != "" or @$_POST["content"] != "") { //contentおよびユーザー名の入力有無を確認
            $_POST["content"] = nl2br($_POST["content"]);
            $st = $pdo->query("SELECT * FROM movie WHERE name LIKE '%" . $_POST["name_search"] . "%' AND content LIKE  '%" . $_POST["content"] . "%'");
        }
    } else if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $name = $_POST['name_edit'];
        $content = $_POST['content_edit'];
        $sql = "UPDATE movie SET name = :name  , content = :content WHERE  id =:id;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->execute();
    }   else if (isset($_POST['good'])) {


if($_POST['good']==0){

    $good = 1;
}else{
    $good = 0;
}
        $id = $_POST['id'];


        $img ="heart.png";
        $sql = "UPDATE movie SET good = :good WHERE  id =:id;";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':good', $good, PDO::PARAM_STR);
        $stmt->execute();
    }
    else if (isset($_POST['comment'])) {
        $movie_id = $_POST['id'];
        $comment = $_POST['comment'];
        $sql = "INSERT INTO comment(comment,movie_id) VALUES (:comment, :movie_id);";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':movie_id', $movie_id, PDO::PARAM_INT);
        $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();

        // $sql = "INSERT INTO movie(fname, extension, raw_data, name, content) VALUES (:fname, :extension, :raw_data, :name, :content);";
        // $stmt = $pdo->prepare($sql);
        // $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        // $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        // $stmt->bindValue(":fname", $fname, PDO::PARAM_STR);
        // $stmt->bindValue(":extension", $extension, PDO::PARAM_STR);
        // $stmt->bindValue(":raw_data", $raw_data, PDO::PARAM_STR);
        // $stmt->execute();

    }
} catch (PDOException $e) {
    echo ("<p>500 Inertnal Server Error</p>");
    exit($e->getMessage());
}
?>

<!DOCTYPE HTML>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <title>掲示板</title>

    <head>
        <link rel="stylesheet" href="//cdn.rawgit.com/necolas/normalize.css/master/normalize.css">
        <link rel="stylesheet" href="//cdn.rawgit.com/milligram/milligram/master/dist/milligram.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <style>
    </style>

<body>
    <div id="content">
        <header>
            <h1 class="headline">
                <a href="index.php">掲示板</a>
            </h1>
            <ul class="nav-list">
                <li class="nav-list-item"><a href="post.php">Post</a></li>
                <li class="nav-list-item"><a href="search.php">Search</a></li>
                <li class="nav-list-item"><a href="top.php">New</a></li>
            </ul>
        </header>
        <div id="container">
            <main>
                <section id="post">
                    <h2>
                        <a>投稿</a>
                    </h2>
                    <form action="index.php" enctype="multipart/form-data" method="post">
                        名前: <input type="text" name="name">
                        内容:<textarea name="content"></textarea>
                        <div class='body_file'>
                            <label for="file" class="filelabel">動画/画像選択</label>
                            <input type="file" name="upfile" id="file" class="fileinput">
                            jpeg，png，gif,mp4方式のみ対応
                        </div>
                        <br>
                        <div class="btn_submit">
                            <input type="submit" value="アップロード">
                        </div>
                    </form>
                </section>
                <section id="search">
                    <h2>
                        <a>検索</a>
                    </h2>

                    <form method="post">
                        名前:<input type="text" name="name_search" value="<?php echo $_POST['name_search'] ?>">
                        内容:<textarea name="content" value="<?php echo $_POST['content'] ?>"></textarea>
                        <button class="btn_search" type="submit" name="search">検索</button>
                    </form>
                    <h3>
                        <a>検索結果</a>
                    </h3>

                    <h4>
                        <a>名前:<?php echo ($_POST['name_search']); ?></a>
                    </h4>
                    <h4>
                        <a>内容:<?php echo ($_POST['content']); ?></a>
                    </h4>
                    <?php foreach ($st as $row) : ?>

                        <td><?= htmlspecialchars($row['name']) ?>
                            <!-- htmlspecialcharsとはセキュリティーのためにこの中身を完全な文字列として認識させる -->
                            <?php echo ($row['timestamp']); ?>
                        </td><br>
                        <td><?= $row['content'] ?></td>

                        <td><?= $row['good'] ?></td>
                        <img style="width:40px" src="<?= $row['good'] ?>.png" alt="">

                        <?php
                        //動画と画像で場合分け
                        $target = $row["fname"];
                        if ($row["extension"] == "mp4") {
                            echo ("<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>");
                        } elseif ($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif") {
                            echo ("<div class='image'><img class='image'  src='import_media.php?target=$target'></div>");
                        }
                        ?>
                        <div>
                            <form method="POST">
                                <button class="btn_right" type="submit" name="delete">削除</button>
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="delete" value="true">
                            </form>
                        </div>


                    <?php endforeach; ?>
                </section>
                <section id="top">
                    <h2>
                        <a>一覧</a>
                    </h2>
                        <?php
                    //DBから取得して表示する．
                    $sql = "SELECT * FROM movie ORDER BY id;";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute();
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <div class="twiit">
                        <div><?= htmlspecialchars($row['name']) ?>
                            <?php echo ($row['timestamp']); ?></div>
                        <div><?= $row['content'] ?></div>
                        <?php
                        $target = $row["fname"];
                        if ($row["extension"] == "mp4") {
                            echo ("<video src=\"import_media.php?target=$target\" width=\"426\" height=\"240\" controls></video>");
                        } elseif ($row["extension"] == "jpeg" || $row["extension"] == "png" || $row["extension"] == "gif") {
                            echo ("<img src='import_media.php?target=$target'>");
                        }?>
                        <div>
                            <form method="POST">
                                <button class="btn_right" type="submit" name="delete">削除</button>
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="delete" value="true">
                            </form>
                        </div>
                        <div>
                            <form method="POST">
                                <button class="btn_right" type="submit" name="edit">編集</button>
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                名前:<input type="text" name="name_edit" value="<?php echo $_POST['name_edit'] ?>">
                                内容:<textarea name="content_edit" value="<?php echo $_POST['content_edit'] ?>"></textarea>

                            </form>
                            <form method="POST">
                            <div style="display:flex">
                             <span style="padding-left:0px"><?= $row['good'] ?></span>
                                <img style="width:20px;height:20px;" src="<?= $row['good'] ?>.png" alt="">

                            </div>
                            <button class="btn_right" type="submit" name="good">いいね</button>

                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="hidden" name="good" value="<?= $row['good'] ?>">
                            </form>

                   <?php
                        $comment_sql = "SELECT * FROM comment ORDER BY id;";
                        $comment_stmt = $pdo->prepare($comment_sql);
                        $comment_stmt->execute();
                        ?>
                        <p>コメント</p>
                        <?php  while ($comment_row = $comment_stmt->fetch(PDO::FETCH_ASSOC)) {
                            if ( $comment_row['movie_id'] ==$row['id']){
                           ?>
                         <div> <?= $comment_row['comment'] ?></div>
                        <?php
                        } ?>
                        <?php
                        } ?> <?php
                    ?>
                    <form method="POST">
                                <button class="btn_right" type="submit" name="comment">コメント</button>
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="text" name="comment" value="<?= $row['comment'] ?>">
                            </form>
                        </div>
                     </div>

                         <?php } ?>
                </section>
            </main>
        </div>
    </div>
</body>

</html>
