<?php
require_once __DIR__ . '/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    public function isEmailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return ($res && $res->num_rows > 0);
    }

    public function signup($name, $email, $password, $age, $profile_pic) {
        if ($this->isEmailExists($email)) return false;
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users(full_name, email, password, age, profile_pic) VALUES(?,?,?,?,?)");
        $stmt->bind_param("sssis", $name, $email, $hashed, $age, $profile_pic);
        return $stmt->execute();
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && password_verify($password, $result['password'])) {
            unset($result['password']);
            return $result;
        }
        return false;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, full_name, email, age, profile_pic, created_at FROM users WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateProfile($id, $name, $age, $profile_pic = null) {
        if ($profile_pic) {
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, age = ?, profile_pic = ? WHERE id = ?");
            $stmt->bind_param("sisi", $name, $age, $profile_pic, $id);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET full_name = ?, age = ? WHERE id = ?");
            $stmt->bind_param("sii", $name, $age, $id);
        }
        return $stmt->execute();
    }
}
?>
