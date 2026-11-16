# UIUNest

**The first version of Nestly.** A housing, flatmate-matching and peer-to-peer marketplace platform for UIU students and landlords, built with PHP and MySQL as a database-course project. It was later rebuilt from scratch, with a team, as **[Nestly](https://github.com/SpicyFalcon619/nestly-core)** (Next.js and Supabase, live at [nestly-core.vercel.app](https://nestly-core.vercel.app)). This repository is kept as the original, and Nestly is where development continues.

![PHP](https://img.shields.io/badge/PHP-8-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-database-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Vanilla JS](https://img.shields.io/badge/JavaScript-vanilla-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

![UIUNest home page](UIUNest%20report/screenshot_home.png)

## What it does

| For | Features |
|---|---|
| **Students** | Browse verified listings around campus on a Leaflet map with zone and price filters; find flatmates with a weighted compatibility score (sleep, diet, noise, cleanliness, guests); buy and sell furniture and study materials with offers and counter-offers; split monthly mess bills; post "Looking For" ads; keep a watchlist; get in-app notifications |
| **Landlords** | Create and manage listings with photos, amenities and pricing; review tenancy applications; upload identity documents for a verified badge; create monthly bills for tenants |
| **Master admin** | Manage users (suspend, activate, revoke verification), approve or reject verification documents, resolve complaints, and see live platform statistics |

## Tech stack

PHP 8 with PDO (prepared statements, `password_hash`), MySQL (19 tables), vanilla JavaScript, [Leaflet](https://leafletjs.com), [Chart.js](https://www.chartjs.org/) and [Lucide](https://lucide.dev) icons.

## Run it locally

You need a PHP and MySQL stack such as [XAMPP](https://www.apachefriends.org/).

1. Start **Apache** and **MySQL**.
2. In phpMyAdmin, create a database named `uiunest` and import [`database/schema.sql`](database/schema.sql).
3. Put the folder in `htdocs/UIU-Nest` (or symlink it) and open `http://localhost/UIU-Nest`.
4. To load the master admin and clean tables, run from a terminal: `php api/reset_db.php`. The maintenance scripts (`reset_db.php`, `alter_db_interactions.php`, `database/seed.php`) only run from the command line; the web server refuses them.

### Master admin account

- **Login:** `ADM-MASTER` or `master@admin.com`
- **Password:** `1265Master`

This is a development account for local use.

## Project structure

```text
api/        PHP endpoints: login, listings, applications, bills, marketplace, offers, complaints, notifications, admin actions
database/   schema.sql (19 tables), optional seed data
docs/       architecture notes, feature log, differences from the original ERD
*.html      pages: home, listings, exchange, bills, dashboard, profile, admin
app.js      navigation, auth state and shared helpers
style.css   the design system
```

More detail is in [`docs/system_architecture.md`](docs/system_architecture.md), and the course report is in `UIUNest report/`.

## Release notes

- **v1.2.1:** a floating pill-shaped mobile navbar with a sliding indicator, and login and register buttons that follow the account state.
- **v1.2.0:** responsive fixes on the bills and profile pages, a navbar that pins on the home page and hides on inner pages, and rewritten home-page copy.

## Author

Ahmad Maruf Hossain ([@SpicyFalcon619](https://github.com/SpicyFalcon619)), United International University. The Next.js rewrite, Nestly, was built with Sadia Noman Tonni, Nazha Tarannum Roza and Mohammad Mahadi Hasan.
