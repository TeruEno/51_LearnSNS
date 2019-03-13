<?php
session_start();
require 'dbconnect.php';
// サインインしていないユーザーのアクセス禁止
if (!isset($_SESSION['51_LearnSNS']['id'])) {
    header('Location: signin.php');
    exit();
}
// サインインしているユーザーの情報を取得
$sql = 'SELECT * FROM `users` WHERE `id` = ?';
// サインイン処理時にセッションに保持したユーザーのIDで絞り込む
$data = [$_SESSION['51_LearnSNS']['id']];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);
$signin_user = $stmt->fetch(PDO::FETCH_ASSOC);
// echo '<pre>';
// var_dump($signin_user);
// echo '</pre>';
// エラー内容を保持する配列
$errors = [];
// POST送信されたとき
if (!empty($_POST)) {
    $feed = $_POST['feed'];
    // 空チェック
    if ($feed != '') {
        // 投稿処理
        // NOW()
        // SQLの組み込み関数
        // 現在日時を取得
        $sql = 'INSERT INTO `feeds` (`feed`, `user_id`, `created`) VALUES (?, ?, NOW())';
        // $data = [$feed, $signin_user['id']];
        $data = [$feed, $_SESSION['51_LearnSNS']['id']];
        // オブジェクト指向の範囲
        // インスタンス->メンバメソッド(引数)
        // 主体->振る舞い(利用するもの)
        // $human->walk();
        // $human->eat($meat);
        $stmt = $dbh->prepare($sql);
        $stmt->execute($data);
        // 登録処理が終わったらタイムライン画面に再遷移
        // この処理を入れないとtimeline.phpにPOST送信で留まることになる
        // ブラウザ更新するたびにフォームが送信されてしまう
        header('Location: timeline.php');
        exit();
    } else {
        // エラー
        $errors['feed'] = 'blank';
    }
}
// 投稿情報の取得
$sql = '
    SELECT `f`.*, `u`.`name`, `u`.`img_name`
    FROM `feeds` AS `f`
    JOIN `users` AS `u`
    ON `f`.`user_id` = `u`.`id`
    ORDER BY `f`.`created` DESC
';
$stmt = $dbh->prepare($sql);
$stmt->execute();
// 投稿情報を格納する配列
$feeds = [];
// あるだけ取る
// 無限ループを意図的に発生させている
while (true) {
    // fetchは一行取得して次の行へ移る
    // $recordには一行分のデータが連想配列で入っている
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    // レコードが取得できないfalseが$recordに入る
    // つまり、それ以上レコードがない時なので、無限ループを抜ける
    if ($record == false) {
        break;
    }
    // レコードが取得できた場合は$feedsに$recordを追加
    // $配列名[] = 値;
    // NG：$配列名 = 値; 値で変数を上書いてしまう
    $feeds[] = $record;
}
echo '<pre>';
var_dump($feeds);
echo '</pre>';

// 宿題１：投稿件数分、投稿のブロックが繰り返されるように実装
// 宿題２：各投稿のブロックにDBの値が反映されるように実装
// 条件：foreach文と$feedsを利用すること
?>
<?php include 'layouts/header.php'; ?>
<body style="margin-top: 60px; background: #E4E6EB;">
    <!--
        include(ファイル名);
        指定されたファイルの内容をそのまま差し込む
        同じレイアウトを複数画面から利用する時などに利用
        ※includeとrequireの違い
            エラー発生時の挙動が異なる
            include：Warningとなり、処理は続行
            require：Errorとなり、処理は中断
            不具合が発生しても重要な問題にならないものはinclude
            DB接続など重要な機能に関してはrequire
        読み込み元で利用可能な変数は、読み込まれたファイルの中でも使用可能
    -->

    <?php include 'navbar.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-xs-3">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="timeline.php?feed_select=news">新着順</a></li>
                    <li><a href="timeline.php?feed_select=likes">いいね！済み</a></li>
                </ul>
            </div>

            <div class="col-xs-9">
                <div class="feed_form thumbnail">
                    <form method="POST" action="">
                        <div class="form-group">
                            <textarea name="feed" class="form-control" rows="3" placeholder="Happy Hacking!" style="font-size: 24px;"></textarea><br>

                            <?php if (isset($errors['feed']) && $errors['feed'] == 'blank'): ?>
                                <p class="text-danger">投稿内容を入力してください</p>
                            <?php endif; ?>

                        </div>
                        <input type="submit" value="投稿する" class="btn btn-primary">
                    </form>
                </div>
                <?php foreach ($feeds as $feed): ?>
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-1">
                            <img src="user_profile_img/<?php echo $feed['img_name']; ?>" width="40px">
                        </div>
                        <div class="col-xs-11">
                            <a href="profile.php" style="color: #7f7f7f;"><?php echo $feed['name']; ?></a>
                            <p><?php echo $feed['created']; ?></p>
                        </div>
                    </div>
                    <div class="row feed_content">
                        <div class="col-xs-12">
                            <span style="font-size: 24px;"><p><?php echo $feed['feed']; ?></p></span>
                        </div>
                    </div>
                    <div class="row feed_sub">
                        <div class="col-xs-12">
                            <button class="btn btn-default">いいね！</button>
                            いいね数：
                            <span class="like-count">10</span>
                            <?php if ($feed['user_id'] == $signin_user['id']): ?>
                            <a href="#collapseComment" data-toggle="collapse" aria-expanded="false"><span>コメントする</span></a>
                            <span class="comment-count">コメント数：5</span>
                            <a href="edit.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-success btn-xs">編集</a>
                            <a onclick="return confirm('ほんとに消すの？');" href="delete.php?feed_id=<?php echo $feed['id']; ?>" class="btn btn-danger btn-xs">削除</a>
                            <?php endif; ?>
                        </div>
                        <?php include 'comment_view.php'; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <div aria-label="Page navigation">
                    <ul class="pager">
                        <li class="previous disabled"><a><span aria-hidden="true">&larr;</span> Newer</a></li>
                        <li class="next disabled"><a>Older <span aria-hidden="true">&rarr;</span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
<?php include 'layouts/footer.php'; ?>
</html>