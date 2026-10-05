# Laravel-Base

## Project Setup

- composer install (If you are running PHP version greater than 8.4, then use `composer update`)
- cp .env.example .env
- Create database in your local phpmyadmin
- Update the DB configurations
```
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=database that you have created
DB_USERNAME=root
DB_PASSWORD=
```
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
- after successfully login you will be redirected to a static dashboard page

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
| POST   | `/logout`     | Bearer | Revoke the token of the current device                   |

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
