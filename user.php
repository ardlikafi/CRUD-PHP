<?php
require_once "Database.php";

class User {
    private $conn;
    private $table_name = "tb_user";

    public $id;
    public $nama_lengkap;
    public $user;
    public $pass;
    public $email;
    public $is_aktif;
    public $level_akses;
	public $foto;
    public $last_login;
    public $last_ip;
    public $create_at;
    public $update_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Read Data (Mengambil Data)
    public function read() {
        $query = "SELECT id, nama_lengkap, user, email, is_aktif, level_akses, last_login, last_ip, foto, create_at, update_at FROM " . $this->table_name . " ORDER BY nama_lengkap";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Create Data (Membuat Data Baru)
    public function create($nama_lengkap, $user, $pass, $email, $is_aktif, $level_akses, $foto) {
        $query = "INSERT INTO " . $this->table_name . " (nama_lengkap, user, pass, email, is_aktif, level_akses, foto, create_at) VALUES (:nama_lengkap, :user, :pass, :email, :is_aktif, :level_akses, :foto, NOW())";
        $stmt = $this->conn->prepare($query);

        // Sanitize data (Mencegah SQL Injection)
        $nama_lengkap = htmlspecialchars(strip_tags($nama_lengkap));
        $user = htmlspecialchars(strip_tags($user));
        $pass = password_hash($pass, PASSWORD_DEFAULT); // Hash password
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $is_aktif = (int)$is_aktif;
        $level_akses = htmlspecialchars(strip_tags($level_akses));
		$foto = htmlspecialchars(strip_tags($foto));

        // Bind parameters
        $stmt->bindParam(":nama_lengkap", $nama_lengkap);
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":pass", $pass);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":is_aktif", $is_aktif, PDO::PARAM_INT);
        $stmt->bindParam(":level_akses", $level_akses);
		$stmt->bindParam(":foto", $foto);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read One (Mengambil Satu Data)
    public function readOne($id) {
        $query = "SELECT id, nama_lengkap, user, email, is_aktif, level_akses, last_login, last_ip, foto, create_at, update_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->id = $row['id'];
        $this->nama_lengkap = $row['nama_lengkap'];
        $this->user = $row['user'];
        $this->email = $row['email'];
        $this->is_aktif = $row['is_aktif'];
        $this->level_akses = $row['level_akses'];
		$this->foto = $row['foto'];
        $this->last_login = $row['last_login'];
        $this->last_ip = $row['last_ip'];
        $this->create_at = $row['create_at'];
        $this->update_at = $row['update_at'];
    }

    // Update Data (Memperbarui Data)
    public function update($id, $nama_lengkap, $user, $email, $is_aktif, $level_akses, $foto) {
        $query = "UPDATE " . $this->table_name . " SET nama_lengkap = :nama_lengkap, user = :user, email = :email, is_aktif = :is_aktif, level_akses = :level_akses, foto = :foto, update_at = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        // Sanitize data
        $id = htmlspecialchars(strip_tags($id));
        $nama_lengkap = htmlspecialchars(strip_tags($nama_lengkap));
        $user = htmlspecialchars(strip_tags($user));
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        $is_aktif = (int)$is_aktif;
        $level_akses = htmlspecialchars(strip_tags($level_akses));
		$foto = htmlspecialchars(strip_tags($foto));

        // Bind parameters
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":nama_lengkap", $nama_lengkap);
        $stmt->bindParam(":user", $user);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":is_aktif", $is_aktif, PDO::PARAM_INT);
        $stmt->bindParam(":level_akses", $level_akses);
		$stmt->bindParam(":foto", $foto);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete Data (Menghapus Data)
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT); // Binding ke variabel integer
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function login($user, $pass){
		$query = "SELECT * FROM " . $this->table_name . " WHERE user = ?";
		$stmt = $this->conn->prepare($query);

        // Sanitize data
        $user = htmlspecialchars(strip_tags($user));
		$stmt->bindParam(1, $user);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if($row){
			if(password_verify($pass, $row['pass'])){
				return $row;
			}else{
				return false;
			}
		}else{
				return false;
			}
	}
    
  //Check apakah ada user yang sama
  public function checkIfUserExists($user){
		$query = "SELECT id FROM " . $this->table_name . " WHERE user = ?";
		$stmt = $this->conn->prepare($query);

        // Sanitize data
        $user = htmlspecialchars(strip_tags($user));
		$stmt->bindParam(1, $user);
		$stmt->execute();
		$num = $stmt->rowCount();
		if($num > 0){
			return true;
		}else{
			return false;
		}
  }

    //Check apakah ada email yang sama
  public function checkIfEmailExists($email){
		$query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
		$stmt = $this->conn->prepare($query);

        // Sanitize data
        $email = htmlspecialchars(strip_tags($email));
		$stmt->bindParam(1, $email);
		$stmt->execute();
		$num = $stmt->rowCount();
		if($num > 0){
			return true;
		}else{
			return false;
		}
  }

}
?>