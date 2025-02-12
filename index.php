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

// Ambil data dari database
$stmt = $user->read();
$num = $stmt->rowCount();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .foto-user {
            max-width: 50px;
            max-height: 50px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Data User</h1>

        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Aktif</th>
                    <th>Level Akses</th>
                    <th>Foto</th>
                    <th>Last Login</th>
                    <th>Last IP</th>
                    <th>Create At</th>
                    <th>Update At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if($num>0){
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                        extract($row);
                        echo "<tr>";
                        echo "<td>" . ($id? htmlspecialchars($id, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . ($nama_lengkap? htmlspecialchars($nama_lengkap, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . ($user? htmlspecialchars($user, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . ($email? htmlspecialchars($email, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . ($is_aktif ? 'Ya' : 'Tidak') . "</td>";
                        echo "<td>" . ($level_akses? htmlspecialchars($level_akses, ENT_QUOTES) : '') . "</td>";
						echo "<td>";
							if($foto){
								echo "<a href='#' class='openImage' data-toggle='modal' data-target='#imageModal'>";
                                    echo "<img src='uploads/" . htmlspecialchars($foto, ENT_QUOTES) . "' alt='Foto User' class='foto-user'>";
                                echo "</a>";
							} else {
								echo "Tidak Ada Foto";
							}
						echo "</td>";
                        echo "<td>" . ($last_login ? htmlspecialchars($last_login, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . ($last_ip ? htmlspecialchars($last_ip, ENT_QUOTES) : '') . "</td>";
                        echo "<td>" . htmlspecialchars($create_at, ENT_QUOTES) . "</td>";
                        echo "<td>" . htmlspecialchars($update_at, ENT_QUOTES) . "</td>";
                        echo "<td>
                                <a href='update.php?id={$id}' class='btn btn-sm btn-primary mb-1'>Edit</a>
                                <a href='delete.php?id={$id}' class='btn btn-sm btn-danger' onclick=\"return confirm('Apakah Anda yakin ingin menghapus user ini?')\">Hapus</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>Tidak ada data ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="mt-3">
            <a href="create.php" class="btn btn-primary mr-3">Tambah User Baru</a>
            <a href="#" class="btn btn-secondary logoutBtn" data-toggle="modal" data-target="#logoutConfirmationModal">Logout</a>
        </div>
    </div>

    <!-- Modal Konfirmasi Logout -->
    <div class="modal fade" id="logoutConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="logoutConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutConfirmationModalLabel">Konfirmasi Logout</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <a href="logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tampilan Foto -->
    <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="imageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imageModalLabel">Foto User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <img src="" id="modalImage" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $('.openImage').click(function(){
                var imageSrc = $(this).find('img').attr('src');
                $('#modalImage').attr('src', imageSrc);
                $('#imageModal').modal('show');
            });

           $('.deleteBtn').click(function() {
                var userId = $(this).data('userid');
                $('#deleteConfirmBtn').attr('href', 'delete.php?id=' + userId);
            });
        });
    </script>
</body>
</html>