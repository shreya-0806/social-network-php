<?php
require_once __DIR__ . '/Database.php';

class Post {
    private $db;

    public function __construct() {
        $this->db = (new Database())->conn;
    }

    // Returns inserted post ID (int) on success, or false on failure
    public function addPost($user_id, $desc, $image) {
        $stmt = $this->db->prepare("INSERT INTO posts(user_id, description, image) VALUES(?,?,?)");
        $stmt->bind_param("iss", $user_id, $desc, $image);
        if ($stmt->execute()) {
            return (int)$this->db->insert_id;
        }
        return false;
    }

    public function getPostsByUser($user_id) {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE user_id=? ORDER BY created_at DESC");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllPostsWithUser() {
        $sql = "SELECT p.*, u.full_name, u.profile_pic, u.email FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC";
        return $this->db->query($sql);
    }

    public function getPostById($id) {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function deletePost($id, $user_id) {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        return $stmt->execute();
    }

    public function likePost($id) {
        $stmt = $this->db->prepare("UPDATE posts SET likes = likes + 1 WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function dislikePost($id) {
        $stmt = $this->db->prepare("UPDATE posts SET dislikes = dislikes + 1 WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
