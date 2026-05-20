# AgroLens — Land Insights Platform

**Land Insights: Analysis of Land Holding, Irrigation, and Cropping Patterns Across Regions**

AgroLens is a production-oriented agricultural intelligence platform for government departments, researchers, NGOs, and policymakers in India. It delivers real-time analytics, GIS visualization, survey collection, exportable reports, and secure REST APIs.

> **Stack note:** Built on **Laravel 12** (latest), Livewire 3, Reverb, Sanctum, Tailwind CSS 4, Chart.js, and Leaflet. Requirements listed as Laravel 11 in the spec map directly to this codebase.

## Features

| Module | Capabilities |
|--------|----------------|
| **Authentication** | Breeze + Livewire, email verification, password reset, RBAC |
| **RBAC** | Government Officer (full) vs Public Viewer (read-only) |
| **Regional hierarchy** | Country → State → District (+ taluka/village ready) |
| **Land & farmers** | Holdings, categories, geo-tags, fragmentation |
| **Irrigation** | Canal, wells, rain-fed, water stress indicators |
| **Well depth** | Trends, alerts, district comparisons |
| **Cropping** | Kharif/Rabi/Zaid patterns, diversity index |
| **Dashboard** | Livewire KPIs, Chart.js, filters, 30s polling + Reverb events |
| **GIS** | Leaflet map with district heat markers |
| **Reports** | PDF/CSV via queue jobs (DomPDF + League CSV) |
| **API** | Sanctum-protected `/api/v1/analytics/dashboard` |
| **Architecture** | Repository pattern, service classes, form requests, policies |

## Quick Start (Local)

### Prerequisites

- PHP 8.2+ (8.3 recommended)
- Composer
- Node.js 20+
- MySQL 8+ (or SQLite for development)

### Installation

```bash
git clone <repo> AgroLens
cd AgroLens

composer install
cp .env.example .env
php artisan key:generate

# MySQL (recommended)
# Set DB_CONNECTION=mysql and credentials in .env

php artisan migrate:fresh --seed
npm install
npm run build
```

> **Windows PowerShell:** Use `;` instead of `&&` between commands, e.g. `npm install; npm run build`

### Default accounts (after seeding)

| Role | Email | Password |
|------|-------|----------|
| Government Officer | `officer@agrolens.gov.in` | `password` |
| Public Viewer | `viewer@agrolens.gov.in` | `password` |

### Run the application

```bash
# Terminal 1 — web
php artisan serve

# Terminal 2 — queue (reports, notifications)
php artisan queue:work

# Terminal 3 — Vite (development)
npm run dev

# Terminal 4 — Reverb (real-time, optional)
php artisan reverb:start
```

Or use the combined dev script:

```bash
composer run dev
```

Visit: `http://localhost:8000`

## Docker

```bash
docker compose up --build
```

Services: `app` (8000), `mysql` (3306), `redis`, `reverb` (8080), `queue`.

Run migrations inside the app container:

```bash
docker compose exec app php artisan migrate --seed
```

## Real-time broadcasting (Reverb)

Add to `.env`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=agrolens
REVERB_APP_KEY=local-key
REVERB_APP_SECRET=local-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

Dashboard listens on channel `analytics` for `analytics.updated` events.

## REST API

Create a token:

```bash
php artisan tinker --execute '$u = App\Models\User::first(); echo $u->createToken("api")->plainTextToken;'
```

```http
GET /api/v1/analytics/dashboard?state=Punjab
Authorization: Bearer {token}
Accept: application/json
```

Rate limit: 60 requests/minute per user/IP.

## Project structure

```
app/
├── Enums/              # Roles, permissions, irrigation, seasons
├── Events/             # AnalyticsUpdated (broadcast)
├── Http/
│   ├── Controllers/    # Reports, API
│   ├── Middleware/     # EnsureUserHasPermission
│   └── Requests/       # StoreReportRequest
├── Jobs/               # GenerateReportJob, RefreshAnalyticsCacheJob
├── Livewire/           # AnalyticsDashboard, GisMap
├── Models/             # Farmer, LandHolding, Well, Region, ...
├── Policies/           # ReportPolicy
├── Repositories/       # AnalyticsRepository (+ interface)
├── Services/           # AnalyticsService, ReportExportService
└── Traits/             # HasPermissions
```

## Testing

```bash
php artisan test --compact
```

## Deployment checklist

1. Set `APP_ENV=production`, `APP_DEBUG=false`
2. Configure MySQL, Redis, queue worker (Supervisor)
3. Run `php artisan config:cache route:cache view:cache`
4. Enable HTTPS and update `REVERB_SCHEME=https`
5. Schedule `RefreshAnalyticsCacheJob` via `routes/console.php`
6. Configure mail/SMS drivers for `WaterShortageAlert` notifications

## License

MIT
