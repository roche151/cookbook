# Cookbook Application - Local Development Setup

This guide helps you set up a local development environment for the Cookbook application.

## Prerequisites

- **PHP 8.2+** with common extensions
- **Composer** (PHP package manager)
- **Node.js 18+** and npm
- **Git**
- **SQLite** (included with PHP) or **MySQL/PostgreSQL**
- **A code editor** (VS Code, PHPStorm, etc.)

## 1. Clone the Repository

```bash
git clone <repository-url> cookbook
cd cookbook
```

## 2. Install Dependencies

### PHP Dependencies

```bash
composer install
```

### Node Dependencies

```bash
npm install
```

## 3. Configure Environment

### Copy Environment File

```bash
cp .env.example .env
```

### Generate Application Key

```bash
php artisan key:generate
```

This creates a random encryption key for your application.

## 4. Database Setup

### Using SQLite (Default - Easiest for Local Development)

SQLite is already configured by default. No additional setup needed - the database file will be created at `database/database.sqlite`.

### Using MySQL

1. Create a database:
```sql
CREATE DATABASE cookbook_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Update `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cookbook_dev
DB_USERNAME=root
DB_PASSWORD=
```

### Using PostgreSQL

1. Create a database:
```bash
createdb cookbook_dev
```

2. Update `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cookbook_dev
DB_USERNAME=postgres
DB_PASSWORD=
```

## 5. Run Migrations & Seeding

```bash
# Run all migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

This creates sample recipes, tags, and a test user account:
- **Email:** `test@example.com`
- **Password:** `password`

## 6. Build Frontend Assets

```bash
# Development build (with watching for changes)
npm run dev

# Or production build (one-time)
npm run build
```

The `npm run dev` command will watch for file changes and automatically rebuild.

## 7. Start Development Server

Open a new terminal and run:

```bash
php artisan serve
```

This starts a development server at `http://localhost:8000`

## 8. Access the Application

- **URL:** http://localhost:8000
- **Email:** test@example.com
- **Password:** password

## Development Commands

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test class
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run with coverage report
php artisan test --coverage
```

### Database Commands

```bash
# Rollback last migration
php artisan migrate:rollback

# Reset entire database
php artisan migrate:reset

# Refresh database (rollback + migrate + seed)
php artisan migrate:refresh --seed

# Run specific migration
php artisan migrate --path=database/migrations/2025_11_24_000003_create_recipes_table.php
```

### Tinker (Interactive Shell)

```bash
php artisan tinker

# Inside tinker:
>>> $user = User::first();
>>> $user->recipes()->count();
>>> Recipe::where('title', 'like', '%Pizza%')->first();
```

### Queue Commands (if using background jobs)

```bash
# Process queued jobs (locally)
php artisan queue:work

# In separate terminal for async processing
php artisan queue:listen
```

### Cache Commands

```bash
# Clear all caches
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Cache routes (don't do this in development!)
php artisan route:cache
```

## Project Structure

```
cookbook/
├── app/                      # Application code
│   ├── Http/
│   │   ├── Controllers/     # Route controllers
│   │   └── Requests/        # Form request validation
│   ├── Models/              # Eloquent models (Recipe, User, Tag, etc.)
│   ├── Services/            # Business logic layer
│   └── View/
│       └── Components/      # Reusable view components
├── database/
│   ├── migrations/          # Database migrations
│   ├── seeders/             # Database seeders
│   └── factories/           # Model factories for testing
├── resources/
│   ├── views/               # Blade templates (.blade.php)
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript files
├── routes/
│   ├── web.php              # Web routes
│   └── auth.php             # Auth routes
├── storage/                 # Logs, cache, file uploads
├── tests/
│   ├── Feature/             # Feature tests
│   └── Unit/                # Unit tests
└── public/                  # Publicly accessible files
    ├── index.php            # Application entry point
    └── build/               # Built assets (CSS, JS)
```

## Common Development Tasks

### Creating a New Model

```bash
php artisan make:model ModelName -m  # With migration
php artisan make:model ModelName -mfs  # With migration, factory, seeder
```

### Creating a New Controller

```bash
php artisan make:controller ControllerName
php artisan make:controller ControllerName --resource  # With resource methods
```

### Creating a New Migration

```bash
php artisan make:migration migration_description --create=table_name
```

### Creating a New Middleware

```bash
php artisan make:middleware MiddlewareName
```

## Debugging

### Using Laravel Debugbar

The application includes Laravel Debugbar for development. It appears as a bar at the bottom of web pages and shows:
- Execution time and memory usage
- Database queries
- Route information
- Request/response details

### Using var_dump()

```php
// In controller or model
dd($variable);  // Dump and die
dump($variable);  // Dump without dying
```

### Checking Logs

```bash
# Real-time log viewing
tail -f storage/logs/laravel.log
```

## Useful Aliases

Add to `.bash_profile`, `.bashrc`, or `.zshrc`:

```bash
alias artisan='php artisan'
alias test='php artisan test'
alias serve='php artisan serve'
alias tinker='php artisan tinker'
alias migrate='php artisan migrate'
alias fresh='php artisan migrate:fresh --seed'
```

## Database Design

### Core Entities

- **Users:** Application users who create and manage recipes
- **Recipes:** Cooking recipes with title, description, time, difficulty
- **Tags:** Categories for organizing recipes (Italian, Dessert, etc.)
- **Directions:** Step-by-step cooking instructions
- **Ingredients:** Recipe components with quantities
- **Recipe Ratings:** User ratings and reviews of recipes
- **Shopping Lists:** User's personal shopping lists
- **Shopping List Items:** Individual items in shopping lists

### Key Relationships

- User has many Recipes
- Recipe belongs to User
- Recipe has many Tags (many-to-many)
- Recipe has many Directions
- Recipe has many Ingredients
- User has many Ratings
- User has many Shopping Lists

## Performance Tips

1. **Use Lazy Loading** when needed:
```php
$recipes = Recipe::with(['tags', 'directions', 'ingredients'])->get();
```

2. **Cache frequently accessed data:**
```php
$tags = Cache::remember('all_tags', 3600, function() {
    return Tag::all();
});
```

3. **Use pagination:**
```php
$recipes = Recipe::paginate(15);
```

4. **Profile slow queries** using Laravel Debugbar

## IDE Setup

### VS Code Extensions Recommended

- PHP Intelephense
- Laravel Artisan
- Laravel Blade Snippets
- Prettier - Code formatter
- ESLint

### PHPStorm Configuration

- Enable Laravel framework support
- Set up run configurations for artisan commands
- Configure debugger with Xdebug

## Troubleshooting

### "Class not found" errors

Clear autoloader cache:
```bash
composer dump-autoload
```

### Port 8000 already in use

Use different port:
```bash
php artisan serve --port=8001
```

### Database connection error

Verify `.env` database settings match your local setup

### NPM build fails

```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install
npm run build
```

### Permission denied on storage/logs

```bash
chmod -R 775 storage bootstrap/cache
```

## Need Help?

- Check [Laravel Documentation](https://laravel.com/docs)
- Look at existing tests in `tests/` folder
- Review Artisan command reference: `php artisan list`
- Check application logs: `storage/logs/laravel.log`

Happy coding!
