<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
## API Documentation

### Overview
This API provides digital banking services including user authentication, account management, and transaction processing. Built with Laravel, it follows RESTful principles and uses JSON for data exchange.

### Base URL
```
http://localhost/api
```

### Authentication
The API uses OAuth 2.0 authentication via Laravel Passport.

1. **Obtain a token**: Use the `/login` endpoint
2. **Use the token**: Include in request headers as `Authorization: Bearer {token}`

### User Management Endpoints

#### Register a new user
```
POST /register
```
**Request Body**:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "cpf": "12345678900",
    "password": "secure_password"
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "customer_account": {
            "account_number": "123456789",
            "balance": 0
        }
    },
    "token": "access_token_here"
}
```

#### Login
```
POST /login
```
**Request Body**:
```json
{
    "email": "john@example.com",
    "password": "secure_password"
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "token": "access_token_here"
}
```

#### Logout
```
POST /logout
```
**Headers**:
```
Authorization: Bearer {token}
```
**Response** (200 OK):
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

#### Forgot Password
```
POST /forgot-password
```
**Request Body**:
```json
{
    "email": "john@example.com"
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "message": "Password reset link sent to your email"
}
```

#### Change Password
```
PUT /password-change
```
**Headers**:
```
Authorization: Bearer {token}
```
**Request Body**:
```json
{
    "current_password": "old_password",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "message": "Password changed successfully"
}
```

### Account Management Endpoints

#### Add Funds
```
POST /account/add-funds
```
**Headers**:
```
Authorization: Bearer {token}
```
**Request Body**:
```json
{
    "amount": 100.00
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "account_number": "123456789",
        "balance": 100.00,
        "transaction_id": "txn_123456"
    }
}
```

#### Withdraw Funds
```
POST /account/withdraw
```
**Headers**:
```
Authorization: Bearer {token}
```
**Request Body**:
```json
{
    "amount": 50.00
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "account_number": "123456789",
        "balance": 50.00,
        "transaction_id": "txn_789012"
    }
}
```

#### Transfer Funds
```
POST /account/transfer
```
**Headers**:
```
Authorization: Bearer {token}
```
**Request Body**:
```json
{
    "destination_account": "987654321",
    "amount": 25.00
}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "source_account": "123456789",
        "destination_account": "987654321",
        "amount": 25.00,
        "balance": 25.00,
        "transaction_id": "txn_345678"
    }
}
```

#### Get Balance
```
GET /account/balance
```
**Headers**:
```
Authorization: Bearer {token}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "account_number": "123456789",
        "balance": 25.00
    }
}
```

#### Get Account Statement
```
GET /account/statement
```
**Headers**:
```
Authorization: Bearer {token}
```
**Query Parameters**:
- `start_date` (optional): Start date (YYYY-MM-DD)
- `end_date` (optional): End date (YYYY-MM-DD)

**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "account_number": "123456789",
        "balance": 25.00,
        "transactions": [
            {
                "id": "txn_123456",
                "type": "deposit",
                "amount": 100.00,
                "date": "2023-07-01 10:30:00"
            },
            {
                "id": "txn_789012",
                "type": "withdrawal",
                "amount": -50.00,
                "date": "2023-07-02 14:15:00"
            },
            {
                "id": "txn_345678",
                "type": "transfer",
                "amount": -25.00,
                "destination": "987654321",
                "date": "2023-07-03 09:45:00"
            }
        ]
    }
}
```

#### Get Account Details
```
GET /account
```
**Headers**:
```
Authorization: Bearer {token}
```
**Response** (200 OK):
```json
{
    "success": true,
    "data": {
        "account_number": "123456789",
        "balance": 25.00,
        "owner": {
            "name": "John Doe",
            "cpf": "12345678900"
        },
        "created_at": "2023-07-01"
    }
}
```

### Error Handling

All API errors return with appropriate HTTP status codes and a consistent JSON structure:

```json
{
    "success": false,
    "error": {
        "code": "error_code",
        "message": "Description of the error"
    }
}
```

#### Common Status Codes
- `400` - Bad Request: Invalid input data
- `401` - Unauthorized: Authentication required
- `403` - Forbidden: Insufficient permissions
- `404` - Not Found: Resource not found
- `422` - Unprocessable Entity: Validation errors
- `500` - Internal Server Error: Server-side issue

### Interactive Documentation

This API provides Swagger/OpenAPI documentation at:
```
/api/documentation
```

Use this interactive interface to explore endpoints, request parameters, and response schemas.

### Rate Limiting

To ensure service stability, API requests are limited to:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users