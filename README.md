# 🛍️ Shopbi API

### Modern Content & E-Commerce Backend

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13.35-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Filament-3.3-FDAE4B?style=for-the-badge" alt="Filament">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="MIT License">
</p>

<p align="center">
  <b>A modern, secure, and scalable backend for content management, authentication, media handling, and e-commerce applications.</b>
</p>

Shopbi API is a Laravel-powered backend designed to deliver a versioned REST API, mobile-based authentication, advanced content management, and a powerful administration panel.

Built with Laravel Sanctum, Spatie packages, and Filament, Shopbi provides a solid foundation for modern web applications powered by a Next.js frontend.

> 🚀 **Our goal:** Build a modular, maintainable, and extensible backend for content-driven websites and e-commerce platforms.

---

## 📑 Table of Contents

- [✨ Features](#-features)
- [🛠️ Tech Stack](#️-tech-stack)
- [🏗️ Architecture](#️-architecture)
- [📂 Project Structure](#-project-structure)
- [🚀 Installation](#-installation)
- [⚙️ Environment Configuration](#️-environment-configuration)
- [🔐 Authentication](#-authentication)
- [🔌 API Documentation](#-api-documentation)
- [🧪 API Testing with cURL](#-api-testing-with-curl)
- [🗄️ Database Structure](#️-database-structure)
- [👥 Roles and Permissions](#-roles-and-permissions)
- [🎛️ Admin Panel](#️-admin-panel)
- [🔍 SEO Features](#-seo-features)
- [🖼️ Media Management](#️-media-management)
- [🔒 Security](#-security)
- [🧪 Testing](#-testing)
- [📸 Screenshots](#-screenshots)
- [🗺️ Roadmap](#️-roadmap)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [📬 Contact](#-contact)

---

## ✨ Features

| Module | Features |
|---|---|
| 🔐 Authentication | Mobile OTP login, registration, Sanctum tokens, session management |
| 📝 Content Management | CRUD operations, hierarchical categories, tags, nested comments |
| 🔍 SEO | Meta tags, Open Graph, Twitter Cards, JSON-LD, XML Sitemap |
| 👥 User Management | User profiles, avatars, roles, permissions, account status |
| 🎛️ Admin Panel | Content, user, comment, and settings management with Filament |
| 🖼️ Media | Featured images, galleries, and file attachments |
| ⚙️ Settings | Grouped key-value configuration, caching, public settings API |
| 🛡️ Security | Rate limiting, authorization policies, input validation, access control |

### Highlights

- ✅ Versioned REST API using `/api/v1`
- ✅ Phone number authentication with one-time passwords
- ✅ SMS provider integration with Kavenegar and SMS.ir
- ✅ Support for `post`, `page`, and `product` content types
- ✅ Hierarchical categories and many-to-many tags
- ✅ Nested comments with moderation support
- ✅ Scheduled content publishing
- ✅ Fine-grained role and permission management
- ✅ Filament-powered administration panel
- ✅ Media management with Spatie MediaLibrary
- ✅ Redirect management and XML Sitemap generation
- ✅ Designed for seamless integration with Next.js

> **Note:** These features describe the intended project capabilities. Verify each feature against the actual implementation before treating it as production-ready.

---

## 🛠️ Tech Stack

| Technology | Version | Purpose |
|---|---|---|
| PHP | 8.3 | Backend programming language |
| Laravel | 13.35 | Application framework |
| MySQL | 8.0 | Relational database |
| Laravel Sanctum | Installed version | API authentication |
| Spatie Laravel Permission | Installed version | Roles and permissions |
| Spatie MediaLibrary | Installed version | Media management |
| Spatie Laravel Sluggable | Installed version | Automatic slug generation |
| Spatie Laravel Sitemap | Installed version | Sitemap generation |
| Filament | 3.3 | Admin panel |
| Livewire | 3.8 | Interactive server-driven UI |
| Guzzle HTTP | Installed version | HTTP requests to external services |
| Next.js | Independent frontend | API consumer |

> Confirm the exact package versions using `composer.json` and `composer.lock` before deployment.

---

## 🏗️ Architecture

Shopbi follows Laravel's layered application architecture to separate HTTP handling, business logic, data access, and authorization.

```text
┌──────────────────────────────┐
│        Next.js Frontend      │
│    Website / User Interface  │
└──────────────┬───────────────┘
               │ HTTPS / JSON
               ▼
┌──────────────────────────────┐
│       Laravel REST API       │
│           /api/v1            │
├──────────────────────────────┤
│ Routes & Middleware          │
│ Controllers & Form Requests  │
│ API Resources                │
│ Policies & Authorization     │
│ Services & Eloquent Models   │
└──────────┬──────────┬────────┘
           │          │
           ▼          ▼
      ┌─────────┐  ┌───────────────┐
      │  MySQL  │  │ SMS Providers │
      │   8.0   │  │ Kavenegar     │
      └─────────┘  │ SMS.ir        │
                   └───────────────┘

       ┌────────────────────────┐
       │    Filament Admin      │
       │   Management Panel     │
       └────────────────────────┘
```

### Main Components

- **Routes:** Define API endpoints and HTTP methods.
- **Controllers:** Handle requests and coordinate application operations.
- **Form Requests:** Validate incoming data and request authorization.
- **API Resources:** Standardize JSON responses.
- **Services:** Encapsulate reusable business logic, including OTP and SMS handling.
- **Models:** Represent database entities and relationships.
- **Policies:** Control access to application resources.
- **Observers:** Execute model-related event logic.
- **Filament:** Provide the administrative interface.

---

## 📂 Project Structure

```text
backend/
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── PostResource.php
│   │   │   ├── CategoryResource.php
│   │   │   ├── TagResource.php
│   │   │   ├── UserResource.php
│   │   │   └── CommentResource.php
│   │   ├── Pages/
│   │   │   └── ManageSettings.php
│   │   └── Widgets/
│   │       ├── StatsOverview.php
│   │       ├── LatestPosts.php
│   │       └── LatestComments.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── AuthController.php
│   │   │           ├── PostController.php
│   │   │           └── ...
│   │   ├── Requests/
│   │   │   └── Api/V1/
│   │   ├── Resources/
│   │   │   └── Api/V1/
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Post.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Comment.php
│   │   ├── OtpCode.php
│   │   ├── Redirect.php
│   │   └── Setting.php
│   │
│   ├── Observers/
│   │   └── PostObserver.php
│   │
│   ├── Policies/
│   │   └── PostPolicy.php
│   │
│   ├── Services/
│   │   ├── SmsService.php
│   │   ├── OtpService.php
│   │   └── MediaService.php
│   │
│   └── Traits/
│       └── ApiResponse.php
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│       ├── RolePermissionSeeder.php
│       ├── AdminUserSeeder.php
│       ├── SettingSeeder.php
│       └── CategorySeeder.php
│
├── routes/
│   ├── api.php
│   └── web.php
│
├── config/
├── resources/
├── storage/
├── tests/
├── public/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

This tree represents the main application structure. Additional files and directories may exist in the actual repository.

---

## 🚀 Installation

### Prerequisites

Make sure the following tools are installed:

- PHP 8.3 with the required Laravel extensions
- Composer
- MySQL 8.0
- Git
- PHP extensions such as `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `xml`, and `intl`, as required by the installed dependencies

### 1. Clone the Repository

```bash
git clone <YOUR_GITHUB_REPOSITORY_URL> backend
cd backend
```

Replace the placeholder with your actual repository URL.

### 2. Install Dependencies

```bash
composer install
```

### 3. Create the Environment File

**Linux / macOS:**

```bash
cp .env.example .env
```

**Windows CMD:**

```bat
copy .env.example .env
```

**PowerShell:**

```powershell
Copy-Item .env.example .env
```

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Create the Database

Run the following SQL statement in MySQL:

```sql
CREATE DATABASE shopbi
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Configure your database credentials in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopbi
DB_USERNAME=root
DB_PASSWORD=
```

These values are suitable as an example for a local development environment. Use a dedicated database account with restricted privileges in production.

### 6. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

If configured, the seeders will create initial roles, permissions, settings, and categories.

Review the seeders before running them again to avoid duplicate data or unintended modifications.

### 7. Create the Storage Symlink

```bash
php artisan storage:link
```

This creates the public storage link when the standard Laravel filesystem configuration is used.

### 8. Clear Application Caches

```bash
php artisan optimize:clear
```

### 9. Start the Development Server

```bash
php artisan serve
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

API base URL:

```text
http://127.0.0.1:8000/api/v1
```

### 10. Verify the Registered Routes

```bash
php artisan route:list --path=api/v1
```

To inspect the Filament routes:

```bash
php artisan route:list --path=admin
```

The actual admin URL depends on the project's Filament configuration.

---

## ⚙️ Environment Configuration

Example environment configuration:

```dotenv
APP_NAME="Shopbi API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

FRONTEND_URL=http://localhost:3000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shopbi
DB_USERNAME=root
DB_PASSWORD=

SMS_DRIVER=log
KAVENEGAR_API_KEY=
SMSIR_API_KEY=

OTP_EXPIRE_MINUTES=2
OTP_LENGTH=6
OTP_MAX_ATTEMPTS=5
OTP_RESEND_DELAY=60

SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
```

> These are example values, not guaranteed application defaults. Make sure every environment variable is supported by the actual application configuration and service implementations. Use `SMS_DRIVER=log` only if a corresponding driver is implemented.

### Environment Variables

| Variable | Description |
|---|---|
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Enables detailed error output; must be `false` in production |
| `APP_URL` | Backend application URL |
| `FRONTEND_URL` | Frontend application URL |
| `DB_HOST` | Database hostname |
| `DB_PORT` | MySQL port |
| `DB_DATABASE` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `SMS_DRIVER` | Selected SMS provider |
| `KAVENEGAR_API_KEY` | Kavenegar API key |
| `SMSIR_API_KEY` | SMS.ir API key |
| `OTP_EXPIRE_MINUTES` | OTP validity duration |
| `OTP_LENGTH` | Number of OTP digits |
| `OTP_MAX_ATTEMPTS` | Maximum verification attempts |
| `OTP_RESEND_DELAY` | Minimum delay between OTP requests |
| `SANCTUM_STATEFUL_DOMAINS` | Trusted domains for stateful Sanctum authentication |

### Security Notes

- Never commit `.env` to version control.
- Never expose SMS API keys to the frontend.
- Set `APP_DEBUG=false` in production.
- Clear and rebuild configuration caches after environment changes as part of deployment.
- Configure CORS and Sanctum according to the actual authentication strategy.

---

## 🔐 Authentication

Shopbi API uses Laravel Sanctum for API authentication and is designed around phone number verification using one-time passwords.

### OTP Authentication Flow

```text
User
  |
  v
Submit Phone Number
  |
  v
POST /api/v1/auth/send-otp
  |
  v
Generate OTP and Apply Rate Limits
  |
  v
Send OTP Through SMS Provider
  |
  v
POST /api/v1/auth/verify-otp
  |
  v
Validate OTP and Phone Number
  |
  v
Issue Sanctum Token
  |
  v
Access Protected API Endpoints
```

### Authentication Features

- SMS-based OTP delivery
- OTP expiration and attempt limits
- Resend throttling
- Token issuance for protected endpoints
- Current user profile retrieval
- Single-session logout
- Logout from all devices
- User registration

### Token Expiration

The intended token lifetime is 30 days.

**Important:** Sanctum personal access tokens do not automatically expire after 30 days by default. The application's token expiration configuration and token issuance logic must be configured to enforce this requirement.

Likewise, a `/refresh` endpoint is listed as a project endpoint, but Sanctum does not provide an OAuth2-style refresh-token flow by default. Verify that the endpoint has a real implementation before relying on it.

---

## 🔌 API Documentation

### Base Configuration

| Property | Value |
|---|---|
| Base URL | `http://127.0.0.1:8000/api/v1` |
| Protocol | HTTP / HTTPS |
| Data Format | JSON |
| Authentication | Bearer Token |
| API Version | `v1` |

Use HTTPS in production.

### Suggested Success Response

```json
{
  "success": true,
  "message": "Request completed successfully",
  "data": {}
}
```

### Suggested Error Response

```json
{
  "success": false,
  "message": "The provided data is invalid",
  "errors": {
    "mobile": [
      "The mobile number is invalid"
    ]
  }
}
```

These are example response formats. Align them with the project's actual API response trait, resources, and exception handler.

### 🔐 Public Authentication Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/auth/send-otp` | Send a one-time password |
| `POST` | `/auth/verify-otp` | Verify OTP and authenticate |
| `POST` | `/auth/register` | Register a new user |

### 🔐 Protected Authentication Endpoints

A valid access token must be provided in the `Authorization` header.

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/auth/me` | Retrieve the authenticated user |
| `POST` | `/auth/logout` | Log out the current session |
| `POST` | `/auth/logout-all` | Log out all sessions |
| `POST` | `/auth/refresh` | Refresh a token, if implemented |

### 📝 Public Content Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/posts` | Retrieve content listings |
| `GET` | `/posts/{slug}` | Retrieve content details |

Filtering, searching, sorting, and pagination depend on the actual controller implementation.

### 📝 Content Management Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/posts` | Create content |
| `PUT` | `/posts/{slug}` | Update content |
| `DELETE` | `/posts/{slug}` | Delete content |
| `GET` | `/my-posts` | Retrieve the authenticated user's content |

Protected endpoints should use authentication middleware and appropriate authorization policies. Authentication alone does not grant permission to modify every resource.

### Example: List Content

```http
GET /api/v1/posts?page=1&per_page=10
Accept: application/json
```

Example response:

```json
{
  "success": true,
  "message": "Content retrieved successfully",
  "data": [
    {
      "id": 1,
      "title": "Getting Started with Shopbi",
      "slug": "getting-started-with-shopbi",
      "type": "post",
      "excerpt": "Explore the platform's features",
      "published_at": "2026-10-01T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

### Example: Retrieve Content Details

```http
GET /api/v1/posts/getting-started-with-shopbi
Accept: application/json
```

Example response:

```json
{
  "success": true,
  "message": "Content retrieved successfully",
  "data": {
    "id": 1,
    "title": "Getting Started with Shopbi",
    "slug": "getting-started-with-shopbi",
    "type": "post",
    "content": "Full content goes here...",
    "views_count": 120,
    "likes_count": 15,
    "comments_count": 4,
    "reading_time": 5,
    "seo": {
      "meta_title": "Getting Started with Shopbi",
      "meta_description": "Explore the platform's features"
    }
  }
}
```

The example fields and pagination structure must be aligned with the actual API resources.

---

## 🧪 API Testing with cURL

The following examples assume that the OTP request uses `mobile` and the verification request uses `code`. Adjust the payloads to match your actual Form Requests.

### Step 1: Send an OTP

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/auth/send-otp" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "mobile": "09123456789"
  }'
```

Example response:

```json
{
  "success": true,
  "message": "Verification code sent",
  "data": {
    "expires_in": 120
  }
}
```

OTP delivery depends on the configured SMS driver. For local testing, use a dedicated testing mechanism rather than exposing OTPs in public API responses or accessible logs.

### Step 2: Verify the OTP

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/auth/verify-otp" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "mobile": "09123456789",
    "code": "123456"
  }'
```

Example response:

```json
{
  "success": true,
  "message": "Authentication successful",
  "data": {
    "user": {
      "id": 1,
      "mobile": "09123456789"
    },
    "token": "YOUR_ACCESS_TOKEN",
    "token_type": "Bearer"
  }
}
```

The values are illustrative. Replace them with the actual response returned by your API.

### Step 3: Create a Post

Use the token returned by the authentication endpoint:

```bash
curl -X POST "http://127.0.0.1:8000/api/v1/posts" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -d '{
    "title": "My First Post",
    "slug": "my-first-post",
    "type": "post",
    "content": "This is my first post on Shopbi.",
    "status": "draft"
  }'
```

Replace `YOUR_ACCESS_TOKEN` with a valid token.

The accepted fields, valid `type` and `status` values, and whether clients may submit `slug` directly depend on the project's validation and persistence logic.

### HTTP Status Codes

| Status | Meaning |
|---|---|
| `200 OK` | Request successful |
| `201 Created` | Resource created |
| `204 No Content` | Successful response without a body |
| `400 Bad Request` | Invalid request |
| `401 Unauthorized` | Missing or invalid authentication |
| `403 Forbidden` | Insufficient permissions |
| `404 Not Found` | Resource not found |
| `422 Unprocessable Entity` | Validation error |
| `429 Too Many Requests` | Rate limit exceeded |
| `500 Internal Server Error` | Unexpected server error |

---

## 🗄️ Database Structure

The main expected database tables are:

| Table | Description |
|---|---|
| `users` | User accounts |
| `otp_codes` | One-time password records |
| `personal_access_tokens` | Sanctum personal access tokens |
| `categories` | Hierarchical categories |
| `posts` | Articles, pages, and product content |
| `tags` | Content tags |
| `post_tag` | Many-to-many post/tag relationship |
| `comments` | Comments and parent-child relationships |
| `redirects` | Redirect records |
| `settings` | Key-value application settings |
| `media` | Media library records |
| `roles` | User roles |
| `permissions` | Application permissions |
| `model_has_roles` | Model-to-role relationships |
| `model_has_permissions` | Direct model permissions |
| `role_has_permissions` | Role-to-permission relationships |

Additional Spatie Permission tables may vary depending on the installed version and configuration.

### Core Relationships

```text
User
 ├── hasMany Posts
 ├── hasMany Comments
 └── belongsToMany Roles

Post
 ├── belongsTo User
 ├── belongsTo Category
 ├── belongsToMany Tags
 ├── hasMany Comments
 └── hasMany Media

Category
 ├── belongsTo Parent Category
 └── hasMany Child Categories

Comment
 ├── belongsTo Post
 ├── belongsTo User
 └── belongsTo Parent Comment
```

This diagram illustrates the intended conceptual relationships. Verify the actual foreign keys and relationship definitions in the models and migrations.

---

## 👥 Roles and Permissions

Role and permission management is designed around Spatie Laravel Permission.

| Role | Content Management | User Management | Settings Management | User Features |
|---|---|---|---|---|
| `admin` | Full access | Full access | Full access | Full access |
| `editor` | Authorized content management | No | No | As permitted |
| `author` | Own content only | No | No | Own content |
| `user` | No | No | No | Profile and general features |

### Role Definitions

- **Admin:** Full administrative access according to application policies.
- **Editor:** Manages authorized content, categories, tags, and comments.
- **Author:** Creates and manages their own content according to assigned permissions and publication rules.
- **User:** Accesses public features and account-related functionality.

### Authorization Principles

1. Enforce all permissions on the backend.
2. Hiding frontend buttons is not a security mechanism.
3. Protect content operations with policies and ownership checks.
4. Define granular permissions in seeders and application configuration.
5. Verify access to the Filament panel independently.

The actual permissions depend on the roles, permissions, and policies configured in the application.

---

## 🎛️ Admin Panel

Shopbi's administration panel is designed with Filament.

### Management Modules

- 📝 Posts and pages
- 🗂️ Categories
- 🏷️ Tags
- 💬 Comment moderation
- 👥 Users and roles
- ⚙️ Site settings
- 📊 Dashboard statistics
- 🖼️ Media management

### Dashboard Widgets

| Widget | Purpose |
|---|---|
| `StatsOverview` | Display high-level statistics |
| `LatestPosts` | Show recently created content |
| `LatestComments` | Show recent comments |

Dashboard metrics may include user counts, post counts, page views, and comments. Their calculation and update behavior depend on the actual widget implementation.

### Admin Access

Filament commonly uses `/admin` as a panel path, but the actual URL depends on the configured Panel Provider.

Create the initial administrator using the project's intended seeder or secure account creation process. Never publish default credentials in a public repository.

---

## 🔍 SEO Features

Shopbi is designed to support SEO requirements for content-driven websites and online stores.

| Feature | Description |
|---|---|
| Meta Title | Search engine page title |
| Meta Description | Search result description |
| Open Graph | Social sharing metadata |
| Twitter Cards | Link previews on X |
| JSON-LD | Structured data |
| `is_indexable` | Intended control over indexing |
| `is_followable` | Intended control over link following |
| Redirect 301 | Permanent URL redirection |
| XML Sitemap | Discoverable URL listing |
| Robots.txt | Crawler instructions |

### Example SEO Data

```json
{
  "meta_title": "Shopbi Shopping Guide",
  "meta_description": "Discover Shopbi's features and products",
  "og_title": "Shopbi Shopping Guide",
  "og_description": "A complete guide to using the store",
  "og_image": "https://example.com/images/guide.jpg",
  "is_indexable": true,
  "is_followable": true
}
```

This is an illustrative data structure.

**Architecture note:** If Next.js renders the frontend HTML, the frontend must also generate the relevant metadata. Storing SEO fields in the API alone does not guarantee that the final page contains correct meta tags or JSON-LD.

---

## 🖼️ Media Management

Media handling is designed around Spatie MediaLibrary.

### Use Cases

- Featured images
- Content galleries
- File attachments
- User avatars
- Media associated with different models

### Best Practices

- Separate public and private files according to application requirements.
- Validate MIME types and upload sizes on the server.
- Never trust uploaded filenames.
- Use protected endpoints or temporary URLs for private media.
- Keep filesystem configuration consistent across environments.

---

## ⚙️ Site Settings

The settings system is designed around grouped key-value configuration.

| Group | Purpose |
|---|---|
| `general` | Site name and general information |
| `seo` | Global SEO configuration |
| `social` | Social media links |
| `sms` | SMS provider settings |
| `system` | Technical and system settings |

### Design Principles

- Cache frequently accessed settings to reduce database queries.
- Invalidate the cache when settings change.
- Separate public configuration from sensitive values.
- Never expose API keys or passwords through public endpoints.

Sensitive configuration must be protected by appropriate authorization rules.

---

## 🔒 Security

Security must be enforced throughout the application.

### OTP Security

- Rate-limit OTP sending and verification.
- Limit failed verification attempts.
- Prevent reuse of consumed OTPs.
- Enforce short expiration periods.
- Protect authentication endpoints against automated abuse.
- Avoid unnecessary disclosure of account existence.

### Token Security

- Store tokens securely on the client.
- Use HTTPS in production.
- Revoke tokens during logout.
- Enforce the configured expiration policy.
- Never write access tokens to logs.

### Authorization

- Use Laravel Policies.
- Validate resource ownership.
- Separate roles from granular permissions.
- Enforce authorization on the server.

### Database and Input Validation

- Protect against mass assignment using `$fillable` or `$guarded`.
- Validate input using Form Requests.
- Avoid unsafe SQL construction.
- Manage schema changes through migrations.
- Apply Soft Deletes only to models configured to support them.

### Network and File Security

- Restrict CORS to trusted origins.
- Validate uploaded file types and sizes.
- Prevent unauthorized access to private files.
- Store secrets outside the source code.
- Disable debug output in production.

### Production Checklist

- [ ] Verify production environment settings.
- [ ] Review role permissions and policies.
- [ ] Test OTP rate limiting.
- [ ] Verify token expiration and revocation.
- [ ] Configure HTTPS and CORS.
- [ ] Review upload and file access rules.
- [ ] Restrict database privileges.
- [ ] Ensure `.env` is not committed.
- [ ] Run automated tests.
- [ ] Review logging and error handling.

---

## 🧪 Testing

Laravel provides built-in tooling for automated application tests.

Run the test suite:

```bash
php artisan test
```

Or run PHPUnit directly:

```bash
./vendor/bin/phpunit
```

### Recommended Test Coverage

| Module | Test Cases |
|---|---|
| OTP | Expiration, attempt limits, resend throttling, reuse prevention |
| Authentication | Login, logout, unauthenticated access |
| Posts | Create, update, delete, retrieve |
| Authorization | Prevent editing other users' content |
| Comments | Moderation and parent-child relationships |
| Media | File validation and access control |
| Settings | Administrative permissions and secret protection |
| SEO | Metadata and sitemap generation |

Use a dedicated testing database for database-dependent tests. Tests must not modify production data.

---

## 📸 Screenshots

Add real project screenshots to the following paths.

### Admin Panel

![Shopbi Admin Panel](docs/screenshots/admin-panel.png)

### Analytics Dashboard

![Shopbi Analytics Dashboard](docs/screenshots/dashboard.png)

### Create Post Form

![Shopbi Create Post Form](docs/screenshots/create-post.png)

> Add the actual image files to these paths before publishing. Until then, GitHub may display broken image placeholders.

---

## 🗺️ Roadmap

The following items represent potential future improvements.

- [ ] Complete OpenAPI / Swagger documentation
- [ ] Publish an official Postman collection
- [ ] Expand unit and feature test coverage
- [ ] Introduce strategic caching for high-traffic endpoints
- [ ] Improve reporting and analytics
- [ ] Add error monitoring and service health checks
- [ ] Improve SMS queue handling and background processing
- [ ] Optimize image processing and delivery
- [ ] Add advanced content publishing workflows
- [ ] Implement shopping cart and order management if required
- [ ] Add automated CI testing
- [ ] Improve production deployment workflows

This roadmap is a development proposal, not a statement that these features are already implemented.

---

## 🤝 Contributing

Contributions, bug reports, and feature suggestions are welcome.

### Contribution Workflow

**1. Fork the repository.**

**2. Create a feature branch:**

```bash
git checkout -b feature/your-feature
```

**3. Implement your changes and run tests:**

```bash
php artisan test
```

**4. Commit your changes:**

```bash
git add .
git commit -m "feat: add your feature"
```

**5. Push the branch:**

```bash
git push origin feature/your-feature
```

**6. Open a Pull Request** explaining the purpose, implementation details, and test results.

### Development Guidelines

- Follow PSR-12 and Laravel conventions.
- Use descriptive names.
- Validate input through Form Requests.
- Keep classes focused and maintainable.
- Add tests for new functionality.
- Never commit secrets or credentials.
- Document API changes.
- Use clear and consistent commit messages.

### Bug Reports

Include the PHP and Laravel versions, reproduction steps, expected behavior, and actual behavior. Never publish secrets, access tokens, or personal data in public issues.

---

## 📄 License

Shopbi API is intended to be released under the MIT License.

The MIT License permits use, modification, distribution, and sublicensing subject to its terms.

To apply the license officially:

1. Create a `LICENSE` file in the repository root.
2. Add the complete standard MIT License text.
3. Replace the copyright holder with the appropriate name.

Suggested copyright header:

```text
MIT License

Copyright (c) 2026 [Your Name]
```

**Important:** The repository is not officially licensed under MIT until the license is actually added and the project owner has approved its use.

---

## 📬 Contact

| Item | Details |
|---|---|
| Project | Shopbi API |
| Developer | `[Your Name]` |
| Email | `[your-email@example.com]` |
| GitHub Repository | `[Your GitHub Repository URL]` |
| Bug Reports | GitHub Issues |

For feature requests, bug reports, or technical discussions, use the project's official repository.

---

## 🌟 Support the Project

If you find Shopbi API useful:

- ⭐ Star the repository on GitHub.
- 🐛 Report bugs.
- 💡 Suggest new features.
- 🤝 Contribute code and documentation.

---

<p align="center">
  <b>Built for scalable content platforms. Powered by Laravel.</b>
  <br><br>
  <b>Made with ❤️ in Iran</b>
</p>
