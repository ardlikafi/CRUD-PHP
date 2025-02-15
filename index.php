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

//Proses Pencarian
$search_term = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';

//Proses Paginasi
$records_per_page = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $records_per_page;

//Ambil Data Dari Database Dengan Limit
$stmt = $user->readAll($search_term, $start_from, $records_per_page);
$num = $stmt->rowCount();

//Ambil Total Record Tanpa Limit Untuk Paginasi
$total_rows = $user->countAll($search_term);
$total_pages = ceil($total_rows / $records_per_page);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>

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
        <!-- Form Pencarian -->
        <form method="GET" action="index.php" class="mb-3">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Cari nama, email, atau username" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i> Cari
                    </button>
                </div>
            </div>
        </form>

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
                        echo "<td>" . (is_null($id) ? '' : htmlspecialchars($id, ENT_QUOTES)) . "</td>";
                        echo "<td>" . (is_null($nama_lengkap) ? '' : htmlspecialchars($nama_lengkap, ENT_QUOTES)) . "</td>";
                        echo "<td>" . (is_null($user) ? '' : htmlspecialchars($user, ENT_QUOTES)) . "</td>";
                        echo "<td>" . (is_null($email) ? '' : htmlspecialchars($email, ENT_QUOTES)) . "</td>";
                        echo "<td>" . ($is_aktif ? 'Ya' : 'Tidak') . "</td>";
                        echo "<td>" . (is_null($level_akses) ? '' : htmlspecialchars($level_akses, ENT_QUOTES)) . "</td>";
                        echo "<td>";
                            if($foto){
                                echo "<a href='#' class='openImage' data-toggle='modal' data-target='#imageModal'>";
                                echo "<img src='uploads/" . (is_null($foto) ? '' : htmlspecialchars($foto, ENT_QUOTES)) . "' alt='Foto User' class='foto-user'>";
                                echo "</a>";
                            } else {
                                echo "Tidak Ada Foto";
                            }
                        echo "</td>";
                        echo "<td>" . (is_null($last_login) ? '' : htmlspecialchars($last_login, ENT_QUOTES)) . "</td>";
                        echo "<td>" . (is_null($last_ip) ? '' : htmlspecialchars($last_ip, ENT_QUOTES)) . "</td>";
                        echo "<td>" . htmlspecialchars($create_at, ENT_QUOTES) . "</td>";
                        echo "<td>" . (is_null($last_login) || empty($last_login) ? '' : htmlspecialchars($last_login, ENT_QUOTES)) . "</td>";
                        echo "<td>
                                <a href='update.php?id={$id}' class='btn btn-sm btn-primary mb-1'>Edit</a>
                                <button type='button' class='btn btn-sm btn-danger deleteBtn' data-id='{$id}'>Hapus</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>Tidak ada data ditemukan.</td></tr>";
                }
                ?>
            </tbody>
        </table>

         <!-- Paginasi -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?page=<?php echo ($page - 1) . '&search=' . htmlspecialchars($search_term); ?>" aria-label="Previous">
                                <span aria-hidden="true">«</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="index.php?page=<?php echo $i . '&search=' . htmlspecialchars($search_term); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="index.php?page=<?php echo ($page + 1) . '&search=' . htmlspecialchars($search_term); ?>" aria-label="Next">
                                <span aria-hidden="true">»</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

	      <div class="mt-3">
            <?php 
                if (isset($_SESSION["user_id"]) && $_SESSION["level_akses"] == "admin") {
                    echo '<a href="create.php" class="btn btn-primary mr-3">Tambah User Baru</a>';
                }
            ?>
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
 $(document).ready(function() {
        // Fungsi untuk menangani klik tombol delete
        $('.deleteBtn').on('click', function(e) {
            e.preventDefault(); // Menghentikan aksi default tombol

            var userId = $(this).data('id'); // Ambil ID user dari data-id
            // Tampilkan SweetAlert2
            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Kamu tidak akan bisa mengembalikan tindakan ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus saja!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user menekan tombol "Ya, hapus saja!", redirect ke delete.php dengan ID
                    window.location.href = 'delete.php?id=' + userId;
                }
            });
        });

		$('.openImage').click(function(){
                var imageSrc = $(this).find('img').attr('src');
                $('#modalImage').attr('src', imageSrc);
                $('#imageModal').modal('show');
            });
    });
    </script>
</body>
</html>