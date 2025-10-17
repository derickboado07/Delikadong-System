<?php
include 'backend/db_connect.php';

$res = $conn->query('DESCRIBE orders');
if($res){
    echo 'orders table structure:' . PHP_EOL;
    while($r = $res->fetch_assoc()){
        echo '  ' . $r['Field'] . ' ' . $r['Type'] . ' ' . ($r['Null']=='NO'?'NOT NULL':'NULL') . ' ' . ($r['Key']?'KEY':'') . ' ' . ($r['Default']!==null?'DEFAULT '.$r['Default']:'') . ' ' . ($r['Extra']?'EXTRA':'') . PHP_EOL;
    }
} else {
    echo 'orders table does not exist.' . PHP_EOL;
}
?>
