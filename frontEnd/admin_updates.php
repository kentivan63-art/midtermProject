<?php
require_once("../config/session.php");
requireLogin();
requireAdmin(); // Only allow admin access

require_once("../config/db.php");

$userID = getCurrentUserID();

$message = "";
$messageType = "";

// Handle manual update creation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_update"])) {
    $version = trim($_POST["version"]);
    $description = trim($_POST["description"]);
    $updateType = $_POST["update_type"] ?? "improvement";
    $releaseDate = $_POST["release_date"] ?? date('Y-m-d');
    
    if (!empty($version) && !empty($description)) {
        // Create table if it doesn't exist
        $createTable = "
            CREATE TABLE IF NOT EXISTS app_updates (
                update_id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(20) NOT NULL,
                description TEXT NOT NULL,
                release_date DATE NOT NULL,
                update_type ENUM('release', 'feature', 'bugfix', 'improvement') DEFAULT 'improvement',
                commit_count INT DEFAULT 0,
                pusher VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_release_date (release_date),
                INDEX idx_version (version)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        $conn->query($createTable);
        
        // Check if version already exists
        $checkStmt = $conn->prepare("SELECT update_id FROM app_updates WHERE version = ?");
        $checkStmt->bind_param("s", $version);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt = $conn->prepare("
                INSERT INTO app_updates (version, description, release_date, update_type, pusher)
                VALUES (?, ?, ?, ?, 'Manual Entry')
            ");
            $stmt->bind_param("ssss", $version, $description, $releaseDate, $updateType);
            
            if ($stmt->execute()) {
                $message = "Update added successfully!";
                $messageType = "success";
            } else {
                $message = "Error adding update: " . $stmt->error;
                $messageType = "error";
            }
        } else {
            $message = "Version $version already exists!";
            $messageType = "error";
        }
    } else {
        $message = "Please fill in all required fields!";
        $messageType = "error";
    }
}

// Handle update deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_update"])) {
    $updateID = (int)$_POST["update_id"];
    
    $stmt = $conn->prepare("DELETE FROM app_updates WHERE update_id = ?");
    $stmt->bind_param("i", $updateID);
    
    if ($stmt->execute()) {
        $message = "Update deleted successfully!";
        $messageType = "success";
    } else {
        $message = "Error deleting update: " . $stmt->error;
        $messageType = "error";
    }
}

// Fetch all updates
$updates = [];
try {
    $createTable = "
        CREATE TABLE IF NOT EXISTS app_updates (
            update_id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(20) NOT NULL,
            description TEXT NOT NULL,
            release_date DATE NOT NULL,
            update_type ENUM('release', 'feature', 'bugfix', 'improvement') DEFAULT 'improvement',
            commit_count INT DEFAULT 0,
            pusher VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_release_date (release_date),
            INDEX idx_version (version)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    $conn->query($createTable);
    
    $stmt = $conn->prepare("SELECT * FROM app_updates ORDER BY release_date DESC, update_id DESC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row;
    }
} catch (Exception $e) {
    $updates = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Updates - Groovify</title>
    <link rel="stylesheet" href="../assets/dashboard.css?v=3">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
    <style>
        .admin-container {
            padding: 20px;
        }
        
        .admin-form {
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.05);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: 13px;
            color: var(--muted);
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px 12px;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255,255,255,.1);
            background: rgba(255,255,255,.05);
            color: white;
            font-size: 14px;
            outline: none;
            transition: border-color var(--ease);
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: rgba(29,185,84,.5);
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .btn-submit {
            background: var(--green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            font-weight: 700;
            cursor: pointer;
            transition: background var(--ease);
        }
        
        .btn-submit:hover {
            background: #1ed760;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            margin-bottom: 16px;
            font-weight: 600;
        }
        
        .message.success {
            background: rgba(29,185,84,.1);
            color: var(--green);
            border: 1px solid rgba(29,185,84,.3);
        }
        
        .message.error {
            background: rgba(239,68,68,.1);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,.3);
        }
        
        .webhook-info {
            background: rgba(59,130,246,.1);
            border: 1px solid rgba(59,130,246,.3);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 24px;
        }
        
        .webhook-info h3 {
            color: #3b82f6;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .webhook-info p {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.5;
        }
        
        .webhook-url {
            background: rgba(0,0,0,.3);
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            font-family: monospace;
            font-size: 12px;
            color: var(--green);
            word-break: break-all;
        }
        
        .update-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.05);
            margin-bottom: 12px;
            align-items: flex-start;
        }
        
        .update-item:hover {
            background: rgba(255,255,255,.05);
            border-color: rgba(255,255,255,.1);
        }
        
        .update-version {
            flex-shrink: 0;
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            background: rgba(29,185,84,.15);
            color: var(--green);
            font-weight: 700;
            font-size: 14px;
            border: 1px solid rgba(29,185,84,.25);
        }
        
        .update-details {
            flex: 1;
        }
        
        .update-description {
            font-size: 15px;
            color: var(--text);
            margin-bottom: 6px;
            line-height: 1.4;
        }
        
        .update-meta {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 8px;
        }
        
        .delete-btn {
            background: rgba(239,68,68,.1);
            color: #ef4444;
            border: 1px solid rgba(239,68,68,.3);
            padding: 6px 12px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: background var(--ease);
        }
        
        .delete-btn:hover {
            background: rgba(239,68,68,.2);
        }
    </style>
</head>
<body>

<div class="app">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="brand">
            <a href="dashboard.php">
                <img src="../logotransparent.png" class="brand-logo" alt="Groovify">
            </a>
            <div class="brand-name">Groovify</div>
        </div>

        <nav class="nav">
            <a class="nav-item" href="dashboard.php">Home</a>
            <a class="nav-item" href="library.php">Library</a>
            <a class="nav-item" href="updates.php">Update Version</a>
            <a class="nav-item active" href="admin_updates.php">Admin Updates</a>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>

        <div class="spacer"></div>
        <a class="logout" href="logout.php">Log out</a>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size: 22px; font-weight: 800;">Admin Updates Management</h2>
        </header>

        <section class="panel">
            <div class="admin-container">
                <!-- Webhook Information -->
                <div class="webhook-info">
                    <h3>🔗 GitHub Webhook Setup</h3>
                    <p>To enable automatic updates from GitHub pushes, set up a webhook in your GitHub repository:</p>
                    <p style="margin-top: 8px;"><strong>Webhook URL:</strong></p>
                    <div class="webhook-url"><?php echo "https://" . $_SERVER['HTTP_HOST'] . "/midtermProject/frontEnd/webhook_handler.php"; ?></div>
                    <p style="margin-top: 8px;"><strong>Content type:</strong> application/json</p>
                    <p><strong>Events:</strong> Push events</p>
                    <p style="margin-top: 8px;"><strong>Commit message format:</strong> Use keywords like "feat:", "fix:", "release:" to auto-detect update types.</p>
                </div>

                <!-- Message Display -->
                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <!-- Add Update Form -->
                <div class="admin-form">
                    <h3 style="margin-bottom: 16px; font-size: 18px;">Add Manual Update</h3>
                    <form method="POST" action="admin_updates.php">
                        <div class="form-group">
                            <label for="version">Version Number *</label>
                            <input type="text" id="version" name="version" placeholder="e.g., 1.2.0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="update_type">Update Type</label>
                            <select id="update_type" name="update_type">
                                <option value="release">Release</option>
                                <option value="feature">Feature</option>
                                <option value="bugfix">Bugfix</option>
                                <option value="improvement" selected>Improvement</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="release_date">Release Date</label>
                            <input type="date" id="release_date" name="release_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" placeholder="Describe the changes in this update..." required></textarea>
                        </div>
                        
                        <button type="submit" name="add_update" class="btn-submit">Add Update</button>
                    </form>
                </div>

                <!-- Existing Updates -->
                <div class="panel-title" style="margin-bottom: 16px;">Existing Updates</div>
                
                <?php if (!empty($updates)): ?>
                    <?php foreach ($updates as $update): ?>
                        <div class="update-item">
                            <div class="update-version">
                                <?php echo htmlspecialchars($update['version']); ?>
                            </div>
                            <div class="update-details">
                                <div class="update-description">
                                    <?php echo htmlspecialchars($update['description']); ?>
                                </div>
                                <div class="update-meta">
                                    Type: <?php echo htmlspecialchars($update['update_type']); ?> | 
                                    Date: <?php echo htmlspecialchars(date('F j, Y', strtotime($update['release_date']))); ?>
                                    <?php if (!empty($update['pusher'])): ?>
                                        | Pusher: <?php echo htmlspecialchars($update['pusher']); ?>
                                    <?php endif; ?>
                                    <?php if (!empty($update['commit_count'])): ?>
                                        | Commits: <?php echo htmlspecialchars($update['commit_count']); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST" action="admin_updates.php" style="display:inline;">
                                <input type="hidden" name="update_id" value="<?php echo $update['update_id']; ?>">
                                <button type="submit" name="delete_update" class="delete-btn">Delete</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--muted);">
                        No updates yet. Add your first update above or set up the GitHub webhook for automatic updates.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

</div>

</body>
</html>