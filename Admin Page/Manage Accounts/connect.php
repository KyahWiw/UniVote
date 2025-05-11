<?php
try {
    $db=new PDO("mysql:host=localhost;dbname=univote_accounts;charset=utf8","root","");
} catch (PDOException $e) {
    echo $e->getMessage();
}

?>