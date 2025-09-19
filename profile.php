<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }

require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';

$postObj = new Post();
$userObj = new User();

// Refresh session user with latest DB values
$_SESSION['user'] = $userObj->getById((int)$_SESSION['user']['id']);
$me = $_SESSION['user'];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Profile</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container layout">
  <!-- Left: profile card -->
  <aside class="sidebar">
    <div class="card profile-card">
      <div class="profile-avatar avatar-edit">
        <img src="<?= htmlspecialchars($me['profile_pic']); ?>" alt="Profile">
        <button id="edit-profile-btn" title="Edit profile" class="edit-icon">✎</button>
      </div>
      <h3 class="profile-name"><?= htmlspecialchars($me['full_name']); ?></h3>
      <p class="profile-meta">Email: <?= htmlspecialchars($me['email']); ?></p>
      <p class="profile-meta">Age: <span id="profile-age"><?= htmlspecialchars($me['age']); ?></span></p>
      <a href="logout.php" class="btn btn-secondary">Logout</a>
    </div>
  </aside>

  <!-- Main -->
  <main>
    <!-- Profile edit (hidden by default) -->
    <div id="edit-profile-card" class="card hidden">
      <h3>Edit Profile</h3>
      <form id="edit-profile-form" enctype="multipart/form-data" class="form">
        <label>Name</label>
        <input class="input" type="text" name="name" value="<?= htmlspecialchars($me['full_name']); ?>" required>
        <label>Age</label>
        <input class="input" type="number" name="age" value="<?= htmlspecialchars($me['age']); ?>" required>
        <label>Change profile picture</label>
        <input class="input" type="file" name="profile_pic" accept="image/*">
        <div class="row">
          <button class="btn btn-primary" type="submit">Save</button>
          <button id="cancel-edit" type="button" class="btn btn-ghost">Cancel</button>
        </div>
      </form>
    </div>

    <!-- New Post (AJAX) -->
    <div class="card new-post">
      <form id="new-post-form" enctype="multipart/form-data" class="form">
        <textarea class="input" name="description" placeholder="Write something..." required></textarea>
        <input class="input" type="file" name="image" accept="image/*">
        <button class="btn btn-primary" type="submit">Post</button>
      </form>
      <div id="post-message" class="small text-muted"></div>
    </div>

    <!-- Posts list (only this user's posts) -->
    <div id="posts" class="posts">
      <?php
      $posts = $postObj->getPostsByUser($me['id']);
      while ($row = $posts->fetch_assoc()) {
          $imgHtml = $row['image'] ? "<img class='post-image' src='{$row['image']}'>" : "";
          echo "<div class='card post' data-id='{$row['id']}'>";
          echo "<div class='meta'><div class='name'>" . htmlspecialchars($me['full_name']) . "</div>";
          echo "<div class='time timestamp'>" . htmlspecialchars($row['created_at']) . "</div></div>";
          echo "<div class='body'>";
          echo "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";
          echo $imgHtml;
          echo "<div class='controls'>";
          echo "<button class='control-btn like' data-id='{$row['id']}'>👍 Like <span class='count'>{$row['likes']}</span></button>";
          echo "<button class='control-btn dislike' data-id='{$row['id']}'>👎 Dislike <span class='count'>{$row['dislikes']}</span></button>";
          echo "<button class='control-btn delete-post' data-id='{$row['id']}'>🗑️ Delete</button>";
          echo "</div></div></div>";
      }
      ?>
    </div>
  </main>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/script.js"></script>
</body>
</html>
