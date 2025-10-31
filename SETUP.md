# Credential Vault Laravel Setup

## Requirements
- PHP 8.2+
- PostgreSQL 12+
- Composer

## Installation Steps

### 1. Clone Repository
\`\`\`bash
git clone <repo-url>
cd project-be-laravel
\`\`\`

### 2. Install Dependencies
\`\`\`bash
composer install
\`\`\`

### 3. Setup Environment
\`\`\`bash
cp .env.example .env
php artisan key:generate
\`\`\`

### 4. Configure Database
Edit `.env`:
\`\`\`
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=credential_vault
DB_USERNAME=postgres
DB_PASSWORD=admin
\`\`\`

### 5. Configure Google OAuth
1. Go to Google Cloud Console: https://console.cloud.google.com/
2. Create a new project or select existing one
3. Enable Google+ API
4. Create OAuth 2.0 credentials (Web application)
5. Add Authorized redirect URIs:
   - http://localhost:8000/auth/google/callback (development)
   - https://yourdomain.com/auth/google/callback (production)
6. Copy Client ID and Client Secret to `.env`:

\`\`\`
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
\`\`\`

### 6. Run Migrations
\`\`\`bash
php artisan migrate
\`\`\`

### 7. Generate Encryption Key (if needed)
\`\`\`bash
php artisan key:generate
\`\`\`

### 8. Start Server
\`\`\`bash
php artisan serve
\`\`\`

Server akan berjalan di: http://localhost:8000

## API Endpoints

### Auth
- \`GET /api/v1/auth/google\` - Start Google OAuth login
- \`GET /api/v1/auth/google/callback\` - Google callback
- \`POST /api/v1/auth/verify-token\` - Verify Google ID Token (SPA)
- \`POST /api/v1/auth/logout\` - Logout

### Vaults
- \`GET /api/v1/vaults?owner_id=user-id\` - List vaults
- \`POST /api/v1/vaults\` - Create vault
- \`DELETE /api/v1/vaults/:id\` - Delete vault

### Credentials
- \`GET /api/v1/vaults/:vault_id/credentials\` - List credentials
- \`POST /api/v1/credentials\` - Create credential
- \`GET /api/v1/credentials/:id\` - Get credential
- \`PUT /api/v1/credentials/:id\` - Update credential
- \`DELETE /api/v1/credentials/:id\` - Delete credential

## Features

✅ Google OAuth 2 integration  
✅ API Token Authentication (Sanctum)  
✅ AES-256 Password Encryption  
✅ PostgreSQL Database  
✅ Repository Pattern  
✅ CORS Support  

## Notes

- Passwords are encrypted before saving to database
- All timestamps are in ISO 8601 format
- Use \`Authorization: Bearer <token>\` header for protected routes
- Frontend URL: set in \`.env\` as \`FRONTEND_URL\`

## Troubleshooting

### Migration fails
Check PostgreSQL is running and credentials in .env are correct

### Google Auth not working
- Verify Client ID and Secret in .env
- Check OAuth redirect URIs in Google Cloud Console
- Make sure FRONTEND_URL is correct

### Password decryption fails
Ensure \`APP_KEY\` is set correctly in .env

\`\`\`