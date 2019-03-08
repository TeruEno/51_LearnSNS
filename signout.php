<?php

session_start();
// セッションにユーザーのIDが保持されていたらサインインしている状態
// セッション変数の破棄
// ブラウザから破棄
$_SESSION = [];
// サーバ内のセッション変数クリア
// サーバから破棄
session_destroy();

header('Location: signin.php');
exit();
