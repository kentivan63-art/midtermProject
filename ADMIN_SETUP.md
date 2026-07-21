# Admin Setup Guide

## How to Set Up Admin Users

### Option 1: Simple Admin Setup (Current Implementation)

The current system automatically makes the following users admin:
- First registered user (userID = 1)
- User with email "admin@groovify.com"

### Option 2: Database Column Setup (Recommended for Production)

Add an `isAdmin` column to your users table:

```sql
ALTER TABLE users ADD COLUMN isAdmin TINYINT(1) DEFAULT 0;
```

Then update specific users to be admins:

```sql
-- Make a specific user admin
UPDATE users SET isAdmin = 1 WHERE email = 'your-email@example.com';

-- Or make the first user admin
UPDATE users SET isAdmin = 1 WHERE userID = 1;
```

### Option 3: Manual Admin Assignment

You can create a simple PHP script to assign admin rights:

```php
<?php
require_once("../config/db.php");

$email = "user@example.com"; // Change this to the user's email
$stmt = $conn->prepare("UPDATE users SET isAdmin = 1 WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

echo "User updated to admin. Check if successful: " . ($stmt->affected_rows > 0 ? "Yes" : "No");
?>
```

## Current Admin Detection Logic

In `login_process.php`, the system checks:
1. If the database has an `isAdmin` column and it's set to 1
2. If the email is "admin@groovify.com" 
3. If the userID is 1 (first registered user)

## Security Recommendations

1. **Add isAdmin column to database** for proper admin management
2. **Use strong admin passwords** 
3. **Limit admin access** to only necessary personnel
4. **Consider 2FA** for admin accounts
5. **Log admin actions** for security auditing

## Testing Admin Access

1. Log in with an admin account (using one of the methods above)
2. The "Admin" tab should appear in the sidebar
3. Access the admin panel at `admin_updates.php`
4. Try to access admin panel with non-admin account (should be denied)

## Making Changes to Admin Detection

To change how admins are detected, edit `frontEnd/login_process.php` around line 24-31:

```php
// Current logic
if ($email === "admin@groovify.com" || $user["userID"] === 1) {
    $isAdmin = true;
}

// Change to your preferred logic
// For example, only use database column:
$isAdmin = isset($user["isAdmin"]) && $user["isAdmin"] == 1;
```