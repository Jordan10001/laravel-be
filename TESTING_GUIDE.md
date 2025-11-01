# Testing Guide - Laravel Backend Compatibility

## Pre-requisites

- [ ] MySQL server running (XAMPP)
- [ ] Database `password` created
- [ ] Migrations run successfully (`php artisan migrate`)
- [ ] `.env` configured with correct values
- [ ] Laravel server running (`php artisan serve`)
- [ ] Frontend `.env` updated with `NEXT_PUBLIC_API_BASE_URL=http://localhost:8000`

## Test Scenarios

### 1. Authentication Flow

#### Test 1.1: Google OAuth Login
```
1. Open browser: http://localhost:8000/auth/google
2. Expected: Redirect to Google login page
3. Login with Google account
4. Expected: Redirect to http://localhost:3000/login?token=...&user_id=...
5. Check: user_id is a valid UUID
6. Check: User exists in database (users table)
```

**Database Check**:
```sql
SELECT * FROM users WHERE email = 'your-email@gmail.com';
```

### 2. Vault Operations

#### Test 2.1: Create Vault
```bash
POST http://localhost:8000/api/v1/vaults
Content-Type: application/json

{
  "owner_user_id": "YOUR_USER_ID_FROM_LOGIN",
  "name": "Test Vault",
  "description": "My first vault"
}
```

**Expected Response (201)**:
```json
{
  "status": "success",
  "message": "Vault created",
  "data": {
    "id": "uuid",
    "owner_user_id": "uuid",
    "name": "Test Vault",
    "description": "My first vault",
    "created_at": "2025-11-01T..."
  }
}
```

**Checklist**:
- [ ] Status code is 201
- [ ] Response has `status: "success"`
- [ ] `id` is a valid UUID
- [ ] `created_at` is in ISO 8601 format
- [ ] Vault appears in database

#### Test 2.2: List Vaults
```bash
GET http://localhost:8000/api/v1/vaults?owner_id=YOUR_USER_ID
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": [
    {
      "id": "uuid",
      "owner_user_id": "uuid",
      "name": "Test Vault",
      "description": "My first vault",
      "created_at": "2025-11-01T..."
    }
  ]
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] Returns array (even if empty)
- [ ] All vaults belong to correct owner
- [ ] Timestamps in ISO format

#### Test 2.3: Delete Vault
```bash
DELETE http://localhost:8000/api/v1/vaults/VAULT_ID
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "Vault deleted successfully"
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] Vault removed from database
- [ ] All credentials in vault also deleted (CASCADE)

### 3. Credential Operations

#### Test 3.1: Create Credential
```bash
POST http://localhost:8000/api/v1/credentials
Content-Type: application/json

{
  "vault_id": "VAULT_ID",
  "username": "test@example.com",
  "password": "MySecurePassword123",
  "url": "https://example.com"
}
```

**Expected Response (201)**:
```json
{
  "status": "success",
  "message": "Credential created successfully",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "test@example.com",
    "password": "MySecurePassword123",
    "url": "https://example.com",
    "created_at": "2025-11-01T...",
    "updated_at": "2025-11-01T..."
  }
}
```

**Checklist**:
- [ ] Status code is 201
- [ ] Password in response is PLAINTEXT (not encrypted)
- [ ] Password in database is ENCRYPTED
- [ ] Both `created_at` and `updated_at` present
- [ ] Timestamps in ISO format

**Database Check**:
```sql
SELECT * FROM credentials WHERE username = 'test@example.com';
-- Check: password_encrypted should NOT be plaintext
```

#### Test 3.2: List Credentials
```bash
GET http://localhost:8000/api/v1/vaults/VAULT_ID/credentials
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": [
    {
      "id": "uuid",
      "vault_id": "uuid",
      "username": "test@example.com",
      "password": "MySecurePassword123",
      "url": "https://example.com",
      "created_at": "2025-11-01T...",
      "updated_at": "2025-11-01T..."
    }
  ]
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] Passwords are DECRYPTED (plaintext in response)
- [ ] Empty array if no credentials
- [ ] All credentials belong to correct vault

#### Test 3.3: Get Single Credential
```bash
GET http://localhost:8000/api/v1/credentials/CREDENTIAL_ID
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "ok",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "test@example.com",
    "password": "MySecurePassword123",
    "url": "https://example.com",
    "created_at": "2025-11-01T...",
    "updated_at": "2025-11-01T..."
  }
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] Password is decrypted
- [ ] All fields present

#### Test 3.4: Update Credential
```bash
PUT http://localhost:8000/api/v1/credentials/CREDENTIAL_ID
Content-Type: application/json

{
  "username": "updated@example.com",
  "password": "NewPassword456",
  "url": "https://updated.com"
}
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "Credential updated successfully",
  "data": {
    "id": "uuid",
    "vault_id": "uuid",
    "username": "updated@example.com",
    "password": "NewPassword456",
    "url": "https://updated.com",
    "created_at": "2025-11-01T...",
    "updated_at": "2025-11-01T..." // Should be later than created_at
  }
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] All fields updated
- [ ] `updated_at` changed
- [ ] Password re-encrypted in database

#### Test 3.5: Delete Credential
```bash
DELETE http://localhost:8000/api/v1/credentials/CREDENTIAL_ID
```

**Expected Response (200)**:
```json
{
  "status": "success",
  "message": "Credential deleted successfully"
}
```

**Checklist**:
- [ ] Status code is 200
- [ ] Credential removed from database

### 4. CORS Testing

#### Test 4.1: Preflight Request
```bash
OPTIONS http://localhost:8000/api/v1/vaults
Origin: http://localhost:3000
```

**Expected Headers**:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

**Checklist**:
- [ ] Status code is 200
- [ ] All CORS headers present
- [ ] Origin `*` (allows all)

#### Test 4.2: Actual Request from Frontend
```bash
GET http://localhost:8000/api/v1/vaults?owner_id=...
Origin: http://localhost:3000
```

**Checklist**:
- [ ] No CORS errors in browser console
- [ ] Response received successfully

### 5. Frontend Integration Test

#### Test 5.1: Complete User Flow
1. [ ] Open frontend: http://localhost:3000
2. [ ] Click "Login with Google"
3. [ ] Redirected to Google, login
4. [ ] Redirected back to http://localhost:3000/login
5. [ ] User ID saved in localStorage
6. [ ] Click "Go to Vaults"
7. [ ] Create new vault (name + description)
8. [ ] Vault appears in list
9. [ ] Click on vault to view details
10. [ ] Add new credential (username, password, URL)
11. [ ] Credential appears in list
12. [ ] Click "Show" to view password
13. [ ] Password matches what was entered
14. [ ] Edit credential
15. [ ] Changes saved successfully
16. [ ] Delete credential
17. [ ] Credential removed from list
18. [ ] Go back to vault list
19. [ ] Delete vault
20. [ ] Vault removed from list

### 6. Error Handling

#### Test 6.1: Invalid Vault ID
```bash
GET http://localhost:8000/api/v1/vaults/invalid-uuid/credentials
```

**Expected**: Error response with appropriate message

#### Test 6.2: Missing Required Fields
```bash
POST http://localhost:8000/api/v1/vaults
Content-Type: application/json

{
  "owner_user_id": "uuid"
  // Missing "name"
}
```

**Expected**: 400 Bad Request with validation errors

### 7. Performance Tests

- [ ] List 100+ vaults: Response time < 1s
- [ ] List 100+ credentials: Response time < 1s
- [ ] Create vault: Response time < 500ms
- [ ] Encryption/decryption: No noticeable delay

## Common Issues & Solutions

### Issue: CORS error
**Solution**: Check HandleCors middleware is active and headers are set correctly

### Issue: Password not decrypting
**Solution**: Check APP_KEY is set and hasn't changed since encryption

### Issue: Google OAuth redirect fails
**Solution**: 
- Check GOOGLE_REDIRECT_URI in .env matches Google Console
- Check FRONTEND_URL is correct

### Issue: Database connection failed
**Solution**:
- Check MySQL is running
- Check database `password` exists
- Check credentials in .env

## Final Compatibility Check

Compare responses between Go and Laravel backends:

1. **Start both backends**:
   - Go: `http://localhost:8080`
   - Laravel: `http://localhost:8000`

2. **Make same request to both**:
   ```bash
   # Go
   GET http://localhost:8080/api/v1/vaults?owner_id=...
   
   # Laravel
   GET http://localhost:8000/api/v1/vaults?owner_id=...
   ```

3. **Compare responses**:
   - [ ] Same structure
   - [ ] Same field names
   - [ ] Same data types
   - [ ] Same status codes
   - [ ] Same error messages

4. **Switch frontend backend**:
   ```env
   # Test with Go
   NEXT_PUBLIC_API_BASE_URL=http://localhost:8080
   
   # Test with Laravel
   NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
   ```

5. **Verify**:
   - [ ] Frontend works identically with both backends
   - [ ] No code changes needed
   - [ ] All features work the same

## Success Criteria

✅ All tests pass
✅ Frontend works with Laravel backend without modifications
✅ API responses match Go backend exactly
✅ CORS allows frontend requests
✅ Passwords encrypted/decrypted correctly
✅ Google OAuth works
✅ Database operations successful

---

**Last Updated**: 2025-11-01
