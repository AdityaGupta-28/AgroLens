# Deploying AgroLens for Free

This guide provides a step-by-step roadmap to deploy the AgroLens Agricultural Intelligence platform **completely for free** using modern cloud hosting platforms: **Fly.io** (recommmended for SQLite applications) or **Render**.

---

## Technical Architecture for Free Hosting

Free cloud database add-ons (like hosted MySQL or PostgreSQL) often come with extremely tight storage limits or expire after 30 days. To avoid these limitations and keep hosting **100% free forever**, we utilize **SQLite** as our production database.
- **Fly.io** supports persistent block-storage volumes. This allows your SQLite database file (`database.sqlite`) to persist safely across server restarts and new code deployments.
- **Render** offers free Web Services and a simple way to configure environment variables.

---

## Option A: Deploying on Fly.io (Recommended)

Fly.io provides a generous free tier that includes up to **3 shared-cpu-1x VMs**, **3 GB of persistent volume storage**, and **160 GB of outbound bandwidth** per month.

### Step 1: Install Flyctl
Open your terminal and install the Fly.io command line tool:
- **Windows (PowerShell)**:
  ```powershell
  iwr https://fly.io/install.ps1 -useb | iex
  ```
- **macOS / Linux**:
  ```bash
  curl -L https://fly.io/install.sh | sh
  ```

### Step 2: Sign Up and Log In
```bash
fly auth signup
fly auth login
```

### Step 3: Initialize the App
Run the fly launch command in the root directory:
```bash
fly launch
```
- Fly will detect that this is a Laravel application and ask to set up a Dockerfile, standard database, etc.
- **CRITICAL**: When asked if you want to provision a MySQL or Postgres database, select **NO**! We will use SQLite on a persistent volume.

### Step 4: Configure Persistent SQLite Storage
To ensure SQLite data isn't wiped when your app restarts, create a **1 GB persistent volume** (which fits fully inside the Fly.io free tier):
```bash
fly volumes create agrolens_data --size 1
```

Now, open the generated `fly.toml` file and mount the volume by adding the following section:
```toml
[mounts]
  source = "agrolens_data"
  destination = "/var/www/html/database/storage"
```

### Step 5: Update Env and Start script
Modify your environment variables using Fly secrets to bind the database path to the mounted volume:
```bash
fly secrets set DB_CONNECTION=sqlite DB_DATABASE=/var/www/html/database/storage/database.sqlite APP_ENV=production APP_DEBUG=false
```

Deploy the application:
```bash
fly deploy
```

On deployment, Fly.io will run composer optimizations, build your CSS/JS assets, run database migrations, and boot your server. Your app will be live at `https://[your-app-name].fly.dev`!

---

## Option B: Deploying on Render (Free Web Service)

Render offers a fast and free Web Service tier. Because Render's free tier has an ephemeral disk (files are lost on restart), use this option if you want to deploy quickly for a demo, or couple it with a free PostgreSQL database from providers like Neon.tech or Supabase.

### Step 1: Create a Render Account
Sign up for free at [Render](https://render.com).

### Step 2: Create a New Web Service
- Connect your GitHub/GitLab repository.
- Select the repository for **AgroLens**.
- Choose the **PHP** runtime.

### Step 3: Configure Build and Start Commands
- **Build Command**:
  ```bash
  composer install --no-interaction --optimize-autoloader && npm install && npm run build
  ```
- **Start Command**:
  ```bash
  php artisan migrate --force && php artisan db:seed --class=AgroLensPlatformSeeder --force && php artisan serve --host 0.0.0.0 --port $PORT
  ```

### Step 4: Add Environment Variables
Inside the Render dashboard under **Environment**, add the following key-value pairs:
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- `APP_KEY`: `base64:...` (Generate one locally using `php artisan key:generate`)
- `DB_CONNECTION`: `sqlite`
- `DB_DATABASE`: `database/database.sqlite` (If using a simple SQLite demo)

Render will spin up your container and deploy the app at `https://agrolens.onrender.com`.

---

## Running Seeders and Creating Default Users

Once deployed, your database will automatically contain the default seed records. You can log in using:
1. **Super Admin**: `superadmin@agrolens.gov.in` (password: `password`) — *Full access to User Management, stats, and settings.*
2. **Government Officer**: `officer@agrolens.gov.in` (password: `password`) — *Access to surveys, dashboards, and GIS maps.*
3. **Public Viewer**: `viewer@agrolens.gov.in` (password: `password`) — *Read-only access to GIS maps and regional analytics.*
