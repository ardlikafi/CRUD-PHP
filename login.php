<?php
// login.php
session_start();

require_once "config.php";
require_once "Database.php";
require_once "User.php";

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_name = htmlspecialchars($_POST["user"]);
    $pass = $_POST["pass"];

    $result = $user->login($user_name, $pass);

    if ($result) {
        // Login berhasil
        $_SESSION["user_id"] = $result['id'];
        $_SESSION["user_name"] = $result['nama_lengkap'];
        $_SESSION["level_akses"] = $result['level_akses'];

        // Perbarui last_login dan last_ip
        $id = $result['id'];
        $last_login = date('Y-m-d H:i:s'); // Waktu saat ini
        $last_ip = $_SERVER['REMOTE_ADDR']; // Alamat IP user

        $query = "UPDATE tb_user SET last_login = :last_login, last_ip = :last_ip WHERE id = :id";
        $stmt = $db->prepare($query);

        $stmt->bindParam(":last_login", $last_login);
        $stmt->bindParam(":last_ip", $last_ip);
        $stmt->bindParam(":id", $id);

        if ($stmt->execute()) {
            header("Location: index.php"); // Redirect ke halaman utama
            exit();
        } else {
            echo "Gagal memperbarui last_login dan last_ip.";
        }

    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></div>
        <?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="form-group">
                <label for="user">Username:</label>
                <input type="text" class="form-control" id="user" name="user" required>
            </div>
            <div class="form-group">
                <label for="pass">Password:</label>
                <input type="password" class="form-control" id="pass" name="pass" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>
</body>
</html>