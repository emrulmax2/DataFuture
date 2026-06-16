# DataFutureD

A Laravel 10 web application with a Vue 3 + Vite + Tailwind CSS frontend. It includes
authentication via Laravel Passport, social login (Socialite / Microsoft), payment
integrations (Stripe, PayPal), PDF and QR-code generation, Excel import/export, and
user impersonation.

## Tech Stack

- **Backend:** PHP 8.0.2+, [Laravel 10](https://laravel.com/docs/10.x)
- **Frontend:** Vue 3, Vite, Tailwind CSS
- **Auth:** Laravel Passport, Laravel Socialite (Microsoft)
- **Payments:** Stripe, PayPal Checkout SDK
- **Database:** MySQL / MariaDB (via Eloquent ORM)
- **Cache / Queue:** Redis (Predis)
- **Other:** DomPDF, Simple QRCode, Maatwebsite Excel, Spatie Browsershot

## Requirements

Make sure the following are installed:

- PHP >= 8.0.2 (with the common Laravel extensions: `pdo`, `mbstring`, `openssl`,
  `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/) >= 16 and npm
- MySQL / MariaDB
- (Optional) Redis for cache and queues

## Installation

1. **Clone the repository**

   ```bash
   git clone <repository-url> DataFutureD
   cd DataFutureD
   ```

2. **Install PHP dependencies**

   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**

   ```bash
   npm install
   ```

4. **Create your environment file**

   ```bash
   cp .env.example .env
   ```

5. **Generate the application key**

   ```bash
   php artisan key:generate
   ```

6. **Configure your `.env`** — set at least the database credentials:

   ```env
   APP_NAME=DataFutureD
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=datafutured
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Also configure any third-party services you use: Stripe, PayPal, mail, Redis,
   and the Microsoft Socialite credentials.

7. **Run the database migrations** (add `--seed` to seed sample data):

   ```bash
   php artisan migrate
   ```

8. **Install Passport** (generates the OAuth encryption keys and clients):

   ```bash
   php artisan passport:install
   ```

9. **Link the storage directory** so uploaded files are publicly accessible:

   ```bash
   php artisan storage:link
   ```

## Running the Application

**Backend (Laravel dev server):**

```bash
php artisan serve
```

**Frontend (Vite dev server with hot reload):**

```bash
npm run dev
```

Then open the URL shown by `php artisan serve` (default
[http://localhost:8000](http://localhost:8000)).

## Building for Production

```bash
npm run build      # compile and version frontend assets
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Useful Commands

| Command | Description |
| --- | --- |
| `php artisan migrate:fresh --seed` | Drop all tables, re-migrate, and seed |
| `php artisan queue:work` | Process queued jobs |
| `php artisan tinker` | Interactive REPL |
| `php artisan optimize:clear` | Clear cached config, routes, and views |
| `php artisan test` | Run the test suite |

## Testing

```bash
php artisan test
```

## Project Structure

```
app/            Application code (Models, Controllers, etc.)
config/         Configuration files
database/       Migrations, factories, and seeders
public/         Public web root and compiled assets
resources/      Views, JS, and CSS source files
routes/         Route definitions (web.php, api.php)
storage/        Logs, cache, and uploaded files
```

## License

This project is built on the Laravel framework, which is open-sourced software
licensed under the [MIT license](https://opensource.org/licenses/MIT).
