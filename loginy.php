<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" type="text/css" href="new.css">
</head>
<body>
  <div class="wrapper">
    <div class="form-wrapper sign-in">
      <form action="login.php" method="POST">
        <h2>Login</h2>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo "<p style='color: white;'>{$_SESSION['error']}</p>";
            unset($_SESSION['error']); 
        }
        ?>
        <div class="input-group">
          <input type="text" name="username" required>
          <label for="username">Username</label>
        </div>

        <div class="input-group">
          <input type="password" name="password" required >
          <label for="password">Password</label>
        </div>

        <div class="remember">
          <label><input type="checkbox" name="remember"> Remember me</label>
        </div>

        <button type="submit">Login</button>

        <div class="signUp-link">
          <p>Don't have an account? <a href="register.php" class="signUpBtn-link">Sign Up</a></p>
        </div>
      </form>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
