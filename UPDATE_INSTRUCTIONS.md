# How to Push Updates to Your Users

.\build_update.ps1
.\build_update.bat

This guide explains how the In-App Update System works and how you (the developer) can release new versions to your users.

## 1. Prerequisites (Server Setup)

You need a publicly accessible URL (e.g., a GitHub repository, an S3 bucket, or a simple web server) to host two things:

1.  **`update.json`**: A small file telling the app what the latest version is.
2.  **`update_vX.X.X.zip`**: The actual update files.

## 2. The `update.json` File

You will host a JSON file that looks like this:

```json
{
    "version": "1.1.0",
    "release_date": "2026-02-01",
    "download_url": "https://your-server.com/updates/update_v1.1.0.zip",
    "changelog": "<ul><li>Added new report type</li><li>Fixed login bug</li></ul>",
    "min_php_version": "8.2"
}

{
  "version": "1.3.0",
  "release_date": "2026-02-01",
  "download_url": "https://updates.utilixpro.com/update_v1.3.0.zip",
  "changelog": "<ul><li>Added new report type</li><li>Fixed login bug</li></ul>",
  "min_php_version": "8.2"
}
```

## 3. Creating an Update Package (The Zip File)

When you have made changes to your code and want to release them:

1.  **Increment Version**: Update `APP_VERSION` in your local `.env` and `config/app.php` (if applicable) to the new number (e.g., `1.1.0`).
2.  **Package Files**:
    - **Run the Build Script**: Double-click `build_update.bat` or run `.\build_update.bat` in your terminal.
    - This script automatically:
        - Builds frontend assets (`npm run build`).
        - Optimizes PHP dependencies (`composer install --no-dev`).
        - Creates a ready-to-upload zip file (e.g., `update_package_2026-02-18_19-43.zip`) excluding `node_modules` and `storage`.
        - Restores your dev environment.
3.  **Upload**: Upload this **generated zip file** to your server as `update_v1.1.0.zip` (rename it to match your version).

## 4. Releasing the Update

1.  Update the `update.json` file on your server with the new `version`, `download_url`, and `changelog`.
2.  That's it!

## 5. What Happens on the User's Side?

1.  The user goes to **System > System Updates**.
2.  The system calls your `update.json` URL.
3.  It compares the user's current version (from their `.env`) with the `version` in the JSON.
4.  If a new version is available, they see the "Update Now" button.
5.  When clicked:
    - System downloads the zip.
    - Puts site in Maintenance Mode.
    - Backs up the database.
    - Extracts files (overwriting old code).
    - Runs `php artisan migrate` (to update database structure).
    - Clears cache.
    - Turns off Maintenance Mode.

## 6. Configuration

We need to set the **Update URL** in your application code so it knows where to look.

I will now implement the code to handle steps 5 and 6.
