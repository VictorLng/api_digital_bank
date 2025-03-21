## API Documentation
### Commands to Run the aplication
docker-compose up --build

**To run php and composer use alias**
alias dphp="docker exec -it php-api_digital_bank php"
alias dcomposer="docker exec -it php-api_digital_bank composer"

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
    "password": "secure_password",
    "password_confirmation": "secure_password",
    "phone": "123456789",
    "birth_date": "1990-01-01",
    "bank_account_type": "checking",
    "account_role": "USER"
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
        "cpf": "12345678900",
        "phone": "123456789",
        "birth_date": "1990-01-01",
        "customer_account": {
            "account_number": "123456789",
            "agency": "0001",
            "type_account": "checking",
            "balance": 0.00,
            "status": "active"
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
        "email": "john@example.com",
        "cpf": "12345678900",
        "phone": "123456789",
        "birth_date": "1990-01-01",
        "token": "access_token_here"
        "customer_account": {
            "account_number": "123456789",
            "agency": "0001",
            "type_account": "checking",
            "balance": 0.00,
            "status": "active"
        }
    },
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
            "cpf": "12345678900",
            "email": "john@example.com",
            "phone": "123456789",
            "birth_date": "1990-01-01"
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

### Rate Limiting

To ensure service stability, API requests are limited to:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users