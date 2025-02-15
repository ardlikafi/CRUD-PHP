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

$email_err = $user_err = $upload_err = $general_err = "";
$success = false;

// Batasan dimensi gambar (Konstanta)
define("MAX_WIDTH", 500);
define("MAX_HEIGHT", 500);
define("MAX_FILE_SIZE", 2000000); //2MB
define("ALLOWED_FORMATS", array("jpg","jpeg","png","gif"));

$max_width = MAX_WIDTH;
$max_height = MAX_HEIGHT;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     // Validasi bahwa semua field wajib ada
    $required_fields = ["nama_lengkap", "user", "pass", "email", "level_akses"];
    $missing_fields = array_filter($required_fields, function ($field) {
        return empty($_POST[$field]);
    });

    if (!empty($missing_fields) || empty($_FILES["foto"]["name"])) {
        $general_err = "Semua field wajib diisi, termasuk foto.";
    } else {

        $nama_lengkap = htmlspecialchars($_POST["nama_lengkap"]);
        $user_name = htmlspecialchars($_POST["user"]);
        $pass = $_POST["pass"];
        $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
        $is_aktif = isset($_POST["is_aktif"]) ? 1 : 0;
        $level_akses = htmlspecialchars($_POST["level_akses"]);
        $target_dir = "uploads/";
        $foto = null;
        $uploadOk = 1;

        // Validasi username
        if ($user->checkIfUserExists($user_name)) {
            $user_err = "Username sudah digunakan.";
            $uploadOk = 0;
        }

        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Format email tidak valid.";
            $uploadOk = 0;
        } elseif ($user->checkIfEmailExists($email)) {
            $email_err = "Email sudah terdaftar.";
            $uploadOk = 0;
        }

         // Validasi untuk Foto
        $file_name = $_FILES["foto"]["name"];
        $file_size = $_FILES["foto"]["size"];
        $file_tmp = $_FILES["foto"]["tmp_name"];
        $file_type = strtolower(pathinfo($target_dir . $file_name, PATHINFO_EXTENSION));

        // Validasi file diunggah
        if (empty($file_name)) {
            $upload_err = "Foto wajib diunggah.";
            $uploadOk = 0;
        }else {
            //Periksa file adalah gambar
             $check = @getimagesize($file_tmp);

            if ($check === false) {
                $upload_err = "File bukan gambar.";
                $uploadOk = 0;
            } else {
                // Validasi dimensi gambar
                if ($check[0] > MAX_WIDTH || $check[1] > MAX_HEIGHT) {
                    $upload_err = "Dimensi gambar terlalu besar. Maksimal " . MAX_WIDTH . "x" . MAX_HEIGHT . " pixel.";
                    $uploadOk = 0;
                }
            }

             // Validasi ukuran file
            if ($file_size > MAX_FILE_SIZE) {
                $upload_err = "Maaf, ukuran file terlalu besar.";
                $uploadOk = 0;
            }

             // Validasi jenis file
            if (!in_array($file_type, ALLOWED_FORMATS)) {
                $upload_err = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
                $uploadOk = 0;
            }

              // Coba unggah file
            if ($uploadOk == 1) {
                $target_file = $target_dir . uniqid('IMG-', true) . '.' . $file_type; //Unique name
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $foto = htmlspecialchars(basename($target_file)); //Simpan nama file ke database
                } else {
                    $upload_err = "Maaf, terjadi kesalahan saat mengunggah file.";
                    $uploadOk = 0;
                    $foto = null;
                }
            }
        }

        if ($uploadOk == 1 && empty($user_err) && empty($email_err) && empty($upload_err)) {
            if ($user->create($nama_lengkap, $user_name, $pass, $email, $is_aktif, $level_akses,$foto)) {
                $success = true;
            } else {
                $general_err = "Gagal menambahkan user.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah User Baru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <style>
        .text-danger {
            color: red;
        }
        #previewFoto {
            max-width: 200px;
            margin-top: 10px;
        }
        .is-invalid + .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body  aria-label="Form Tambah User Baru">
    <div class="container">
        <h1 >Tambah User Baru</h1>
        <!--Alert jika ERROR-->
        <?php if (!empty($general_err)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $general_err; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        <?php endif; ?>

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
         <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data" id="formTambahUser" novalidate>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required aria-required="true">
                 <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
            </div>
            <div class="form-group">
                <label for="user">Username: <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="user" name="user" required aria-required="true">
                <span class="text-danger"><?php echo $user_err; ?></span>
                <div class="invalid-feedback">Username wajib diisi.</div>
            </div>
            <div class="form-group">
                <label for="pass">Password: <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="pass" name="pass" required aria-required="true">
                 <div class="invalid-feedback">Password wajib diisi.</div>
            </div>
            <div class="form-group">
                <label for="email">Email: <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" required aria-required="true">
                 <span class="text-danger"><?php echo $email_err; ?></span>
                <div class="invalid-feedback">Email wajib diisi.</div>
            </div>
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_aktif" name="is_aktif">
                    <label class="form-check-label" for="is_aktif">Aktif</label>
                </div>
            </div>
            <div class="form-group">
                <label for="level_akses">Level Akses: <span class="text-danger">*</span></label>
                <select class="form-control" id="level_akses" name="level_akses" required aria-required="true">
                    <option value="">Pilih Level Akses</option>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                 <div class="invalid-feedback">Level akses wajib dipilih.</div>
            </div>
            <div class="form-group">
                <label for="foto">Foto: <span class="text-danger">*</span></label>
                <input type="file" class="form-control-file" id="foto" name="foto" required aria-required="true" accept="image/*">
                 <div class="invalid-feedback">Foto wajib diunggah.</div>
                <small class="text-muted">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal 2MB. Dimensi maksimal: <?php echo MAX_WIDTH . "x" . MAX_HEIGHT; ?> pixel.</small>
                <img id="previewFoto" src="#" alt="Preview Foto" style="display:none;">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="index.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>
    <script>
        $(document).ready(function () {

            // Preview Foto
            $("#foto").change(function() {
                previewImage(this);
            });

            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function(e) {
                        $('#previewFoto').attr('src', e.target.result);
                        $('#previewFoto').css('display', 'block');
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

              //Validasi Realtime Username
            $('#user').on('blur', function() {
                var username = $(this).val();
                var userField = $(this);

                if (username) {
                    $.ajax({
                        url: 'check_username.php', // Ganti dengan URL yang sesuai
                        type: 'POST',
                        data: {user: username},
                        success: function(response) {
                            if (response === 'exists') {
                                userField.addClass('is-invalid');
                                if (!$('#username-error').length) {
                                    userField.after('<div id="username-error" class="invalid-feedback">Username sudah digunakan.</div>');
                                }
                            } else {
                                userField.removeClass('is-invalid');
                                $('#username-error').remove();
                            }
                        }
                    });
                }
            });

                //Sweet Alert
            <?php if ($success): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: 'User berhasil ditambahkan.',
                    showConfirmButton: false,
                    timer: 1500
                }).then((result) => {
                    window.location.href = 'index.php';
                    $('#formTambahUser')[0].reset();
                    $('#previewFoto').attr('src', '#').hide();
                });
             <?php endif; ?>

              //Bootstrap Form Validation
                (function() {
                    'use strict';
                    window.addEventListener('load', function() {
                        var form = document.getElementById('formTambahUser');
                        form.addEventListener('submit', function(event) {
                            if (form.checkValidity() === false) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    }, false);
                })();
        });
    </script>
</body>
</html>