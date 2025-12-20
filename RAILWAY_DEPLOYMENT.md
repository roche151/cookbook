# Deploy Cookbook to Railway.app

Railway is the easiest way to deploy your Laravel app. This guide will get you live in ~10 minutes.

## Prerequisites

- GitHub account (✅ You have this - repo at https://github.com/roche151/cookbook)
- Railway account (free to create)
- Credit card (for paid tier after free credits)

## 1. Sign Up to Railway

1. Go to https://railway.app
2. Click "Start Free" 
3. Sign up with GitHub (authorize Railway to access your repos)
4. Complete setup

## 2. Create a New Project

1. In Railway dashboard, click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your `roche151/cookbook` repository
4. Click "Deploy Now"

Railway will automatically detect it's a Laravel app and start the deployment.

## 3. Add a Database

Your app needs PostgreSQL. Railway includes it.

1. In your Railway project, click "Add Service" (+ button)
2. Select "PostgreSQL"
3. Railway creates it automatically and connects it to your app

## 4. Configure Environment Variables

Railway needs your `.env` settings to run the app.

1. Go to your deployed service (the Laravel app)
2. Click "Variables" tab
3. Add these variables:

```
APP_KEY=base64:YOUR_KEY_HERE
APP_NAME=Cookbook
APP_ENV=production
APP_DEBUG=false
APP_URL=${{ RAILWAY_PUBLIC_DOMAIN }}
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database
LOG_LEVEL=error
```

**For APP_KEY:** Get your key from local `.env` file, or generate:
```bash
php artisan key:generate
# Copy the output value (without "base64:" prefix, Railway adds it)
```

### Database Variables (Railway auto-fills these, but verify)

These should be auto-populated from the PostgreSQL service:
```
DB_CONNECTION=pgsql
DB_HOST=${{ POSTGRES_HOST }}
DB_PORT=${{ POSTGRES_PORT }}
DB_DATABASE=${{ POSTGRES_DB }}
DB_USERNAME=${{ POSTGRES_USER }}
DB_PASSWORD=${{ POSTGRES_PASSWORD }}
# Optional: if Railway provides DATABASE_URL, set one of these for convenience
# DATABASE_URL=postgres://USER:PASSWORD@HOST:PORT/DBNAME
# DB_URL=${{ DATABASE_URL }}
```

**Optional - Email (skip for now, can add later):**
```
MAIL_MAILER=log
MAIL_FROM_ADDRESS=noreply@cookbook.test
MAIL_FROM_NAME=Cookbook
```

## 5. Deploy

1. Click the deployment status button
2. Wait for "Build" to complete (2-3 minutes)
3. Wait for "Deploy" to complete (1-2 minutes)

You'll see:
- 🔨 Building...
- ✅ Build successful
- 🚀 Deploying...
- ✅ Deployment successful

## 6. Run Migrations & Seed Data

Your app is deployed but the database is empty. You need to run migrations.

### Option A: Use Railway Deploy/Start Commands (Recommended)

Add helper scripts and wire them in Railway so migrations run automatically and services start reliably.

1) Scripts (already added in repo under `railway/`):
   - `railway/init-app.sh` — runs migrations and caches config/routes/views
   - `railway/run-app.sh` — starts the Laravel web app
   - `railway/run-worker.sh` — starts the queue worker
   - `railway/run-cron.sh` — runs the Laravel scheduler

2) In Railway UI:
   - Web (app) service → Settings → Commands:
     - Deploy Command: `bash railway/init-app.sh`
     - Start Command: `bash railway/run-app.sh`
   - Add two more services if desired:
     - Worker service → Start Command: `bash railway/run-worker.sh`
     - Cron service → Start Command: `bash railway/run-cron.sh`

This approach avoids manual terminals and ensures migrations run on each deploy.

### Option B: SSH into Railway

1. In your Railway project, go to the Laravel service
2. Click "Terminal" tab
3. Run:
```bash
php artisan migrate --force
php artisan db:seed --force
```

This creates all tables and seeds 12 sample recipes + test user.

### Option C: Add a One-Time Deploy Job

If Option A doesn't work:

1. Add a file `Procfile` to your repo root:
```
web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force && php artisan db:seed --force
```

2. Commit and push:
```bash
git add Procfile
git commit -m "Add Procfile for Railway migrations"
git push origin main
```

3. Railway will auto-redeploy and run migrations on first deploy

## 7. Access Your App

1. In Railway dashboard, find your service domain (looks like `cookbook-production.up.railway.app`)
2. Click the link or visit it in browser
3. Login with:
   - **Email:** test@example.com
   - **Password:** password

## 8. Custom Domain (Optional)

Want your friends to access it via a custom domain like `cookbook.yoursite.com`?

1. In Railway service settings, go to "Networking"
2. Click "Generate Domain"
3. Or connect your own domain using Railway's CNAME instructions

## Troubleshooting

### App won't deploy / Build fails

Check the build logs:
1. Click your service
2. Go to "Deployments" tab
3. Click the failed deployment
4. View logs to see the error

**Common issues:**
- **PHP version mismatch:** Railway uses PHP 8.1 by default. Update if needed in `railway.json`
- **Missing dependencies:** Ensure `composer.lock` is in repo (it should be)

### Database connection error

1. Verify PostgreSQL service is added
2. Check Variables tab has all `DB_*` variables
3. Re-run migrations via terminal:
```bash
php artisan migrate --force
```

#### "connection to server at 127.0.0.1:5432 failed" 

This means your app is still using the default host.

- Ensure these variables exist on the app service and have values from the Postgres service (not literals):
  - `DB_CONNECTION=pgsql`
  - `DB_HOST` (should be a non-127.0.0.1 host)
  - `DB_PORT=5432`
  - `DB_DATABASE`
  - `DB_USERNAME`
  - `DB_PASSWORD`
- Optionally also add one of:
  - `DATABASE_URL` (full Postgres URL supplied by Railway)
  - `DB_URL` referencing `DATABASE_URL`
- After updating variables, trigger a redeploy (click "Redeploy latest") and then run:
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Can't login / tables missing

Run migrations and seeding:
```bash
php artisan migrate --force
php artisan db:seed --force
```

### App is slow / times out

- Railway free tier is limited. Upgrade to paid plan ($5/month) for consistent performance
- Check logs for slow queries

## Monitoring & Logs

View app logs in Railway:
1. Click your service
2. Go to "Logs" tab
3. See real-time application logs

## Next Steps

### Share with Friends & Family

Send them the Railway domain link:
```
https://cookbook-production.up.railway.app
```

Login credentials:
- Email: `test@example.com`
- Password: `password`

### Add More Test Users (Optional)

Via SSH terminal:
```bash
php artisan tinker
User::create(['name' => 'Friend Name', 'email' => 'friend@example.com', 'password' => bcrypt('password')])
```

### Set Up Real Email (Optional)

Currently using `log` mailer (emails go to logs, not actually sent).

To send real emails, set up SendGrid or Mailgun:

1. Create free SendGrid account (sendgrid.com)
2. Get API key
3. Add to Railway Variables:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=sg_YOUR_API_KEY_HERE
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Cookbook
```

## Cost Breakdown

- **First month:** Free ($5 Railway credits)
- **After that:** ~$5-7/month for:
  - PHP app (shared instance)
  - PostgreSQL database (5GB free tier)
  - No additional fees for bandwidth/traffic

## Questions?

- Railway docs: https://docs.railway.app/
- Laravel on Railway: https://docs.railway.app/guides/laravel
- Check Railway logs for error details

**You're live!** 🚀 Share the link with friends and enjoy!
