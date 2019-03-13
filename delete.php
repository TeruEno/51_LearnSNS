<?php

require 'dbconnect.php';
//1
$feed_id = $_GET['feed_id'];

//2
$sql = 'DELETE FROM `feeds` WHERE `id` = ?';
$data = [$feed_id];
$stmt = $dbh->prepare($sql);
$stmt->execute($data);

//3
header('Location: timeline.php');
exit();
