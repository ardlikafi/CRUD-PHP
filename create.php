<!-- create.php -->
<?php
session_start();

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

$email_err = $user_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_lengkap = htmlspecialchars($_POST["nama_lengkap"]);
    $user_name = htmlspecialchars($_POST["user"]);
    $pass = $_POST["pass"];
    $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
    $is_aktif = isset($_POST["is_aktif"]) ? 1 : 0;
    $level_akses = htmlspecialchars($_POST["level_akses"]);
    $target_dir = "uploads/"; // Direktori untuk menyimpan foto
    $foto = null;
	$uploadOk = 1;

    // Validasi username
    if ($user->checkIfUserExists($user_name)) {
        $user_err = "Username sudah digunakan.";
        $uploadOk = 0; //Tambahkan pesan ini supaya tidak ke insert
    }

    // Validasi email
    if ($user->checkIfEmailExists($email)) {
        $email_err = "Email sudah terdaftar.";
        $uploadOk = 0; //Tambahkan pesan ini supaya tidak ke insert
    }

    // Validasi untuk Foto, jika ada
	if($_FILES["foto"]["name"]){
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
				$uploadOk = 0;
				$foto = null;
			}
		}
	}

    if ($uploadOk == 1 && $user->create($nama_lengkap, $user_name, $pass, $email, $is_aktif, $level_akses,$foto)) {
        header("Location: index.php");
        exit();
    } else {
        $upload_err = "Gagal menambahkan user. Mohon periksa kembali input Anda.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah User Baru</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Tambah User Baru</h1>
  <!--Alert jika ERROR-->
    <?php if (!empty($user_err) || !empty($email_err) || !empty($upload_err)) : ?>
	<div class="alert alert-danger alert-dismissible fade show" role="alert">
		<?php echo $user_err; ?>
		<?php echo $email_err; ?>
		<?php echo $upload_err; ?>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">×</span>
		</button>
	</div>
	<?php endif; ?>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="form-group">
                <label for="user">Username:</label>
                <input type="text" class="form-control" id="user" name="user" required>
                <span class="text-danger"><?php echo $user_err; ?></span>
            </div>
            <div class="form-group">
                <label for="pass">Password:</label>
                <input type="password" class="form-control" id="pass" name="pass" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
                 <span class="text-danger"><?php echo $email_err; ?></span>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif">
                    <label class="form-check-label" for="is_aktif">Aktif</label>
                </div>
            </div>
            <div class="form-group">
                <label for="level_akses">Level Akses:</label>
                <select class="form-control" id="level_akses" name="level_akses">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
			<div class="form-group">
                <label for="foto">Foto:</label>
                <input type="file" class="form-control-file" id="foto" name="foto">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</body>
</html>