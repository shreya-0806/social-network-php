<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';

$postObj = new Post();
$userObj = new User();

if (!isset($_SESSION['user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}
$uid = (int)$_SESSION['user']['id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

/* ---------- LIKE ---------- */
if ($action === 'like' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($postObj->likePost($id)) {
        $row = $postObj->getPostById($id);
        echo json_encode(['status' => 'ok', 'likes' => (int)$row['likes']]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

/* ---------- DISLIKE ---------- */
if ($action === 'dislike' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($postObj->dislikePost($id)) {
        $row = $postObj->getPostById($id);
        echo json_encode(['status' => 'ok', 'dislikes' => (int)$row['dislikes']]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

/* ---------- ADD POST (AJAX) ---------- */
if ($action === 'add_post') {
    $description = trim($_POST['description'] ?? '');

    // uploads
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
            exit;
        }
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'File too large (max 5MB)']);
            exit;
        }
        $filename = time() . '_' . preg_replace("/[^A-Za-z0-9\-_\.]/", '', basename($_FILES['image']['name']));
        $target = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $imagePath = 'uploads/' . $filename;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
            exit;
        }
    }

    $ok = $postObj->addPost($uid, $description, $imagePath);
    if ($ok) {
        // return the newly created post (fetch last inserted id)
        $newId = $postObj->db->insert_id;
        $newPost = $postObj->getPostById($newId);
        // attach user fields
        $user = $userObj->getById($uid);
        $newPost['full_name'] = $user['full_name'];
        $newPost['profile_pic'] = $user['profile_pic'];
        echo json_encode(['status' => 'ok', 'post' => $newPost]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

/* ---------- DELETE POST ---------- */
if ($action === 'delete_post' && !empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    if ($postObj->deletePost($id, $uid)) {
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

/* ---------- UPDATE PROFILE ---------- */
if ($action === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $age = (int)($_POST['age'] ?? 0);

    // uploads (profile pic)
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $profilePicPath = null;
    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid profile image type']);
            exit;
        }
        if ($_FILES['profile_pic']['size'] > 5 * 1024 * 1024) {
            echo json_encode(['status' => 'error', 'message' => 'Profile file too large (max 5MB)']);
            exit;
        }
        $filename = time() . '_' . preg_replace("/[^A-Za-z0-9\-_\.]/", '', basename($_FILES['profile_pic']['name']));
        $target = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $profilePicPath = 'uploads/' . $filename;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Profile upload failed']);
            exit;
        }
    }

    $ok = $userObj->updateProfile($uid, $name, $age, $profilePicPath);
    if ($ok) {
        $updated = $userObj->getById($uid);
        // update session user info
        $_SESSION['user'] = $updated;
        echo json_encode(['status' => 'ok', 'user' => $updated]);
    } else {
        echo json_encode(['status' => 'error']);
    }
    exit;
}

/* default */
echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
exit;
?>
