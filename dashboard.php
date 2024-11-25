<?php  
session_start();

function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][$type][] = $message;
}

function displayFlashMessages() {
    if (!empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $type => $messages) {
            foreach ($messages as $message) {
                echo "<div class='flash-message {$type}'>{$message}</div>";
            }
        }
        unset($_SESSION['flash_messages']);
    }
}

// Check if user is authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location:3 loginy.php");
    exit();
}

// Database connection parameters
$host = "localhost";
$dbname = "REG1";
$user = "postgres";
$password = "1234";

try {
    // Database connection
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);



    // Handle profile photo upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_photo'])) {
        $uploadDir = "uploads/";
        $file = $_FILES['profile_photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
        if (!in_array($file['type'], $allowedTypes)) {
            setFlashMessage('error', "Invalid file type. Only JPEG, PNG, and GIF are allowed.");
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            setFlashMessage('error', "File size exceeds the 2MB limit.");
        } else {
            $fileName = uniqid() . "-" . basename($file['name']);
            $targetFilePath = $uploadDir . $fileName;
    
            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                $updatePhotoQuery = "UPDATE users SET profile_photo = :photo WHERE id = :user_id";
                $stmt = $pdo->prepare($updatePhotoQuery);
                $stmt->execute([':photo' => $fileName, ':user_id' => $_SESSION['user_id']]);
    
                $_SESSION['profile_photo'] = $fileName;
                setFlashMessage('success', "Profile photo updated successfully!");
            } else {
                setFlashMessage('error', "Failed to upload the file. Please try again.");
            }
        }
    }
    


    // Ensure every user has a default profile photo if none exists
    $defaultPhoto = "img/people.png";

    $userPhotoQuery = "SELECT COALESCE(profile_photo, :default_photo) AS profile_photo FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($userPhotoQuery);
    $stmt->execute([':user_id' => $_SESSION['user_id'], ':default_photo' => $defaultPhoto]);
    $user = $stmt->fetch();

    $_SESSION['profile_photo'] = $user['profile_photo'];

    // Fetch user details
    $stmt = $pdo->prepare("SELECT username, email,referral_code, status, created_at FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $userDetails = $stmt->fetch();

    if (!$userDetails) {
        die("User not found.");
    }


    // Fetch balances for the logged-in user
    $balanceQuery = "
        SELECT 
            COALESCE(SUM(expense), 0) AS total_expense, 
            COALESCE(SUM(profit), 0) AS total_profit, 
            COALESCE(SUM(balance), 0) AS account_balance, 
            COALESCE(SUM(reward_points), 0) AS reward_points 
        FROM balances 
        WHERE user_id = :user_id
    ";
    $stmt = $pdo->prepare($balanceQuery);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $balances = $stmt->fetch();

    $rewardQuery = "
    SELECT 
        COALESCE(SUM(reward_points), 0) AS reward_points
    FROM users 
        WHERE id = :user_id
    ";
    $stmt = $pdo->prepare($rewardQuery);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $rewards= $stmt->fetch();

    // Fetch user's referrals
    $referralQuery = "
        SELECT 
            username, 
            TO_CHAR(created_at, 'YYYY-MM-DD') AS registration_date, 
            status

        FROM users
        WHERE referred_by = :user_id
    ";
    $stmt = $pdo->prepare($referralQuery);
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $referrals = $stmt->fetchAll();

    // Handle password change
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'])) {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
    
        // Fetch the current password from the database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :user_id");
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
    
        if ($user && password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE id = :user_id");
                $stmt->execute([':new_password' => $newHashedPassword, ':user_id' => $_SESSION['user_id']]);
                setFlashMessage('success', "Password updated successfully!");
            } else {
                setFlashMessage('error', "New passwords do not match.");
            }
        } else {
            setFlashMessage('error', "Current password is incorrect.");
        }
    }
      // 1. Mark all unread messages as read when the user visits the messages page
    $markAsReadQuery = "
        UPDATE users_messages 
        SET is_read = TRUE 
        WHERE user_id = :user_id AND is_read = FALSE";
    
    $markAsReadStmt = $pdo->prepare($markAsReadQuery);
    $markAsReadStmt->execute([':user_id' => $_SESSION['user_id']]);

    // 2. Fetch all messages for the user, including their read/unread status
    $fetchMessagesQuery = "
        SELECT message, is_read, created_at 
        FROM users_messages 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC
    ";
    $fetchMessagesStmt = $pdo->prepare($fetchMessagesQuery);
    $fetchMessagesStmt->execute([':user_id' => $_SESSION['user_id']]);
    $messages = $fetchMessagesStmt->fetchAll();

    // 3. Count unread messages to display in the notification icon
    $countUnreadQuery = "
        SELECT COUNT(*) AS unread_count 
        FROM users_messages 
        WHERE user_id = :user_id AND is_read = True
    ";
    $countUnreadStmt = $pdo->prepare($countUnreadQuery);
    $countUnreadStmt->execute([':user_id' => $_SESSION['user_id']]);
    $unreadCount = $countUnreadStmt->fetchColumn();

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Dashboard</title>
</head>
<body>
    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <span class="text">PESAPUB</span>
        </a>
        <ul class="side-menu top">
		<li class="active">
			<a href="#" data-content="dashboard">
				<i class='bx bxs-dashboard'></i>
				<span class="text">Dashboard</span>
			</a>
		</li>
        <li>
            <a href="#" data-content="profile">
                <i class='bx bxs-user'></i>
                <span class="text">Profile</span>
            </a>
        </li>
		<li>
			<a href="#" data-content="Withdrawals">
				<i class='bx bxs-shopping-bag-alt'></i>
				<span class="text">Withdrawals</span>
			</a>
		</li>
		<li>
			<a href="#" data-content="REWARDS">
				<i class='bx bxs-doughnut-chart'></i>
				<span class="text">REWARDS</span>
			</a>
		</li>
        <li>
			<a href="#" data-content="WHEEL">
				<i class='bx bxs-doughnut-chart'></i>
				<span class="text">WHEEL</span>
			</a>
		</li>
		<li>
			<a href="#" data-content="messages">
				<i class='bx bxs-message-dots'></i>
				<span class="text">Messages</span>
			</a>
		</li>
		<li>
			<a href="#" data-content="team">
				<i class='bx bxs-group'></i>
				<span class="text">Refferals</span>
			</a>
		</li>
	</ul>	
		<ul class="side-menu">
			<li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'settings.html') ? 'active' : ''; ?>">
				<a href="settings.html">
					<i class='bx bxs-cog'></i>
					<span class="text">Settings</span>
				</a>
			</li>
		</ul>
	</section> 
    <!-- SIDEBAR -->

<!-- CONTENT -->
    <section id="content">
		<nav>

			<i class='bx bx-menu' ></i>
			<a href="#" class="nav-link">MENU</a>
			<form action="index.html">
				<div class="form-input">
					<input type="search" placeholder="Search...">
					<button type="submit" class="search-btn"><i class='bx bx-search' ></i></button>
				</div>
			</form>
			<input type="checkbox" id="switch-mode" hidden>
			<label for="switch-mode" class="switch-mode"></label>
			<a href="#" class="notification" data-content="messages">
                <i class='bx bxs-bell'></i>
                <span class="num"><?php echo $unreadCount; ?></span>
            </a>
            <a href="index.html" class="profile"> 
                        <?php
                        // Directory paths
                        $uploadsDir = "uploads/";
                        $defaultPhoto = "profile.png";

                        // Profile photo from session or fallback
                        $profilePhoto = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : $defaultPhoto;

                        // Check if the file exists
                        $profilePhotoPath = $uploadsDir . $profilePhoto;
                        if (!file_exists($profilePhotoPath)) {
                            $profilePhoto = $defaultPhoto;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($uploadsDir . $profilePhoto); ?>" alt="Profile Photo" class="rounded-photo">
            </a>
		</nav>
		<!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <?php displayFlashMessages(); ?>
            <section id="dashboard" class="content-section active">	
                <div class="head-title">    
                    <div class="left">
                    <h3>Hey there, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                        <h1>Dashboard</h1>
                        <ul class="breadcrumb">
                            <li>
                                <a href="#">Dashboard</a>
                            </li>
                            <li><i class='bx bx-chevron-right' ></i></li>
                            <li>
                                <a class="active" data-content="Withdrawals" href="#">BALANCE</a>
                            </li>
                        </ul>
                    </div>
                    <a href="#" class="btn-download">
                        <i class='bx bxs-cloud-download' ></i>
                        <span class="text" data-content="Withdrawals" >DEPOSIT</span>
                    </a>
                </div>
                <!-- Balance Cards -->
                <div> 
                <ul class="box-info">
                    <li>
                        <i class='bx bxs-calendar-check'></i>
                        <span class="text">
                            <h3>KSH. <?php echo number_format($balances['total_expense'], 2); ?></h3>
                            <p>EXPENSE</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-group'></i>
                        <span class="text">
                            <h3>KSH. <?php echo number_format($balances['total_profit'], 2); ?></h3>
                            <p>PROFIT</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">
                            <h3>KSH. <?php echo number_format($balances['account_balance'], 2); ?></h3>
                            <p>ACCOUNT BALANCE</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-dollar-circle'></i>
                        <span class="text">
                            <h3>KSH. <?php echo number_format($rewards['reward_points'],  2); ?></h3>
                            <p>REFFERAL EARNINGS</p>
                        </span>
                    </li>
                </ul>
                </div>
            </section>

            <!-- Referral Table -->
            <section id="team" class="content-section">
              <div class="head-title">
                <h1>Team</h1>
              </div>
              <div class="table-container">
                <table>
                    <thead>
                        <tr>

                            <th>Profile</th>
                            <th>Username</th>
                            <th>Date of Registration</th>
                            <th>Status</th>
                            <th>PROFITS FOR YOU</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($referrals)) : ?>
                        <?php foreach ($referrals as $referral) : ?>
                            <tr>
                                <td>
									<img src="uploads/profile.png">
								</td>
                                <td><?= htmlspecialchars($referral['username']); ?></td>
                                <td><?= htmlspecialchars($referral['registration_date']); ?></td>
                                <td><?= htmlspecialchars($referral['status']); ?></td>
                                <td>KSH.100</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else : ?>
                            <tr>
                                <td colspan="4">No referrals yet.</td>
                             </tr>
                            <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </section>
            <section id="Withdrawals" class="content-section"> 
            <div class="container">
                <h1>Deposits and Withdrawals</h1>

                    <!-- Deposit Section -->
                <div class="section">
                <h2>Deposit</h2>
                    <form id="deposit-form">
                        <label for="deposit-amount">Amount:</label>
                        <input type="number" id="deposit-amount" name="deposit-amount" placeholder="Enter amount" required>
                        <button type="submit" class="btn">Deposit</button>
                    </form>
                </div>

                    <!-- Withdrawal Section -->
                <div class="section">
                <h2>Withdrawal</h2>
                <form id="withdrawal-form">
                    <label for="withdrawal-amount">Amount:</label>
                    <input type="number" id="withdrawal-amount" name="withdrawal-amount" placeholder="Enter amount" required>

                    <label for="provider">Select Provider:</label>
                    <select id="provider" name="provider" required>
                    <option value="" disabled selected>Select your provider</option>
                    <option value="airtel">Airtel</option>
                    <option value="safaricom">Safaricom</option>
                    </select>

                    <button type="submit" class="btn">Withdraw</button>
                </form>
                </div>
            </div>
			</section>
            <section id="WHEEL" class="content-section" >
            <div class="wheel-container">
              <div class="wheel" id="wheel">
                <!-- Prize labels -->
                <div class="prize" style="--rotation: 0deg;">ksh.100</div>
                <div class="prize" style="--rotation: 45deg;">10</div>
                <div class="prize" style="--rotation: 90deg;">5</div>
                <div class="prize" style="--rotation: 135deg;">0</div>
                <div class="prize" style="--rotation: 180deg;">5</div>
                <div class="prize" style="--rotation: 225deg;">0</div>
                <div class="prize" style="--rotation: 270deg;">110</div>
                <div class="prize" style="--rotation: 315deg;">0</div>
                <div class="center-circle">
                    <button id="spinButton">Spin</button>
                </div>
              </div>
                <div class="pointer"></div>
            </div>
               <p id="result" class="result">Click "Spin" to try your luck!</p>

            </section>
		
			<!-- REWARDS Content -->
            
            <section id="REWARDS" class="content-section"> 
                <div>
                    <ul class="box1-info">
                        <li>
                            <i class='bx bxs-dollar-circle'></i>
                            <span class="text">
                                <h3>WELCOME BONUS</h3>
                                <p>Thank you for joining us! You have been awarded a bonus of KSH.100</p>
                                <form id="claim-bonus-form">
                                    <div class="btn">
                                        <button type="submit" class="btn btn-primary">Claim Bonus!</button>
                                    </div>
                                </form>
                                <div id="bonus-message"></div>
                            </span>
                        </li>
                    </ul>
                </div>
            </section>


            

		
			<!-- Messages Content -->
            <section id="messages" class="content-section">
                <h1>Messages</h1>
                <ul class="messages-list">
                    <?php if (!empty($messages)) : ?>
                        <?php foreach ($messages as $message) : ?>
                            <li class="message <?= $message['is_read'] ? 'read' : 'unread' ?>">
                                <p><?= htmlspecialchars($message['message']) ?></p>
                                <span class="timestamp"><?= htmlspecialchars(date('F j, Y, g:i a', strtotime($message['created_at']))) ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <li>No messages to display.</li>
                    <?php endif; ?>
                </ul>
            </section>

		
			<!-- Team Content -->
			<section id="team" class="content-section">
				<h1>Team</h1>
				<p>Meet your team members here.</p>
			</section>
            <section id="profile" class="content-section">
                <div class="profile-header">
                    <h1>Your Profile</h1>
                </div>
                <div class="profile-details">
                    <?php if (isset($_SESSION['success'])): ?>
                        <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
                    <?php endif; ?>

                    <!-- Profile Picture -->
                    <div class="profile-photo">
                        <?php
                        // Directory paths
                        $uploadsDir = "uploads/";
                        $defaultPhoto = "profile.png";

                        // Profile photo from session or fallback
                        $profilePhoto = !empty($_SESSION['profile_photo']) ? $_SESSION['profile_photo'] : $defaultPhoto;

                        // Check if the file exists
                        $profilePhotoPath = $uploadsDir . $profilePhoto;
                        if (!file_exists($profilePhotoPath)) {
                            $profilePhoto = $defaultPhoto;
                        }
                        ?>
                        <img src="<?php echo htmlspecialchars($uploadsDir . $profilePhoto); ?>" alt="Profile Photo" class="rounded-photo">
                    </div>


                    <!-- Upload Form -->
                    <form action="" method="POST" enctype="multipart/form-data">
                        <label for="profilePhoto">Change Profile Photo:</label>
                        <input type="file" name="profile_photo" id="profilePhoto" accept="image/*" required>
                        <button type="submit">Upload</button>
                    </form>

                    <!-- User Details -->
                    <p><strong>Your Referral Code:</strong> <?php echo htmlspecialchars($userDetails['referral_code']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>Account created at:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($userDetails['created_at']))); ?></p>
                    <p><strong>Account status:</strong> <?php echo htmlspecialchars($userDetails['status']); ?></p>
                </div>
                
                <div class="change-password">
                    <h2>Change Password</h2>
                    <?php if (isset($_SESSION['password_error'])): ?>
                        <p style="color: red;"><?php echo $_SESSION['password_error']; unset($_SESSION['password_error']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['password_success'])): ?>
                        <p style="color: green;"><?php echo $_SESSION['password_success']; unset($_SESSION['password_success']); ?></p>
                    <?php endif; ?>
                    <form action="dashboard.php" method="POST">
                        <label for="current_password">Current Password:</label>
                        <input type="password" name="current_password" id="current_password" required>

                        <label for="new_password">New Password:</label>
                        <input type="password" name="new_password" id="new_password" required>

                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>

                        <button type="submit">Change Password</button>
                    </form>
                    <button type="submit">LOGOUT</button>
                </div>

            </section>


        	
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script1.js"></script>
</body>
</html>
