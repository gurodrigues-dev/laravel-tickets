# Laravel Ticket Booking Application

A scalable, high-performance ticket booking system built with Laravel 10, featuring optimistic locking for concurrent purchases, Redis session caching, and a modern React-based frontend with Inertia.js.

## Table of Contents

- [Technology Stack](#technology-stack)
- [Architecture & Scalability](#architecture--scalability)
- [Backend Routes](#backend-routes)
- [Frontend Routes](#frontend-routes)
- [Features Implemented](#features-implemented)
- [Email Verification](#email-verification)
- [Local Development Setup](#local-development-setup)
- [API Documentation](#api-documentation)
- [Business Rules & Configurations](#business-rules--configurations)
- [Security Features](#security-features)
- [Additional Tools](#additional-tools)

---

## Technology Stack

### Backend
- **Laravel 10.10** - PHP Framework
- **PostgreSQL 16** - Primary Database
- **Redis 6.2.6** - Session & Cache Storage
- **Laravel Sanctum 3.3** - API Authentication
- **Predis 3.4** - PHP Redis Client

### Frontend
- **React 18.2.0** - UI Library
- **Inertia.js 1.0** - SPA-like Experience
- **Tailwind CSS 3.2.1** - Utility-first CSS
- **Vite 5.0.0** - Build Tool
- **Headless UI 1.4.2** - Accessible Components

### Development Tools
- **Pest 2.36** - Testing Framework
- **L5 Swagger 8.6** - API Documentation
- **Metabase** - Database Visualization
- **Docker Compose** - Container Orchestration

---

## Architecture & Scalability

### Application Design Principles

The application is architected with scalability and performance in mind, designed to handle high-traffic scenarios with concurrent user interactions.

### Optimistic Locking

**Implementation:** Version-based optimistic locking on the `events` table to prevent race conditions during concurrent ticket purchases.

**How it works:**
1. Each event record has a `version` field (starts at 1)
2. When creating/updating a reservation, the client must send the current `version`
3. The system updates the event only if the provided `version` matches the database version
4. On successful update, the `version` increments by 1
5. If versions don't match, a 409 Conflict is returned, prompting the client to retry

**Example Workflow:**
```php
// Client sends reservation request with event version
{
    "event_id": 1,
    "quantity": 2,
    "version": 5  // Current version
}

// Backend attempts atomic update
UPDATE events
SET available_tickets = available_tickets - 2,
    version = version + 1
WHERE id = 1 AND version = 5

// If affected rows = 0 → Version conflict detected
// Client receives 409 and must refresh event data
```

**Benefits:**
- Prevents overselling of tickets
- No database locks required (better performance)
- Handles high concurrency efficiently
- Clear error messages for version conflicts

### Performance SLA

All API endpoints are optimized to respond within **0.5 seconds** under normal load conditions:

| Endpoint | Target Response Time | Optimization |
|----------|---------------------|--------------|
| GET /api/v1/events | < 0.3s | Indexed queries, Redis caching |
| POST /api/v1/reservations | < 0.5s | Optimistic locking, transactions |
| GET /api/v1/reservations/my-reservations | < 0.3s | Paginated queries, indexes |
| Auth endpoints | < 0.2s | Session caching, rate limiting |

### Database Optimization

- **Indexed Columns:** `event_date`, `user_id`, `event_id`
- **Pagination:** All list endpoints support pagination (default: 10, max: 100)
- **Query Optimization:** Eager loading for relationships to prevent N+1 queries
- **Transaction Management:** All reservation operations use database transactions

### Caching Strategy

- **Session Storage:** Redis (configured via `SESSION_DRIVER=redis`)
- **Cache Storage:** Redis (configured via `CACHE_DRIVER=redis`)
- **Session TTL:** 120 minutes (configurable via `SESSION_LIFETIME`)
- **Cache Tags:** Event data caching with automatic invalidation on updates

---

## Backend Routes

### Web Routes (`routes/web.php`)

| Method | URI | Name | Middleware | Description |
|--------|-----|------|------------|-------------|
| GET | `/` | - | guest | Landing page (redirects to events if authenticated) |
| GET | `/profile` | `profile.edit` | auth | User profile edit page |
| PATCH | `/profile` | `profile.update` | auth | Update user profile |
| DELETE | `/profile` | `profile.destroy` | auth | Delete user account |
| GET | `/events` | `events.index` | auth | Events listing page |
| GET | `/my-reservations` | `reservations.my` | auth | User's reservations page |

### Authentication Routes (`routes/auth.php`)

#### Guest Routes
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | `/register` | `register` | Registration page |
| POST | `/register` | - | Process registration |
| GET | `/login` | `login` | Login page |
| POST | `/login` | - | Process login |
| GET | `/forgot-password` | `password.request` | Forgot password page |
| POST | `/forgot-password` | `password.email` | Send password reset link |
| GET | `/reset-password/{token}` | `password.reset` | Reset password page |
| POST | `/reset-password` | `password.store` | Process password reset |

#### Authenticated Routes
| Method | URI | Name | Middleware | Description |
|--------|-----|------|------------|-------------|
| GET | `/verify-email` | `verification.notice` | auth | Email verification notice |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | auth, signed, throttle:6,1 | Verify email address |
| POST | `/email/verification-notification` | `verification.send` | auth, throttle:6,1 | Resend verification email |
| GET | `/confirm-password` | `password.confirm` | auth | Confirm password page |
| POST | `/confirm-password` | - | auth | Confirm password |
| PUT | `/password` | `password.update` | auth | Update password |
| POST | `/logout` | `logout` | auth | Process logout |

### API Routes (`routes/api.php`)

#### Public Routes (`/api/v1`)
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/api/v1/register` | `api.register` | Register new user |
| GET | `/api/v1/verify-email/{id}/{hash}` | `api.verification.verify` | Verify email (signed URL) |
| POST | `/api/v1/auth/login` | `api.auth.login` | User login |
| POST | `/api/v1/forgot-password` | `api.password.email` | Send password reset link |
| POST | `/api/v1/reset-password` | `api.password.reset` | Reset password |

#### Authenticated Routes (`/api/v1` - requires `auth:sanctum`)
| Method | URI | Name | Description |
|--------|-----|------|-------------|
| POST | `/api/v1/auth/logout` | `api.auth.logout` | User logout |
| GET | `/api/v1/auth/user` | `api.auth.user` | Get current user |
| GET | `/api/v1/events` | `api.events.index` | List events (paginated) |
| POST | `/api/v1/events` | `api.events.store` | Create new event |
| GET | `/api/v1/reservations/my-reservations` | `api.reservations.my` | List user's reservations |
| POST | `/api/v1/reservations` | `api.reservations.store` | Create reservation |
| PUT | `/api/v1/reservations/{id}` | `api.reservations.update` | Update reservation |
| DELETE | `/api/v1/reservations/{id}` | `api.reservations.destroy` | Cancel reservation |

---

## Frontend Routes

### Inertia.js Pages

#### Public Pages
| Path | Component | Description |
|------|-----------|-------------|
| `/` | `Welcome` | Landing page with login/register options |
| `/login` | `Auth/Login` | Login form |
| `/register` | `Auth/Register` | Registration form |
| `/forgot-password` | `Auth/ForgotPassword` | Forgot password form |
| `/reset-password/{token}` | `Auth/ResetPassword` | Password reset form |

#### Authenticated Pages
| Path | Component | Description |
|------|-----------|-------------|
| `/events` | `Events/Index` | Events listing with filters and booking |
| `/my-reservations` | `Reservations/Index` | User's reservations management |
| `/profile` | `Profile/Edit` | User profile settings |
| `/verify-email` | `Auth/VerifyEmail` | Email verification prompt |

### Frontend Components

#### Layout Components
- `AuthenticatedLayout` - Main layout for authenticated users
- `GuestLayout` - Layout for guest users

#### UI Components
- `BookingModal` - Ticket booking modal with quantity selection
- `ThemeToggle` - Dark/light mode toggle switch
- `Spinner` - Loading spinner component
- `Modal` - Reusable modal dialog
- `PrimaryButton`, `SecondaryButton`, `DangerButton` - Button variants
- `TextInput`, `Checkbox`, `InputLabel` - Form inputs
- `InputError` - Form error display
- `Dropdown` - Dropdown menu component
- `ResponsiveNavLink` - Navigation links
- `ApplicationLogo` - Application logo

#### Custom Hooks
- `useInfiniteScroll` - Infinite scroll pagination hook
- `useTheme` - Theme management (dark/light mode)

---

## Features Implemented

### 1. Dark/Light Mode

**Implementation:** React Context API with localStorage persistence

**Features:**
- Toggle between light and dark themes
- Theme preference saved to localStorage
- System preference detection on first visit
- Smooth transitions between themes
- All UI components support both themes

**Usage:**
```jsx
import { useTheme } from '@/Context/ThemeContext';

function MyComponent() {
    const { theme, toggleTheme, isDark } = useTheme();

    return (
        <button onClick={toggleTheme}>
            {isDark ? 'Switch to Light' : 'Switch to Dark'}
        </button>
    );
}
```

### 2. Pagination

**Implementation:** Server-side pagination with Laravel's LengthAwarePaginator

**Features:**
- Configurable items per page (default: 10, max: 100)
- Page numbers, previous/next links
- Total count and page metadata
- Optimized queries with limits/offsets

**API Response Format:**
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 10,
    "total": 100,
    "from": 1,
    "to": 10
  },
  "links": {
    "first": "/api/v1/events?page=1",
    "last": "/api/v1/events?page=10",
    "prev": null,
    "next": "/api/v1/events?page=2"
  }
}
```

### 3. Lazy Loading (Infinite Scroll)

**Implementation:** Intersection Observer API with custom React hook

**Features:**
- Automatic loading as user scrolls to bottom
- Loading state indicators
- Error handling with retry functionality
- Optimized with debouncing
- Configurable distance from bottom

**Custom Hook: `useInfiniteScroll`**
```jsx
const {
    data,           // Loaded items
    loading,        // Current loading state
    hasMore,        // More items available
    error,          // Error object
    loadMore,       // Manual load function
    retry,          // Retry failed request
    observerRef     // Observer reference (pass to bottom element)
} = useInfiniteScroll(fetchFunction, {
    initialPage: 1,
    perPage: 10,
    loadMoreText: 'Loading more...'
});
```

### 4. Session Caching with Redis

**Configuration:**
```env
REDIS_HOST=redis          # or 127.0.0.1 for local
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_DRIVER=redis
SESSION_LIFETIME=120      # minutes
```

**Features:**
- Session data stored in Redis for fast access
- Shared sessions across multiple app instances
- Automatic session expiration
- Configurable TTL
- Session encryption enabled

**Redis Keys:**
```
laravel_session:{session_id}     # User session data
laravel_cache:{cache_key}        # Cached data
```

### 5. Rate Limiting

**Implementation:** Laravel's built-in throttle middleware

**Rate Limits Applied:**
| Route | Limit | Period | Description |
|-------|-------|--------|-------------|
| `/api/v1/verify-email/{id}/{hash}` | 6 requests | 1 minute | Email verification |
| `/email/verification-notification` | 6 requests | 1 minute | Resend verification |
| Login endpoint | 5 requests | 1 minute | Failed login attempts |
| Password reset | 5 requests | 1 minute | Reset attempts |

**Usage:**
```php
Route::middleware(['throttle:6,1'])->group(function () {
    // Limited to 6 requests per minute
    Route::get('/verify-email/{id}/{hash}', ...);
});
```

---

## Email Verification

⚠️ **Important:** Email verification is **NOT automatically sent** in this application.

### How to Verify Emails

To verify email addresses, check the **mail_maler logs** which contain the verification route information.

### Verification Workflow

1. User registers → Verification URL generated but not sent
2. Check mail logs for verification URL
3. Access URL manually: `/api/v1/verify-email/{id}/{hash}`
4. Email marked as verified in database

### Mail Logs Location

Mail logs are stored in the Laravel log directory:
```
storage/logs/laravel.log
```

### Resending Verification

Users can request a new verification link (still logged):
```http
POST /email/verification-notification
Authorization: Bearer {token}
```

### Verification Status Check

Check user's verification status via API:
```http
GET /api/v1/auth/user
Authorization: Bearer {token}
```

Response includes `email_verified_at` field:
```json
{
  "id": 1,
  "email": "user@example.com",
  "email_verified_at": "2024-02-18 10:30:00",
  ...
}
```

---

## Local Development Setup

### Prerequisites

- PHP 8.1 or higher
- Composer 2.x
- Node.js 18.x or higher
- Docker and Docker Compose (required for infrastructure)

**⚠️ Important:** The project requires Docker for PostgreSQL and Redis infrastructure. The application does not support running PostgreSQL/Redis directly on the host machine without Docker.

---

## Local Development Setup Clarifications

There are **TWO different approaches** for running the application locally. Choose the one that fits your workflow:

---

### Option 1: Docker Compose for Infrastructure + Local PHP Server

**Purpose:** Run PostgreSQL and Redis via Docker, but run Laravel application locally with `php artisan serve`.

**Benefits:**
- Faster development cycle (no container rebuild needed for code changes)
- Can use Xdebug for debugging locally
- Full control over PHP environment
- Easier to use with IDE tools

**Setup Steps:**

**Step 1: Clone and Install Dependencies**

```bash
git clone <repository-url>
cd laravel-tickets
composer install
npm install
```

**Step 2: Start Infrastructure Services via Docker**

```bash
docker compose up -d postgres redis
```

This will start only:
- **PostgreSQL 16** (port 5432)
- **Redis 6.2.6** (port 6379)

**Step 3: Configure Environment**

```bash
cp .env.example .env
php artisan key:generate
```

**Edit `.env` file with these CRITICAL settings:**

```env
# IMPORTANT: Use 127.0.0.1 because PHP runs locally (not in Docker)
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mirus
DB_USERNAME=postgres
DB_PASSWORD=password

# IMPORTANT: Use 127.0.0.1 because PHP runs locally (not in Docker)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Session and cache drivers
SESSION_DRIVER=redis
CACHE_DRIVER=redis

# Application URL
APP_URL=http://localhost:8000
```

**Why `127.0.0.1`?** Since your PHP application runs on the host machine (not in Docker), it needs to connect to Docker containers via `localhost` (127.0.0.1) using the exposed ports.

**Step 4: Run Migrations and Seed Data**

```bash
php artisan migrate --seed
```

**Step 5: Build Frontend Assets**

```bash
npm run dev
```

**Step 6: Start Local PHP Development Server**

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

**Step 7: Access Additional Services**

- **Swagger Documentation:** `http://localhost:8000/api/documentation`
- **Metabase:** `docker compose up -d metabase` (if needed)

---

### Option 2: Docker Compose for Everything (Infrastructure + App)

**Purpose:** Run the entire stack (PostgreSQL, Redis, and Laravel application) via Docker Compose.

**Benefits:**
- Complete isolation from host system
- Consistent environment across team members
- No local PHP/PostgreSQL/Redis dependencies needed
- Reproducible builds

**⚠️ WARNING:** NOT for production use - every rebuild runs `php artisan migrate:fresh --seed`, which resets all data.

**Setup Steps:**

**Step 1: Clone and Install Dependencies**

```bash
git clone <repository-url>
cd laravel-tickets
composer install
npm install
```

**Step 2: Configure Environment**

```bash
cp .env.example .env
php artisan key:generate
```

**Keep `.env` file with these CRITICAL settings:**

```env
# IMPORTANT: Use service names because PHP runs in Docker network
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=mirus
DB_USERNAME=postgres
DB_PASSWORD=password

# IMPORTANT: Use service names because PHP runs in Docker network
REDIS_HOST=redis
REDIS_PORT=6379

# Session and cache drivers
SESSION_DRIVER=redis
CACHE_DRIVER=redis

# Application URL
APP_URL=http://localhost:8000
```

**Why `postgres` and `redis`?** Since the PHP application runs inside Docker, it can resolve the container names `postgres` and `redis` directly within the Docker network. No need for `127.0.0.1`.

**Step 3: Build and Start All Containers**

```bash
docker compose up --build -d
```

This will start:
- **PostgreSQL 16** (port 5432)
- **Redis 6.2.6** (port 6379)
- **Laravel App** (via NGINX on port 8000)
- **Metabase** (port 3000)

**Step 4: Access Application**

```
Application:  http://localhost:8000
API Docs:     http://localhost:8000/api/documentation
Metabase:     http://localhost:3000
```

**Step 5: View Logs**

```bash
# View all logs
docker compose logs -f

# View specific service logs
docker compose logs -f app
docker compose logs -f postgres
docker compose logs -f nginx
```

**Step 6: Stop Services**

```bash
docker compose down
```

**Step 7: Stop and Remove All Data**

```bash
docker compose down -v  # -v removes volumes (deletes all data)
```

---

## Key Differences Between Options

| Aspect | Option 1 (Local PHP) | Option 2 (All Docker) |
|--------|----------------------|----------------------|
| **PHP Execution** | Local machine | Docker container |
| **DB_HOST in .env** | `127.0.0.1` | `postgres` |
| **REDIS_HOST in .env** | `127.0.0.1` | `redis` |
| **Code Changes** | Immediate effect | Requires container rebuild or volume mount |
| **Debugging** | Native Xdebug support | Requires remote Xdebug setup |
| **Performance** | Faster iteration | Slightly slower |
| **Isolation** | Lower | Complete |
| **Recommended For** | Development | Testing/CI |

### Docker Compose Services Reference

| Service | Port | Description |
|---------|------|-------------|
| `postgres` | 5432 | PostgreSQL database |
| `redis` | 6379 | Redis cache/session store |
| `app` | - | Laravel PHP-FPM application |
| `nginx` | 8000 | Web server |
| `metabase` | 3000 | Database visualization tool |

⚠️ IMPORTANT:
The value of DB_HOST and REDIS_HOST depends on where PHP is running.
Read each option carefully before configuring your .env file.

---

## API Documentation

### API Base URL

```
http://localhost:8000/api/v1
```

### Interactive Swagger Documentation

Access the interactive API documentation at:

```
http://localhost:8000/api/documentation
```

The Swagger UI provides:
- Complete API endpoint documentation
- Request/response schemas
- Authentication examples
- Try-it-out functionality

### HTTP Test Files

The application includes `.http` files for API testing (compatible with VS Code REST Client, IntelliJ IDEA, etc.):

**Location:** `tests/http/`

| File | Endpoints |
|------|-----------|
| `auth.http` | Login, register, logout, user info |
| `events.http` | List events, create event |
| `reservations.http` | Create, update, cancel, list reservations |
| `password-reset.http` | Forgot password, reset password |

**Example: `tests/http/events.http`**

```http
### Get All Events
GET http://localhost:8000/api/v1/events
Authorization: Bearer {{token}}

### Create New Event
POST http://localhost:8000/api/v1/events
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "name": "Tech Conference 2024",
  "description": "Annual technology conference",
  "event_date": "2024-12-15T10:00:00",
  "total_tickets": 500,
  "max_tickets_per_user": 5
}
```

**Environment Variables for Testing:**

Create `tests/http/.env.http`:
```env
token=your_bearer_token_here
```

### Authentication

All authenticated endpoints require a Bearer token in the Authorization header:

```http
Authorization: Bearer {token}
```

### Common Response Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 409 | Conflict (version mismatch, sold out) |
| 422 | Validation Error |
| 500 | Server Error |

---

## Business Rules & Configurations

### Max Tickets Per Reservation

**Default Value:** 5 tickets per user per event

**Configuration:**

1. **Database Default** (set during event creation):
   - Field: `max_tickets_per_user` in `events` table
   - Default: `5`

2. **Environment Variable** (optional override):
   ```env
   MAX_TICKETS_PER_RESERVATION=5
   ```

3. **Seeder Configuration** (for test data):
   ```php
   // database/seeders/EventSeeder.php
   'max_tickets_per_user' => 5,
   ```

**Validation Rules:**

- Users cannot reserve more than `max_tickets_per_user` tickets for a single event
- When updating a reservation, the new quantity + existing reservations must not exceed the limit
- Validation is performed both on client and server

**Error Messages:**

```
"You can only reserve a maximum of {max} tickets per event"

"You already have {count} ticket(s) for this event.
 You can only reserve a maximum of {max} tickets per event."
```

### Event Loading Filters

**Default Behavior:** Events are filtered to show **upcoming events only**.

**Frontend Filtering Options:**

| Filter | Description |
|--------|-------------|
| Show Only Upcoming | Default - hides past events |
| Include Past Events | Shows all events including past |
| Date Range | Filter by start/end dates |
| Search | Search by event name |
| Sort By | Date (asc/desc), Available tickets (asc/desc) |

**Backend Sorting:**

Events are sorted by `event_date ASC` by default:

```php
Event::orderBy('event_date', 'asc')->paginate($perPage, ['*'], 'page', $page);
```

### Reservation Business Rules

**Rule 1: Cannot Reserve Events with No Available Tickets**

```php
if ($event->available_tickets < $quantity) {
    throw new Exception('Not enough tickets available', 409);
}
```

**Rule 2: Cannot Reserve Past Events**

Frontend validation prevents booking events where `event_date < now()`.

**Rule 3: Reservation Status Management**

| Status | Description |
|--------|-------------|
| `active` | Active reservation (default) |
| `cancelled` | Cancelled reservation (tickets released) |

**Rule 4: Cancelled Reservations**

- When cancelled, tickets are released back to available_tickets
- Status changes to `cancelled`
- Cannot be modified once cancelled

**Rule 5: Optimistic Locking on Reservations**

All reservation operations require the current event version:

```json
{
  "event_id": 1,
  "quantity": 2,
  "version": 5  // Must match current event version
}
```

If version doesn't match:
```json
{
  "message": "Version conflict. The event may have been modified by
   another user. Please refresh and try again."
}
```

**Status Code:** 409 Conflict

---

## Security Features

### 1. Password Hashing

**Algorithm:** bcrypt (default Laravel hashing)

**Configuration:**
```php
// config/hashing.php
'driver' => 'bcrypt',
'rounds' => 10,
```

**Usage:**
```php
// Hashing
$hashedPassword = Hash::make('password');

// Verifying
if (Hash::check('password', $hashedPassword)) {
    // Password is correct
}
```

### 2. CSRF Protection

**Status:** Enabled for all web routes

**Implementation:**
- CSRF tokens automatically included in forms
- `VerifyCsrfToken` middleware applied to web routes
- API routes exempt from CSRF (use Sanctum tokens instead)

**Middleware:**
```php
// app/Http/Kernel.php
'web' => [
    // ...
    \App\Http\Middleware\VerifyCsrfToken::class,
    // ...
]
```

### 3. Session Encryption

**Status:** Enabled

**Configuration:**
```env
APP_KEY=base64:your-generated-key
```

**Implementation:**
- All session data encrypted before storage
- Automatically decrypted on retrieval
- Uses AES-256-CBC encryption

**Middleware:**
```php
// app/Http/Kernel.php
'web' => [
    \App\Http\Middleware\EncryptCookies::class,
    // ...
]
```

### 4. Optimistic Locking on Events

**Purpose:** Prevent race conditions and overselling

**Implementation:**
- Version field on events table
- Atomic updates with version checking
- Clear conflict error messages

**SQL Example:**
```sql
UPDATE events
SET available_tickets = available_tickets - 2,
    version = version + 1
WHERE id = 1 AND version = 5
-- Returns 0 affected rows if version mismatch
```

### 5. HTTP-Only Cookies

**Status:** Enabled for session cookies

**Configuration:**
```php
// config/session.php
'http_only' => true,
'secure' => env('SESSION_SECURE_COOKIE', false),
'same_site' => 'lax',
```

**Benefits:**
- Cookies not accessible via JavaScript (XSS protection)
- Secure flag enabled in production
- Same-site protection against CSRF

### 6. Additional Security Measures

| Feature | Implementation |
|---------|---------------|
| SQL Injection | Eloquent ORM with parameter binding |
| XSS Protection | Blade template auto-escaping |
| Mass Assignment | `$fillable` whitelist on models |
| Input Validation | Form request validation |
| Rate Limiting | Throttle middleware on sensitive routes |
| CORS | Cross-origin resource sharing configured |
| Trusted Proxies | Load balancer support |

---
