<?php
require_once("../config/session.php");
require_once("../config/db.php");

// Set headers for webhook
header('Content-Type: application/json');

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Security: Verify GitHub webhook signature if configured
$secret = getenv('GITHUB_WEBHOOK_SECRET') ?: 'your-webhook-secret-here';
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';

if ($signature) {
    $hash = 'sha1=' . hash_hmac('sha1', $json, $secret);
    if (!hash_equals($hash, $signature)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

// Log webhook received
error_log("GitHub webhook received: " . print_r($data, true));

// Check if this is a push event
if (isset($data['ref']) && strpos($data['ref'], 'refs/heads/') === 0) {
    $branch = str_replace('refs/heads/', '', $data['ref']);
    $commits = $data['commits'] ?? [];
    
    error_log("Push to branch: " . $branch);
    error_log("Number of commits: " . count($commits));
    
    // Only process pushes to main/master branch
    if (in_array($branch, ['main', 'master'])) {
        $updateInfo = processCommits($commits, $data);
        
        if ($updateInfo) {
            // Store update in database
            storeUpdateInDatabase($updateInfo, $conn);
            
            echo json_encode([
                'success' => true,
                'message' => 'Update processed successfully',
                'update' => $updateInfo
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No version changes detected'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Ignoring push to branch: ' . $branch
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Not a push event'
    ]);
}

/**
 * Process commits to extract version information
 */
function processCommits($commits, $pushData) {
    if (empty($commits)) {
        return null;
    }
    
    $version = null;
    $updateType = 'improvement';
    $descriptions = [];
    
    // Process each commit
    foreach ($commits as $commit) {
        $message = $commit['message'] ?? '';
        $descriptions[] = $message;
        
        // Check for version patterns in commit messages
        if (preg_match('/version\s*[:=]\s*(\d+\.\d+\.\d+)/i', $message, $matches)) {
            $version = $matches[1];
        }
        
        // Determine update type based on commit message
        if (preg_match('/^(feat|feature|new)/i', $message)) {
            $updateType = 'feature';
        } elseif (preg_match('/^(fix|bugfix|bug)/i', $message)) {
            $updateType = 'bugfix';
        } elseif (preg_match('/^(release|major|breaking)/i', $message)) {
            $updateType = 'release';
        }
    }
    
    // If no version found, generate one based on previous version
    if (!$version) {
        $version = generateNewVersion($updateType);
    }
    
    // Create description from commit messages
    $description = createUpdateDescription($descriptions, $pushData);
    
    return [
        'version' => $version,
        'description' => $description,
        'release_date' => date('Y-m-d'),
        'update_type' => $updateType,
        'commit_count' => count($commits),
        'pusher' => $pushData['pusher']['name'] ?? 'Unknown'
    ];
}

/**
 * Generate a new version number based on update type
 */
function generateNewVersion($updateType) {
    // This would typically fetch the latest version from database
    // For now, return a placeholder
    return '1.0.' . (date('j') + 1); // Simple incremental version
}

/**
 * Create a human-readable update description
 */
function createUpdateDescription($commitMessages, $pushData) {
    $pusher = $pushData['pusher']['name'] ?? 'Unknown';
    $count = count($commitMessages);
    
    if ($count === 1) {
        $description = $commitMessages[0];
    } else {
        $description = "Multiple updates: " . implode(', ', array_slice($commitMessages, 0, 3));
        if ($count > 3) {
            $description .= " and " . ($count - 3) . " more commits";
        }
    }
    
    return $description;
}

/**
 * Store update information in database
 */
function storeUpdateInDatabase($updateInfo, $conn) {
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
    $checkStmt->bind_param("s", $updateInfo['version']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        // Insert new update
        $stmt = $conn->prepare("
            INSERT INTO app_updates (version, description, release_date, update_type, commit_count, pusher)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssis",
            $updateInfo['version'],
            $updateInfo['description'],
            $updateInfo['release_date'],
            $updateInfo['update_type'],
            $updateInfo['commit_count'],
            $updateInfo['pusher']
        );
        
        if ($stmt->execute()) {
            error_log("Successfully stored update: " . $updateInfo['version']);
            return true;
        } else {
            error_log("Failed to store update: " . $stmt->error);
            return false;
        }
    } else {
        error_log("Version " . $updateInfo['version'] . " already exists, skipping");
        return false;
    }
}
?>