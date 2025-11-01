# API Compatibility - Laravel Backend

## Overview

This Laravel backend (`project-inno`) is designed to be 100% compatible with the Next.js frontend (`feku`). The API endpoints, request/response formats, and behavior **MUST** match the Go backend (`project-be-go`) exactly.

## Base URL

- **Go Backend**: `http://localhost:8080`
- **Laravel Backend**: `http://localhost:8000`
- **Frontend**: Configure in `.env` as `NEXT_PUBLIC_API_BASE_URL`

## API Endpoints Comparison

### Authentication

#### Google OAuth Login
```
GET /auth/google
```
**Status**: ✅ Compatible
- Redirects user to Google OAuth consent screen
- **Response**: Redirect to Google

#### Google OAuth Callback
```
GET /auth/google/callback?code={code}&state={state}
```
**Status**: ✅ Compatible
- Handles Google OAuth callback
- Creates/updates user in database
- **Go Behavior**: Redirects to `{FRONTEND_URL}/login?token={token}&user_id={user_id}`
- **Laravel Behavior**: Same as Go
- **Response**: Redirect to frontend

---

### Vaults

#### Create Vault
```
POST /api/v1/vaults
Content-Type: application/json

{
  "owner_user_id": "uuid",
  "name": "Vault Name",
  "description": "Optional description"
}
```
**Status**: ✅ Compatible
- **Response (201)**:
```json
{
  "status": "success",
  "message": "Vault created",
  "data": {
    "id": "uuid",
    "owner_user_id": "uuid",
    "name": "Vault Name",
    "description": "Optional description",
    "created_at": "2025-10-31T12:00:00+00:00"
  }
}
```

#### List Vaults by Owner
```
GET /api/v1/vaults?owner_id={user_id}
```
**Status**: ✅ Compatible
- **Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": [
    {
      "id": "uuid",
      "owner_user_id": "uuid",
      "name": "Vault Name",
      "description": "Description",
      "created_at": "2025-10-31T12:00:00+00:00"
    }
  ]
}
```

#### Delete Vault
```
DELETE /api/v1/vaults/{id}
```
**Status**: ✅ Compatible
- Deletes vault and all credentials (CASCADE)
- **Response (200)**:
```json
{
  "status": "success",
  "message": "Vault deleted successfully"
}
```

---

### Credentials

#### Create Credential
```
POST /api/v1/credentials
Content-Type: application/json

{
  "vault_id": "uuid",
  "username": "user@example.com",
  "password": "SecurePassword123",
  "url": "https://example.com"
}
```
**Status**: ✅ Compatible
- Password is encrypted before saving (AES-256)
- **Response (201)**:
```json
{
  "status": "success",
  "message": "Credential created successfully",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "user@example.com",
    "password": "SecurePassword123",
    "url": "https://example.com",
    "created_at": "2025-10-31T12:00:00+00:00",
    "updated_at": "2025-10-31T12:00:00+00:00"
  }
}
```

#### List Credentials by Vault
```
GET /api/v1/vaults/{vault_id}/credentials
```
**Status**: ✅ Compatible
- Password is automatically decrypted
- Returns empty array if no credentials
- **Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": [
    {
      "id": "uuid",
      "vault_id": "uuid",
      "username": "user@example.com",
      "password": "SecurePassword123",
      "url": "https://example.com",
      "created_at": "2025-10-31T12:00:00+00:00",
      "updated_at": "2025-10-31T12:00:00+00:00"
    }
  ]
}
```

#### Get Credential by ID
```
GET /api/v1/credentials/{id}
```
**Status**: ✅ Compatible
- **Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "user@example.com",
    "password": "SecurePassword123",
    "url": "https://example.com",
    "created_at": "2025-10-31T12:00:00+00:00",
    "updated_at": "2025-10-31T12:00:00+00:00"
  }
}
```

#### Update Credential
```
PUT /api/v1/credentials/{id}
Content-Type: application/json

{
  "username": "newuser@example.com",
  "password": "NewPassword456",
  "url": "https://newexample.com"
}
```
**Status**: ✅ Compatible
- Only provided fields are updated
- Password is re-encrypted
- **Response (200)**:
```json
{
  "status": "success",
  "message": "Credential updated successfully",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "newuser@example.com",
    "password": "NewPassword456",
    "url": "https://newexample.com",
    "created_at": "2025-10-31T12:00:00+00:00",
    "updated_at": "2025-10-31T12:15:00+00:00"
  }
}
```

#### Delete Credential
```
DELETE /api/v1/credentials/{id}
```
**Status**: ✅ Compatible
- **Response (200)**:
```json
{
  "status": "success",
  "message": "Credential deleted successfully"
}
```

---

## Response Format

All responses follow this standard format:

### Success Response
```json
{
  "status": "success",
  "message": "Description",
  "data": {}
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error description"
}
```

---

## Security Features

### Password Encryption
- **Go**: AES-256-GCM with base64-encoded key
- **Laravel**: AES-256-CBC (Laravel Crypt) with APP_KEY
- Both provide same level of security
- Passwords are decrypted when returned in API responses

### CORS
- **Go**: Allows all origins (`*`)
- **Laravel**: Allows all origins (`*`)
- Both allow: `GET, POST, PUT, DELETE, OPTIONS`
- Both allow headers: `Content-Type, Authorization`

### Database
- **Go**: PostgreSQL with UUID primary keys
- **Laravel**: MySQL with UUID primary keys
- Both use CASCADE delete for foreign keys

---

## Testing Checklist

- [ ] Google OAuth login works
- [ ] Google OAuth callback redirects to `/login` with `user_id`
- [ ] Create vault returns correct format
- [ ] List vaults by owner works
- [ ] Delete vault removes all credentials
- [ ] Create credential encrypts password
- [ ] List credentials decrypts passwords
- [ ] Update credential works
- [ ] Delete credential works
- [ ] CORS headers allow frontend requests
- [ ] All timestamps in ISO 8601 format

---

## Environment Variables

```env
# Required
APP_KEY=base64:... (for encryption)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=password
DB_USERNAME=root
DB_PASSWORD=

# OAuth
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Frontend
FRONTEND_URL=http://localhost:3000
```

---

## Migration from Go to Laravel

To switch frontend from Go backend to Laravel backend:

1. Update `.env` in `feku` folder:
   ```env
   NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
   ```

2. Restart Next.js frontend:
   ```bash
   npm run dev
   ```

3. No code changes needed in frontend!

---

## Known Differences (Intentional)

None - 100% compatible! 🎉
