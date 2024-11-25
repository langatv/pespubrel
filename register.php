<?php
// Database connection parameters
$host = "localhost";
$dbname = "REG1";
$user = "postgres";
$password = "1234";

try {
    // Create a new PDO instance
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Start session to store error messages
    session_start();

    // Check if the form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve form inputs
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $terms = isset($_POST['terms']) ? 1 : 0;
        $referral_code = $_POST['referral_code'] ?? null; // Optional referral code

        // Input validation
        if (empty($username) || empty($phone) || empty($email) || empty($password)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: register.php");
            exit();
        }

        if (!$terms) {
            $_SESSION['error'] = "You must agree to the terms and conditions.";
            header("Location: register.php");
            exit();
        }

        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        // Check if the referral code exists in the database
        
        $referred_by = null;
        if (!empty($referral_code)) {
          $referralQuery = "SELECT id FROM users WHERE referral_code = :referral_code";
          $stmt = $pdo->prepare($referralQuery);
          $stmt->execute([':referral_code' => $referral_code]);
          $referrer = $stmt->fetch();
         

            
            if ($referrer) { 
              $referred_by = $referrer['id'];
                // Award points to the referrer
                $rewardPoint = 100; // Example: 10 points per referral
                $rewardQuery = "UPDATE users SET reward_points = reward_points + :reward_point WHERE id = :referrer_id";
                $rewardStmt = $pdo->prepare($rewardQuery);
                $rewardStmt->execute([
                    ':reward_point' => $rewardPoint,
                    ':referrer_id' =>$referred_by,
                ]);
              
            } else {
                $_SESSION['error'] = "Invalid referral code.";
                header("Location: register.php");
                exit();
            }
        }

        // Generate a unique referral code for the new user
        function generateReferralCode($length = 8) {
            return strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length));
        }
        $newReferralCode = generateReferralCode();

        // Insert data into the users table
        $query = "INSERT INTO users (username, phone, email, password, referral_code, referred_by) 
        VALUES (:username, :phone, :email, :password, :referral_code, :referred_by) 
        RETURNING id";
        $stmt = $pdo->prepare($query);

        try {
            $stmt->execute([
                ':username' => $username,
                ':phone' => $phone,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':referral_code' => $newReferralCode,
                ':referred_by' => $referred_by,
            ]);

            // Fetch the new user ID
            $newUserId = $stmt->fetchColumn();
              
            // Create a welcome message
            $message = "Welcome, $username! Your referral code is $newReferralCode please head to the  to the Rewards section to check for rewards!";

            // Insert the message into the users_messages table
            $messageQuery = "INSERT INTO users_messages (user_id, message) VALUES (:user_id, :message)";
            $messageStmt = $pdo->prepare($messageQuery);
            $messageStmt->execute([
                ':user_id' => $newUserId,
                ':message' => $message,
            ]);

            // Redirect to login page on success
            header("Location: loginy.php");
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') { // Unique constraint violation (e.g., duplicate email)
                $_SESSION['error'] = "This email is already registered.";
            } else {
                $_SESSION['error'] = "An error occurred during registration.";
            }
            header("Location: register.php");
            exit();
        }
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Form</title>
  <link rel="stylesheet" type="text/css" href="new.css">
</head>
<body>
  <div class="wrapper">
    <div class="form-wrapper sign-in">
      <form action="register.php" method="POST">
        <h2>Sign Up</h2>
        
        <!-- Username Input -->
        <div class="input-group">
          <input type="text" name="username" required>
          <label for="">Username</label>
        </div>
        
        <!-- Phone Number Input -->
        <div class="input-group">
          <input type="text" name="phone" required>
          <label for="">Phone number</label>
        </div>
        
        <!-- Email Input -->
        <div class="input-group">
          <input type="email" name="email" required>
          <label for="">Email</label>
        </div>
        
        <!-- Password Input -->
        <div class="input-group">
          <input type="password" name="password" required>
          <label for="">Password</label>
        </div>
        <div class="input-group">
          <input type="text" name="referral_code" placeholder="Optional">
          <label for="">Referral Code (Optional)</label>
        </div>
        <!-- Terms Checkbox -->
        <div class="remember">
          <label><input type="checkbox" name="terms" required> I agree to the terms & conditions</label>
        </div>
        
        <!-- Submit Button -->
        <button type="submit">Sign Up</button>
        
        <div class="signUp-link">
          <p>Already have an account? <a href="loginy.php" class="signInBtn-link">Sign In</a></p>
        </div>
      </form>
    </div>
  </div>

  <script src="script1.js"></script>
</body>
</html>
