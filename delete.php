<!-- delete.php -->
<?php
session_start(); // Pastikan session_start ada di setiap halaman

require_once "config.php";
require_once "Database.php";
require_once "User.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: login.php");
	exit();
}

global $host, $username, $password, $database;

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Record ID not found.');

if ($user->delete($id)) {
    header("Location: index.php");
    exit();
} else {
    echo "Gagal menghapus user.";
}
?>