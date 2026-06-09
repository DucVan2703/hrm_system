<?php

$host = "fdb1032.awardspace.net";
$db   = "4762137_quanlyluong";
$user = "4762137_quanlyluong";
$pass = "Nguyenvan272005@";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass
    );

    echo "KET NOI THANH CONG";
}
catch(PDOException $e){
    echo $e->getMessage();
}