# Laravel-Base

## Project Setup

- composer install (If you are running PHP version greater than 8.4, then use `composer update`)
- cp .env.example .env
- Create database in your local phpmyadmin
- Update the DB configurations

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=database that you have created
DB_USERNAME=root
DB_PASSWORD=
```

- Configure the file storage disk in `.env`

```env
# Local storage (default)
FILESYSTEM_DISK=public

# Amazon S3
# FILESYSTEM_DISK=s3
```

- `public`: files are stored locally (run `php artisan storage:link`).
- `s3`: files are stored in your S3 bucket. The AWS credentials (access key, secret, region and bucket) are **not** read from `.env`. Add them in the `aws` settings from the admin panel (stored in the `settings` table).
- After changing the disk, run `php artisan config:clear`.
- php artisan key:generate
- php artisan storage:link
- php artisan migrate --seed
- npm install
- npm run build
- php artisan serve
- You can access your application using http://127.0.0.1:8000 url

## Application Start Process

- Login page for admin http://{APP_URL}/admin/login
- Admin Details ( laravel_base@mailinator.com / Admin@123 )
- Login page for user http://{APP_URL}/login
- After successfully login you will be redirected to a dashboard page

## Application API Start Process

- Developer for test api url http://{APP_URL}/swagger/index.html

## API (v1) – Authentication

Base URL: `http://{APP_URL}/api/v1/`

The API uses **Laravel Sanctum** bearer tokens and **OTP based login** (email or phone). There is no password login for API users.
The complete request/response documentation is available in `api-docs.json` (Swagger 2.0).

### Endpoints

| Method | Endpoint      | Auth   | Description                                              |
|--------|---------------|--------|----------------------------------------------------------|
| POST   | `/register`   | Public | Register a new user and save the device (no token)       |
| POST   | `/login`      | Public | Check the account and send OTP (no token)                |
| POST   | `/send-otp`   | Public | Send or **resend** OTP                                   |
| POST   | `/verify-otp` | Public | Verify OTP, save device and get the access token         |

### Login flow

```
register ──► login ──► verify-otp ──► (use token) ──► logout
                │            ▲
                └─ send-otp ─┘   (resend OTP if it expired / attempts are over)
```

1. `POST /register` – creates the account. The user must login afterwards.
2. `POST /login` – validates the account (`login_type` = `email` or `phone`) and sends an OTP.
3. `POST /verify-otp` – on a correct OTP the API returns `token`, `token_type` and `user`.
4. Send the token with every protected request: `Authorization: Bearer {token}`
5. `POST /send-otp` – use it to resend the OTP (a new OTP is generated and the wrong-attempt counter is reset).

### Response format

All API responses use the same structure:

```json
{
    "status": true,
    "message": "Login successful!",
    "data": {}
}
```

Validation errors (HTTP 422) return the first error in `message` and all errors in `data.errors`.

### OTP rules

| Rule                  | Value                                                                 |
|-----------------------|-----------------------------------------------------------------------|
| OTP validity          | `max_time` from the `otp` settings (default **90 seconds**)           |
| Wrong attempts        | **3** attempts per OTP, then the OTP is blocked until it is resent    |
| Send / resend limit   | **5** requests per **10 minutes** per account + IP (HTTP 429)         |
| Delivery              | Email → notification, Phone → Twilio SMS                              |
| Admin users           | Cannot login through OTP (use the admin login page)                   |
| Storage               | OTP is stored **hashed** in cache (not in the database)               |

OTP error responses include `data.reason` so the app does not have to depend on message text:

| Situation                 | HTTP | Message example                                               | `data.reason`      |
|---------------------------|------|---------------------------------------------------------------|--------------------|
| OTP expired               | 422  | `OTP has expired. Please request a new OTP.`                  | `otp_expired`      |
| Incorrect OTP             | 422  | `Incorrect OTP. 2 attempts remaining.`                        | `otp_invalid`      |
| Maximum attempts reached  | 429  | `Incorrect OTP. Maximum attempts reached. Please resend OTP.` | `otp_max_attempts` |

`otp_invalid` and `otp_max_attempts` responses also include `data.remaining_attempts`.

## API (v1) – Master

Master / dropdown data APIs. They are public (no token required) and use the same `{status, message, data}` response format.

| Method | Endpoint               | Description                                            |
|--------|------------------------|--------------------------------------------------------|
| GET    | `/phone-country-code`  | Country list with phone codes (`config/countries.php`) |

## API (v1) – Users

All endpoints below require `Authorization: Bearer {token}`.

| Method | Endpoint           | Tag     | Description                                                                        |
|--------|--------------------|---------|------------------------------------------------------------------------------------|
| GET    | `/users/{id}`      | Users   | Get user details                                                                   |
| PUT    | `/users/{id}`      | Users   | Update name, country iso, country code, phone number and profile image (FORM DATA) |

## API (v1) – Account

All endpoints below require `Authorization: Bearer {token}`.

| Method | Endpoint           | Tag     | Description                                                   |
|--------|--------------------|---------|---------------------------------------------------------------|
| POST   | `/logout`          | Account | Revoke the current device token                               |
