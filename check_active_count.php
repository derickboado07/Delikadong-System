<?php
require_once 'backend/db_connect.php';

$res = $conn->query("SELECT COUNT(*) as cnt FROM menu WHERE status = 'active'");
if($res){
    $r = $res->fetch_assoc();
    echo 'Active products: ' . $r['cnt'] . "\n";
} else {
    echo 'Query failed.' . "\n";
}

$conn->close();
?>
