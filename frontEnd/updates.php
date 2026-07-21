<?php
require_once("../config/session.php");
requireLogin();

require_once("../config/db.php");

// Get current version from database or config
$currentVersion = "1.0.0"; // Default version
$versionDate = date("F j, Y");

// Fetch update history from database if it exists
$updates = [];

try {
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
    
    // Get the latest version for current version display
    $latestStmt = $conn->prepare("SELECT version, release_date FROM app_updates ORDER BY release_date DESC, update_id DESC LIMIT 1");
    $latestStmt->execute();
    $latestResult = $latestStmt->get_result();
    
    if ($latestResult->num_rows > 0) {
        $latest = $latestResult->fetch_assoc();
        $currentVersion = $latest['version'];
        $versionDate = date("F j, Y", strtotime($latest['release_date']));
    }
    $latestStmt->close();
    
    // Fetch all updates for history
    $stmt = $conn->prepare("SELECT version, description, release_date, update_type FROM app_updates ORDER BY release_date DESC, update_id DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $updates[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // If table doesn't exist or error occurs, use default data
    $updates = [];
}

// If no updates in database, use sample data
if (empty($updates)) {
    $updates = [
        [
            'version' => '1.0.0',
            'description' => 'Initial release with core music streaming features',
            'release_date' => '2025-07-21',
            'update_type' => 'release'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Version - Groovify</title>
    <link rel="stylesheet" href="../assets/dashboard.css?v=3">
    <link rel="icon" type="image/x-icon" href="../groovifylogo.ico">
    <style>
        .updates-container {
            padding: 20px;
        }
        
        .current-version {
            background: linear-gradient(135deg, rgba(29,185,84,.1), rgba(29,185,84,.05));
            border: 1px solid rgba(29,185,84,.3);
            border-radius: var(--radius-lg);
            padding: 20px;
            margin-bottom: 24px;
            text-align: center;
        }
        
        .current-version-label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .current-version-number {
            font-size: 32px;
            font-weight: 800;
            color: var(--green);
            margin-bottom: 8px;
        }
        
        .current-version-date {
            font-size: 14px;
            color: var(--muted);
        }
        
        .update-history {
            margin-top: 24px;
        }
        
        .update-item {
            display: flex;
            gap: 16px;
            padding: 16px;
            border-radius: var(--radius-md);
            background: rgba(255,255,255,.02);
            border: 1px solid rgba(255,255,255,.05);
            margin-bottom: 12px;
            transition: background var(--ease), border-color var(--ease);
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
        
        .update-date {
            font-size: 12px;
            color: var(--muted);
        }
        
        .update-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            margin-left: 8px;
        }
        
        .update-type.release {
            background: rgba(29,185,84,.2);
            color: var(--green);
        }
        
        .update-type.feature {
            background: rgba(168,85,247,.2);
            color: #a855f7;
        }
        
        .update-type.bugfix {
            background: rgba(59,130,246,.2);
            color: #3b82f6;
        }
        
        .update-type.improvement {
            background: rgba(249,115,22,.2);
            color: #f97316;
        }
        
        .no-updates {
            text-align: center;
            padding: 40px;
            color: var(--muted);
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
            <a class="nav-item active" href="updates.php">Update Version</a>
            <?php if (isAdmin()): ?>
            <a class="nav-item" href="admin_updates.php">Admin</a>
            <?php endif; ?>
            <a class="nav-item" href="logout_process.php">Log out</a>
        </nav>

        <div class="spacer"></div>
        <a class="logout" href="logout.php">Log out</a>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <header class="topbar">
            <h2 style="font-size: 22px; font-weight: 800;">Update Version</h2>
        </header>

        <section class="panel">
            <div class="updates-container">
                <!-- Current Version -->
                <div class="current-version">
                    <div class="current-version-label">Current Version</div>
                    <div class="current-version-number"><?php echo htmlspecialchars($currentVersion); ?></div>
                    <div class="current-version-date">Released on <?php echo htmlspecialchars($versionDate); ?></div>
                </div>

                <!-- Update History -->
                <div class="update-history">
                    <div class="panel-title" style="margin-bottom: 16px;">Update History</div>
                    
                    <?php if (!empty($updates)): ?>
                        <?php foreach ($updates as $update): ?>
                            <div class="update-item">
                                <div class="update-version">
                                    <?php echo htmlspecialchars($update['version']); ?>
                                </div>
                                <div class="update-details">
                                    <div class="update-description">
                                        <?php echo htmlspecialchars($update['description']); ?>
                                        <?php if (!empty($update['update_type'])): ?>
                                            <span class="update-type <?php echo htmlspecialchars($update['update_type']); ?>">
                                                <?php echo htmlspecialchars($update['update_type']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="update-date">
                                        Released: <?php echo htmlspecialchars(date('F j, Y', strtotime($update['release_date']))); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-updates">
                            No update history available.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

</div>

</body>
</html>