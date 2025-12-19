# Makan API

A production-quality RESTful API for a listings-and-bookings marketplace. This project provides a robust backend where Hosts can list properties and Guests can search, book, and review them.

## Business Scenario
The **Makan** marketplace serves as a centralized platform for property rentals. It addresses the following core requirements:
- **Property Management**: Hosts can list properties with detailed metadata, including amenities and media attachments.
- **Booking Lifecycle**: Guests can discover properties via advanced filtering (city, price, availability) and manage the full booking lifecycle from pending to completion.
- **Trust & Quality**: A polymorphic review system allows guests to rate both listings and hosts, ensuring marketplace transparency.
- **Security & Roles**: Fine-grained access control ensures that sensitive operations (like amenity management or booking moderation) are restricted to authorized roles (Admin/Support).

### Core Resources
1. **User**: Managed via Laravel Sanctum with roles (Guest, Host, Admin, Support).
2. **Listing**: The central entity for properties, supporting advanced search and Redis caching.
3. **Booking**: Manages the stay duration, pricing logic (via Service Layer), and status transitions.
4. **Amenity**: Categorize listings for improved discoverability.
5. **Review**: Polymorphic ratings for listings and users.
6. **Media**: Polymorphic file storage for property images/videos.

---

## Tech Stack & Tools

| Tool | Reason for Inclusion |
| :--- | :--- |
| **Laravel 12** | Chosen for its robust ecosystem, expressive syntax, and built-in support for modern API development patterns. |
| **PostgreSQL 18** | Used for reliable, structured data storage with native support for advanced indexing and UUID types. |
| **Redis 7** | Utilized for both high-performance caching of listing searches and as a reliable driver for the asynchronous queue system. |
| **Laravel Sanctum** | Provides a lightweight but secure token-based authentication system suitable for SPAs and mobile apps. |
| **Spatie Permissions** | The industry standard for managing roles and permissions in Laravel, providing clean, trait-based authorization. |
| **Service Layer** | Business logic (e.g., pricing/availability) is decoupled from controllers into a **BookingService** to improve testability and DRY principles. |

---

## Installation & Setup

### 1. Clone & Dependencies
```bash
git clone https://github.com/zizouhuweidi/makan.git
cd makan
composer install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```
Edit your `.env` file to configure:
- **DB_CONNECTION**: `pgsql` (host/port/database/credentials)
- **QUEUE_CONNECTION**: `redis`
- **CACHE_STORE**: `redis`

### 3. Local Infrastructure (Docker)
Ensure Docker is running, then start the database and cache services:
```bash
docker-compose up -d
```

---

## Database Operations

### Migrations & Seeders
This project uses **UUIDs** across all models. To initialize the database with roles, safe permissions, and demo data:

```bash
# Run fresh migrations and seed all data
php artisan migrate:fresh --seed
```

**Seed Results:**
- **Roles**: `admin`, `host`, `guest`, `support`.
- **Demo Users**: 
    - `admin@makan.com` (password: `password`)
    - `host@makan.com` (password: `password`)
    - `guest@makan.com` (password: `password`)

---

## Background Jobs
Start the queue worker to process booking notifications:
```bash
php artisan queue:work
```


## Usage & API Testing

### Postman Collection
For a comprehensive overview of all endpoints, including example payloads and automated authentication scripts, use the provided collection:

👉 **[api/makan_api_collection.json](api/makan_api_collection.json)**

**How to use:**
1. Import the JSON file into Postman.
2. Run the **Login** request once; a test script will automatically save the Bearer token to your environment.
3. All subsequent protected requests will use this token.

---

