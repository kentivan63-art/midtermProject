# GitHub Webhook Setup Guide

This guide will help you set up automatic update generation when you push changes to GitHub.

## How It Works

When you push changes to your GitHub repository, a webhook will automatically:
1. Receive the push event from GitHub
2. Parse the commit messages for version information
3. Detect the update type (feature, bugfix, release, improvement)
4. Automatically create an entry in your app_updates table
5. Display the update on the "Update Version" page

## Setup Instructions

### 1. Configure GitHub Webhook

1. Go to your GitHub repository
2. Click on **Settings** → **Webhooks**
3. Click **Add webhook**
4. Configure the webhook:
   - **Payload URL**: `https://your-domain.com/midtermProject/frontEnd/webhook_handler.php`
   - **Content type**: `application/json`
   - **Secret**: (Optional) Set a secret key for security
   - **Events**: Select "Push" events only
5. Click **Add webhook**

### 2. Set Webhook Secret (Optional but Recommended)

For security, set a webhook secret:

1. In your webhook settings, set a secret (e.g., `your-secret-key-here`)
2. Update the webhook_handler.php file:
   ```php
   $secret = 'your-secret-key-here'; // Change this to match your webhook secret
   ```

### 3. Commit Message Format

Use these patterns in your commit messages to help the system detect update types:

- **Features**: Start with `feat:` or `feature:`
  - Example: `feat: added new playlist sharing feature`
  
- **Bugfixes**: Start with `fix:` or `bugfix:`
  - Example: `fix: resolved playlist adding issue`
  
- **Releases**: Start with `release:` or `major:`
  - Example: `release: version 2.0.0 with major changes`
  
- **Improvements**: Any other commit will be classified as improvement
  - Example: `improved search performance`

### 4. Version Specification

You can specify version numbers in your commit messages:

- Include `version: X.Y.Z` in your commit message
- Example: `feat: new playlist feature version: 1.2.0`

If no version is specified, the system will automatically generate one.

## Manual Updates

You can also manually add updates through the Admin interface:

1. Go to **Admin** in the sidebar
2. Fill in the version, type, and description
3. Click **Add Update**

## Database Table

The system automatically creates the `app_updates` table if it doesn't exist:

```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Testing the Webhook

1. Make a test commit with the proper format
2. Push to your GitHub repository
3. Check the webhook delivery in GitHub (Settings → Webhooks → Recent Deliveries)
4. View the "Update Version" page to see if the update appeared

## Troubleshooting

### Webhook not triggering updates:
- Check that the webhook URL is accessible from the internet
- Verify the webhook is receiving push events in GitHub
- Check PHP error logs for webhook_handler.php

### Updates not appearing:
- Verify the database table was created
- Check for duplicate version numbers
- Review PHP error logs

### Security concerns:
- Always use a webhook secret
- Implement proper admin authentication for admin_updates.php
- Consider IP whitelisting for webhook requests

## Example Workflow

1. You make changes to your code
2. Commit with: `feat: added dark mode support version: 1.3.0`
3. Push to GitHub
4. Webhook automatically creates update entry
5. Users see the new update on the "Update Version" page

## Files Created

- `webhook_handler.php` - Receives and processes GitHub webhooks
- `admin_updates.php` - Admin interface for manual update management
- `updates.php` - Public-facing updates page (enhanced)

The system is now ready to automatically track your GitHub pushes and generate update summaries!