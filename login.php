<?php
session_start();
require_once "db_connect.php";

$username = $password = "";
$username_err = $password_err = $login_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $login_status = "Invalid request";
    } else {
        // Validate username
        if (empty(trim($_POST["username"]))) {
            $username_err = "Please enter username.";
        } else {
            $username = trim($_POST["username"]);
        }

        // Validate password
        if (empty(trim($_POST["password"]))) {
            $password_err = "Please enter your password.";
        } else {
            $password = trim($_POST["password"]);
        }

        // If no errors, try to login
        if (empty($username_err) && empty($password_err)) {
            $sql = "SELECT id, username, password FROM users WHERE username = ?";
            
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $param_username);
                $param_username = $username;
                
                if ($stmt->execute()) {
                    $stmt->store_result();
                    
                    if ($stmt->num_rows == 1) {
                        $stmt->bind_result($id, $username, $hashed_password);
                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                // Regenerate session ID to prevent fixation
                                session_regenerate_id(true);
                                
                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;
                                
                                header("location: manage_database.php");
                                exit;
                            } else {
                                // Generic error message (security best practice)
                                $login_status = "Invalid username or password";
                            }
                        }
                    } else {
                        // Generic error message (security best practice)
                        $login_status = "Invalid username or password";
                    }
                } else {
                    $login_status = "Oops! Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="auth-style.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>

            <?php if (!empty($login_status)) : ?>
                <div class="auth-status error">
                    <?php echo $login_status; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="<?php echo (!empty($username_err)) ? 'invalid' : ''; ?>" 
                        value="<?php echo htmlspecialchars($username); ?>"
                        autocomplete="username"
                        required
                    >
                    <?php if (!empty($username_err)) : ?>
                        <span class="error-message"><?php echo $username_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="<?php echo (!empty($password_err)) ? 'invalid' : ''; ?>"
                        autocomplete="current-password"
                        required
                    >
                    <?php if (!empty($password_err)) : ?>
                        <span class="error-message"><?php echo $password_err; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="text-link">Forgot password?</a>
                </div>

                <button type="submit" class="auth-button">Sign In</button>

                <div class="auth-footer">
                    Don't have an account? <a href="register.php" class="text-link">Sign up</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>