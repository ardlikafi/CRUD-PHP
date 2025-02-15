<?php
require_once "config.php";
require_once "Database.php";
require_once "User.php";

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if (isset($_POST['user'])) {
    $username = htmlspecialchars($_POST['user']);
    if ($user->checkIfUserExists($username)) {
        echo 'exists';
    } else {
        echo 'available';
    }
}
?>