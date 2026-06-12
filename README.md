# Cloud Storage

Cloud Storage is a Laravel-based file management application for school storage workflows. It provides a web interface, a Sanctum-protected REST API, scoped shared drives, quota tracking, soft delete, starring, sharing, and asynchronous file preview processing.

## Features

- File upload, download, rename, move, star, share, restore, and permanent delete.
- Recursive folder management with soft delete support.
- Role and scope access control for `admin`, `guru_wali`, `guru_jurusan`, `tendik`, and `siswa`.
- Shared drives by class, major, and staff scope.
- Private file uploads by default, with explicit public sharing.
- Storage quota tracking per user.
- Original files archived as ZIP and restored on download/stream.
- Background preview generation for images, videos, office files, and PDFs.
- Mobile/API access through `/api/v1` with Laravel Sanctum.

## Tech Stack

- PHP 8.2+
- Laravel 12
- PostgreSQL
- Laravel Sanctum
- Laravel Socialite
- Tailwind CSS 4
- Vite
- Vanilla JavaScript modules
- `league/flysystem-aws-s3-v3`
- `smalot/pdfparser`
- `spatie/pdf-to-text`

## Project Structure

```text
app/
  Http/Controllers/        Web controllers
  Http/Controllers/Api/    API controllers
  Jobs/                    Queue jobs for preview processing
  Models/                  Eloquent models
  Services/                Shared domain services
database/
  migrations/              Database schema changes
  seeders/                 Master validation and shared drive seeders
resources/
  css/                     Tailwind entry
  js/modules/              Frontend modules
  views/                   Blade views
routes/
  web.php                  Web and session-based JSON routes
  api.php                  Sanctum API routes
tests/
  Feature/                 Feature tests
  Unit/                    Unit tests
```

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

For local development:

```bash
php artisan serve
npm run dev
php artisan queue:work
```

## API Overview

Public authentication endpoints:

- `POST /api/v1/auth/register`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/google`

Protected endpoints require `Authorization: Bearer <token>`:

- `GET /api/v1/auth/me`
- `POST /api/v1/auth/logout`
- `GET /api/v1/files`
- `POST /api/v1/files`
- `GET /api/v1/files/{id}`
- `GET /api/v1/files/{id}/download`
- `GET /api/v1/files/{id}/stream`
- `PATCH /api/v1/files/{id}`
- `DELETE /api/v1/files/{id}`
- `GET /api/v1/folders`
- `POST /api/v1/folders`
- `GET /api/v1/folders/tree`

## Verification

```bash
php artisan test
npm run build
```

## Notes

- Google OAuth registration is limited to users found in `master_validations` or emails configured in `LOCAL_ADMIN_EMAILS`.
- Uploaded files are private by default. Public access is controlled through the `izin` field.
- Preview URLs exposed by the API use Sanctum-protected `/api/v1/files/{id}/stream`.
