<!-- update.php -->
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

// Ambil data user berdasarkan ID
$id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Record ID not found.');
$user->readOne($id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = htmlspecialchars($_POST["nama_lengkap"]); //Sanitize inputs
    $user_name = htmlspecialchars($_POST["user"]);
    $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
    $is_aktif = isset($_POST["is_aktif"]) ? 1 : 0;
    $level_akses = htmlspecialchars($_POST["level_akses"]);
    $foto = $user->foto; //Pertahankan foto lama secara default
    $uploadOk = 1;

    // Tangani unggahan foto (mirip dengan create.php)
	if($_FILES["foto"]["name"]){
		$target_dir = "uploads/"; // Direktori untuk menyimpan foto
		$target_file = $target_dir . basename($_FILES["foto"]["name"]);
		$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
	
		//Periksa file adalah gambar
		$check = getimagesize($_FILES["foto"]["tmp_name"]);
		if($check === false) {
			echo "File bukan gambar.";
			$uploadOk = 0;
		}
	
		//Batasi ukuran file (contoh: 2MB)
		if ($_FILES["foto"]["size"] > 2000000) {
			echo "Maaf, ukuran file terlalu besar.";
			$uploadOk = 0;
		}
	
		//Izinkan hanya format gambar tertentu
		$allowedFormats = array("jpg","jpeg","png","gif");
		if(!in_array($imageFileType, $allowedFormats)) {
			echo "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
			$uploadOk = 0;
		}
		 //Coba unggah file
		if ($uploadOk == 1) {
			if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
				$foto = htmlspecialchars(basename($_FILES["foto"]["name"])); // Nama file untuk disimpan ke database
			} else {
				echo "Maaf, terjadi kesalahan saat mengunggah file.";
				$foto = $user->foto; //Pertahankan foto lama jika unggah baru gagal
			}
		}
	}

    if ($user->update($id, $nama_lengkap, $user_name, $email, $is_aktif, $level_akses,$foto)) {
        header("Location: index.php");
        exit();
    } else {
        echo "Gagal memperbarui user.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h1>Edit User</h1>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id={$id}"); ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($user->nama_lengkap, ENT_QUOTES); ?>" required>
            </div>
            <div class="form-group">
                <label for="user">Username:</label>
                <input type="text" class="form-control" id="user" name="user" value="<?php echo htmlspecialchars($user->user, ENT_QUOTES); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user->email, ENT_QUOTES); ?>" required>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif" <?php echo ($user->is_aktif == 1) ? "checked" : ""; ?>>
                    <label class="form-check-label" for="is_aktif">Aktif</label>
                </div>
            </div>
            <div class="form-group">
                <label for="level_akses">Level Akses:</label>
                <select class="form-control" id="level_akses" name="level_akses">
                    <option value="user" <?php echo ($user->level_akses == 'user') ? "selected" : ""; ?>>User</option>
                    <option value="admin" <?php echo ($user->level_akses == 'admin') ? "selected" : ""; ?>>Admin</option>
                </select>
            </div>
			<div class="form-group">
                <label for="foto">Foto:</label>
                <input type="file" class="form-control-file" id="foto" name="foto">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>