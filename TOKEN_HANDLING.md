# Token Handling - Laravel vs Go Backend

## Important: NO Database Token Storage!

Laravel backend ini **TIDAK menyimpan token di database** (tidak pakai Sanctum `personal_access_tokens`).

Ini sesuai dengan Go backend yang hanya menggunakan **Google's Access Token** langsung.

## How It Works

### Go Backend Behavior
```go
// Redirect with Google's access token (temporary, expires in minutes)
vals := url.Values{}
vals.Set("token", token.AccessToken)  // Google's token
vals.Set("user_id", userID)
frontendURL := redirectBase + "?" + vals.Encode()
```

### Laravel Backend Behavior (NOW MATCHING!)
```php
// Redirect with Google's access token (temporary, expires in minutes)
$redirectUrl = $frontendUrl . 
    '?token=' . urlencode($googleToken) .  // Google's token
    '&user_id=' . urlencode($user->id);
```

## Key Changes Made

### 1. Removed Sanctum Token Creation
**Before** (WRONG):
```php
$token = $user->createToken('api-token')->plainTextToken;  // ❌ Creates DB token
```

**After** (CORRECT):
```php
$googleToken = $result['token'];  // ✅ Uses Google's token
```

### 2. Updated GoogleAuthService Return Type
**Before**:
```php
public function handleCallback(string $code): ?User
{
    // ...
    return $user;  // Only returns user
}
```

**After**:
```php
public function handleCallback(string $code): ?array
{
    // ...
    return [
        'user' => $user,
        'token' => $token['access_token'],  // Google's access token
    ];
}
```

### 3. Removed HasApiTokens from User Model
**Before**:
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids;  // ❌
```

**After**:
```php
class User extends Authenticatable
{
    use HasFactory, HasUuids;  // ✅ No HasApiTokens
```

## Database Tables

### ✅ Required Tables
- `users` - Store user info
- `vaults` - Store vaults
- `credentials` - Store encrypted credentials

### ❌ NOT Required
- `personal_access_tokens` - We DON'T store tokens!

## Token Lifetime

| Backend | Token Source | Lifetime | Storage |
|---------|--------------|----------|---------|
| Go | Google OAuth | ~1 hour | None (in-memory) |
| Laravel | Google OAuth | ~1 hour | None (in-memory) |

Both backends use **Google's access token** which expires after ~1 hour. Token is NOT stored in database.

## Frontend Handling

Frontend receives token in URL:
```
http://localhost:3000/login?token=ya29.a0...&user_id=uuid
```

Frontend stores `user_id` in localStorage (NOT token, because it expires).

For subsequent API requests, frontend sends `user_id` in query/body:
```javascript
// Create vault
fetch(`${API_BASE}/api/v1/vaults`, {
  method: 'POST',
  body: JSON.stringify({
    owner_user_id: localStorage.getItem('user_id'),
    name: 'My Vault'
  })
})
```

## Why No Token Storage?

1. **Simplicity** - No token management needed
2. **Security** - No token can be stolen from database
3. **Compatibility** - Matches Go backend exactly
4. **Stateless** - Each request is independent

## API Authentication

Current implementation: **NO AUTHENTICATION REQUIRED**

All endpoints are public (for demo purposes). In production, you should add:
- Token validation
- User authorization
- Rate limiting

But this would require changing BOTH Go and Laravel backends together.

## Migration Note

If you previously ran Sanctum migrations and have `personal_access_tokens` table:

**You can ignore it!** It's not used and won't cause problems.

If you want to clean it up:
```bash
# Optional - remove unused table
DROP TABLE personal_access_tokens;
```

---

**Date**: 2025-11-01
**Status**: ✅ Matching Go Backend Behavior
