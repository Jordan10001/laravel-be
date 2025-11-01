# Quick Reference - Backend Switching

## Ganti dari Go ke Laravel

### 1. Update Frontend Environment
```bash
cd feku
```

Edit `.env`:
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

### 2. Restart Frontend
```bash
npm run dev
```

### 3. Start Laravel Backend
```bash
cd project-inno
php artisan serve
```

**SELESAI!** Frontend sekarang menggunakan Laravel backend.

---

## Ganti dari Laravel ke Go

### 1. Update Frontend Environment
```bash
cd feku
```

Edit `.env`:
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8080
```

### 2. Restart Frontend
```bash
npm run dev
```

### 3. Start Go Backend
```bash
cd project-be-go
go run cmd/main.go
```

**SELESAI!** Frontend sekarang menggunakan Go backend.

---

## Command Cheatsheet

### Laravel Commands
```bash
# Start server
php artisan serve

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh database (WARNING: deletes all data)
php artisan migrate:fresh

# Generate APP_KEY
php artisan key:generate

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check routes
php artisan route:list
```

### MySQL Commands
```bash
# Login to MySQL
mysql -u root -p

# Create database
CREATE DATABASE password;

# Show databases
SHOW DATABASES;

# Use database
USE password;

# Show tables
SHOW TABLES;

# Check users
SELECT * FROM users;

# Check vaults
SELECT * FROM vaults;

# Check credentials
SELECT id, vault_id, username, url, created_at FROM credentials;
```

### Frontend Commands
```bash
# Install dependencies
npm install

# Start dev server
npm run dev

# Build for production
npm run build

# Start production server
npm start
```

### Testing with cURL

#### Create Vault
```bash
curl -X POST http://localhost:8000/api/v1/vaults \
  -H "Content-Type: application/json" \
  -d '{
    "owner_user_id": "YOUR_USER_ID",
    "name": "My Vault",
    "description": "Test vault"
  }'
```

#### List Vaults
```bash
curl "http://localhost:8000/api/v1/vaults?owner_id=YOUR_USER_ID"
```

#### Create Credential
```bash
curl -X POST http://localhost:8000/api/v1/credentials \
  -H "Content-Type: application/json" \
  -d '{
    "vault_id": "VAULT_ID",
    "username": "test@example.com",
    "password": "SecurePassword123",
    "url": "https://example.com"
  }'
```

#### List Credentials
```bash
curl "http://localhost:8000/api/v1/vaults/VAULT_ID/credentials"
```

---

## URL Reference

| Service | URL | Port |
|---------|-----|------|
| Frontend | http://localhost:3000 | 3000 |
| Laravel Backend | http://localhost:8000 | 8000 |
| Go Backend | http://localhost:8080 | 8080 |
| MySQL | localhost | 3306 |

---

## Environment Files

### feku/.env
```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000  # Change to 8080 for Go
```

### project-inno/.env
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_DATABASE=password
FRONTEND_URL=http://localhost:3000
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
```

### project-be-go/.env
```env
APP_PORT=8080
DATABASE_URL=postgres://...
FRONTEND_URL=http://localhost:3000
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
```

---

## Troubleshooting Quick Fixes

### Frontend can't connect to backend
```bash
# Check if backend is running
curl http://localhost:8000/api/v1/vaults

# Check CORS
curl -H "Origin: http://localhost:3000" http://localhost:8000/api/v1/vaults
```

### Database error in Laravel
```bash
# Check MySQL is running
mysql -u root -p

# Run migrations again
php artisan migrate
```

### Google OAuth not working
- Check `GOOGLE_CLIENT_ID` in `.env`
- Check `GOOGLE_REDIRECT_URI` matches Google Console
- Laravel: `http://localhost:8000/auth/google/callback`
- Go: `http://localhost:8080/auth/google/callback`

### Port already in use
```bash
# Laravel - use different port
php artisan serve --port=8001

# Frontend - use different port
PORT=3001 npm run dev
```

---

## File Structure Quick Reference

```
hem/
├── feku/                    # Next.js Frontend
│   ├── .env                # API_BASE_URL config
│   ├── app/                # Pages
│   ├── components/         # React components
│   └── lib/api/           # API client
│
├── project-be-go/          # Go Backend
│   ├── .env               # Go config
│   ├── app/               # Handlers, services
│   └── cmd/main.go        # Entry point
│
└── project-inno/           # Laravel Backend
    ├── .env               # Laravel config
    ├── app/
    │   ├── Http/Controllers/Api/
    │   ├── Models/
    │   ├── Repositories/
    │   └── Services/
    ├── routes/
    │   ├── api.php        # API routes
    │   └── web.php        # Web routes
    └── database/migrations/
```

---

## Important Notes

⚠️ **JANGAN ubah file di `feku/` atau `project-be-go/`** - mereka sudah working!

✅ **BOLEH ubah file di `project-inno/`** - ini yang sedang dikembangkan

🔑 **API response HARUS identik** antara Go dan Laravel

📝 **Timestamps HARUS ISO 8601** format (e.g., `2025-11-01T12:00:00+00:00`)

🔐 **Password HARUS encrypted** di database, decrypted di response

---

**Created**: 2025-11-01
