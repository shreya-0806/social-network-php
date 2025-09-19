<?php
require_once __DIR__ . '/classes/User.php';
$user = new User();

if (isset($_POST['signup'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $profile_pic = 'uploads/default.png';
    if (!empty($_FILES['profile_pic']['name'])) {
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $err = "Invalid profile image type";
        } elseif ($_FILES['profile_pic']['size'] > 5*1024*1024) {
            $err = "Profile image too large (max 5MB)";
        } else {
            $filename = time() . '_' . preg_replace("/[^A-Za-z0-9\-_\.]/", '', basename($_FILES['profile_pic']['name']));
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
                $profile_pic = 'uploads/' . $filename;
            } else {
                $err = "Upload failed";
            }
        }
    }

    if (empty($err)) {
        if ($user->signup($_POST['name'], $_POST['email'], $_POST['password'], $_POST['age'], $profile_pic)) {
            header("Location: index.php");
            exit;
        } else {
            $err = "Signup failed (email may already exist)";
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Sign Up</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <div class="card" style="max-width:560px;margin:auto;">
    <h2>Create Account</h2>
    <?php if(!empty($err)) echo "<p class='alert alert-error'>$err</p>"; ?>
    <form method="POST" enctype="multipart/form-data" class="form">
      <input class="input" type="text" name="name" placeholder="Full Name" required>
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <input class="input" type="number" name="age" placeholder="Age" required>
      <input class="input" type="file" name="profile_pic" accept="image/*">
      <button class="btn btn-primary" type="submit" name="signup">Sign Up</button>
    </form>
    <p class="small">Already have an account? <a href="index.php">Login</a></p>
  </div>
</div>
</body>
</html>
