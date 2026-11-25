# Filament Starter

A ready-to-use **Laravel 12 + Filament 5** admin starter. It already has users, branches, roles and permissions, and an activity log, so new projects can start from here instead of from scratch.

## What's included

- **Users**, each assigned to a **branch**
- **Roles and permissions** with [Filament Shield](https://github.com/bezhanSalleh/filament-shield)
- **Activity log** with Filament Logger
- **Announcements** shown in the panel
- **Change password** page for signed-in users
- Seeders for branches and a default admin user

## Tech stack

PHP 8.2+, Laravel 12, Filament 5, Vite, Tailwind.

## Start a new project from this starter

```bash
git clone https://github.com/userglang/filament-starter.git my-project
cd my-project
rm -rf .git && git init      # start fresh history for the new project

composer install
npm install

cp .env.example .env
php artisan key:generate

# set the DB_* values in .env, then:
php artisan migrate --seed
php artisan shield:generate --all

composer run dev
```

Open http://localhost:8000/main.

> **Default login (from `UserSeeder`):** `admin@example.com` / `password`.
> Change it before deploying anywhere.

Tip: on GitHub, turn on **Settings → Template repository**. You'll then get a "Use this template" button for new projects.

## Security

See [SECURITY.md](SECURITY.md).
