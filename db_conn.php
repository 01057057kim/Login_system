<?php
$user = 'root';
$password = '123456789';
try {
    $db = new
        PDO('mysql:host=localhost;dbname=login_system;charset=utf8', $user, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    print "ERROR!:" . $e->getMessage();
    die();
}
?>