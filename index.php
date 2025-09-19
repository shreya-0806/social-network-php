<?php
session_start();
require_once __DIR__ . '/classes/User.php';
$user = new User();

if (isset($_POST['login'])) {
    $res = $user->login($_POST['email'], $_POST['password']);
    if ($res) {
        $_SESSION['user'] = $res;
        header("Location: profile.php");
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
  <div class="card" style="max-width:480px;margin:auto;">
    <h2>Login</h2>
    <?php if(!empty($error)) echo "<p class='alert alert-error'>$error</p>"; ?>
    <form method="POST" class="form">
      <input class="input" type="email" name="email" placeholder="Email" required>
      <input class="input" type="password" name="password" placeholder="Password" required>
      <button class="btn btn-primary" type="submit" name="login">Login</button>
    </form>
    <p class="small">Don’t have an account? <a href="signup.php">Sign Up</a></p>
  </div>
</div>
</body>
</html>
