<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account</title>
  <link href="style.css" rel="stylesheet">
</head>
<body>
  <main class="register-container">
    <header class="register-header">
      <h1 class="register-title">Create Account</h1>
    </header>

    <?php if (!empty($register_status)): ?>
      <div class="status-message status-success">
        <?php echo $register_status; ?>
      </div>
    <?php endif; ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username"
          class="form-input <?php echo (!empty($username_err)) ? 'input-error' : ''; ?>" 
          value="<?php echo htmlspecialchars($username); ?>"
          autocomplete="username"
        >
        <?php if (!empty($username_err)): ?>
          <span class="error-message"><?php echo $username_err; ?></span>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password"
          class="form-input <?php echo (!empty($password_err)) ? 'input-error' : ''; ?>"
          autocomplete="new-password"
        >
        <?php if (!empty($password_err)): ?>
          <span class="error-message"><?php echo $password_err; ?></span>
        <?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary">Register</button>

      <div class="form-footer">
        <span>Already have an account?</span>
        <a href="login.php" class="btn-link">Sign in</a>
      </div>
    </form>
  </main>
</body>
</html>