# How to Run This Laravel Invoice System

## Quick Start Guide (Using Laragon)

Since you're using **Laragon**, the setup is straightforward. Laragon automatically handles PHP, MySQL, and Apache/Nginx.

## Step-by-Step Setup

### 1. **Navigate to Project Directory**
```bash
cd E:\laragon\www\acc
```

### 2. **Install PHP Dependencies (Composer)**
```bash
composer install
```
This installs all PHP packages required by Laravel.

### 3. **Install Node.js Dependencies**
```bash
npm install
```
This installs frontend dependencies (Vite, Tailwind CSS, etc.).

### 4. **Configure Environment File**
The `.env` file should already exist. If not, copy from example:
```bash
copy .env.example .env
```

**Edit `.env` file** and configure your database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=acc
DB_USERNAME=root
DB_PASSWORD=
```

**Note:** Laragon's default MySQL credentials are usually:
- Username: `root`
- Password: (empty/blank)

### 5. **Generate Application Key** (if not already done)
```bash
php artisan key:generate
```

### 6. **Create Database**
- Open Laragon
- Click "Database" button or open HeidiSQL
- Create a new database named `acc` (or use your preferred name, update `.env` accordingly)

### 7. **Run Database Migrations and Seeders**
```bash
php artisan migrate:fresh --seed
```
This will:
- Create all database tables
- Seed with sample data including a default user

### 8. **Build Frontend Assets**
```bash
npm run build
```
This compiles CSS and JavaScript files for production.

**For Development (with hot reload):**
```bash
npm run dev
```
Keep this running in a separate terminal while developing.

### 9. **Start Laragon Services**
- Start Laragon
- Ensure MySQL and Apache/Nginx are running (green indicators)

### 10. **Access the Application**

**Option A: Using Laragon's Auto Virtual Host**
Since your project is in `www/acc`, Laragon should automatically create:
- URL: `http://acc.test` or `http://acc.local`

**Option B: Using Laravel Development Server**
```bash
php artisan serve
```
Then access: `http://localhost:8000`

### 11. **Login Credentials**
Default login credentials (from seeder):
- **Email:** `admin@invoice.com`
- **Password:** `password`

## Development Workflow

### For Development (Recommended)
1. **Terminal 1 - Start Laravel Server:**
   ```bash
   php artisan serve
   ```

2. **Terminal 2 - Start Vite Dev Server (for hot reload):**
   ```bash
   npm run dev
   ```

3. Access: `http://localhost:8000`

### For Production Build
```bash
npm run build
php artisan serve
```

## Useful Commands

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Run Migrations Only (without seeding)
```bash
php artisan migrate
```

### Create New Migration
```bash
php artisan make:migration create_table_name
```

### Run Tests
```bash
php artisan test
```

## Troubleshooting

### Issue: "Class not found" or Autoload errors
```bash
composer dump-autoload
```

### Issue: Database connection error
- Check MySQL is running in Laragon
- Verify database credentials in `.env`
- Make sure database exists

### Issue: 500 Error or "APP_KEY not set"
```bash
php artisan key:generate
php artisan config:clear
```

### Issue: CSS/JS not loading
- Make sure to run `npm run build` (production) or `npm run dev` (development)
- Clear cache: `php artisan view:clear`

### Issue: Permission errors (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

## Project Structure

- `app/` - Application code (Controllers, Models, etc.)
- `resources/views/` - Blade templates
- `routes/web.php` - Web routes
- `database/migrations/` - Database migrations
- `database/seeders/` - Database seeders
- `public/` - Public assets (accessed via web server)
- `.env` - Environment configuration (NOT committed to git)

## Next Steps After Setup

1. ✅ Login with default credentials
2. ✅ Create/manage parties
3. ✅ Create delivery challans
4. ✅ Generate invoices from challans
5. ✅ Download/print invoice PDFs

---

**Need Help?** Check the main `README.md` for more details about features and API endpoints.
