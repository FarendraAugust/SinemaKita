<?php
include 'connection.php';

$connection->query("
    DELETE FROM pengguna 
    WHERE verified = 0 AND verify_expire < NOW()
");
?>
