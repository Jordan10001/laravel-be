# Laravel Backend Setup - Credential Vault

## 📋 Prerequisites

- PHP 8.2 
- Composer
- MySQL 8.0
- XAMPP (recommended) 

## 🚀 Installation

### 1. Install Dependencies

```bash
cd project-inno
composer install
```

### 2. Configure Environment

Copy `.env.example` ke `.env` atau edit `.env` yang sudah ada:

```env
APP_NAME=CredentialVault
APP_ENV=local
APP_KEY=base64:9/ktZELiz2t8Iml7rG/4yd/pzT4C+D/nDCW/4NQSnaA=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=password
DB_USERNAME=root
DB_PASSWORD=

FRONTEND_URL=http://localhost:3000

GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### 3. Generate Application Key (jika belum ada)

```bash
php artisan key:generate
```

### 4. Create Database

Buat database MySQL bernama `password`:

```sql
CREATE DATABASE password CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run Migrations

```bash
php artisan migrate
```
- `users` - User accounts (Google OAuth)
- `vaults` - Password vaults
- `credentials` - Stored credentials (encrypted)

### 6. Start Server

```bash
php artisan serve
```
`http://localhost:8000`

## 🔧 Configure Frontend

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

```bash
cd feku
npm run dev
```

## 📡 API Endpoints

### Auth
- `GET /auth/google` - Login with Google
- `GET /auth/google/callback` - OAuth callback

### Vaults
- `POST /api/v1/vaults` - Create vault
- `GET /api/v1/vaults?owner_id={id}` - List vaults
- `DELETE /api/v1/vaults/{id}` - Delete vault

### Credentials
- `POST /api/v1/credentials` - Create credential
- `GET /api/v1/vaults/{vault_id}/credentials` - List credentials
- `GET /api/v1/credentials/{id}` - Get credential
- `PUT /api/v1/credentials/{id}` - Update credential
- `DELETE /api/v1/credentials/{id}` - Delete credential

## 🔐 Security

### Password Encryption
- Menggunakan Laravel's built-in encryption (AES-256-CBC)
- Encryption key dari `APP_KEY` di `.env`
- Password di-encrypt sebelum disimpan ke database
- Password di-decrypt otomatis saat dikirim ke frontend

### Google OAuth
- Menggunakan Google OAuth 2.0
- Credentials harus didaftarkan di Google Cloud Console
- Redirect URI: `http://localhost:8000/auth/google/callback`

### CORS
- Mengizinkan semua origins (`*`)
- Mendukung methods: GET, POST, PUT, DELETE, OPTIONS
- Headers: Content-Type, Authorization

## 🗄️ Database Schema

### Users Table
```sql
- id (UUID, PK)
- email (string, unique)
- name (string)
- picture_url (string)
- google_id (string, unique)
- provider_id (string)
- provider_name (string)
- created_at, updated_at
```

### Vaults Table
```sql
- id (UUID, PK)
- owner_user_id (UUID, FK -> users.id)
- name (string)
- description (text)
- created_at, updated_at
```

### Credentials Table
```sql
- id (UUID, PK)
- vault_id (UUID, FK -> vaults.id)
- username (string)
- password_encrypted (longtext)
- url (string)
- created_at, updated_at
```

## 📂 Project Structure

```
project-inno/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── VaultController.php
│   │   │       └── CredentialController.php
│   │   └── Middleware/
│   │       └── HandleCors.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Vault.php
│   │   └── Credential.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── VaultRepository.php
│   │   └── CredentialRepository.php
│   └── Services/
│       ├── GoogleAuthService.php
│       └── EncryptionService.php
├── database/
│   └── migrations/
├── routes/
│   ├── api.php
│   └── web.php
└── .env
```

## 📝 Notes

- **UUID**: Semua ID menggunakan UUID (bukan auto-increment)
- **Timestamps**: Semua timestamp dalam format ISO 8601
- **Cascade Delete**: Menghapus vault akan menghapus semua credentials di dalamnya
- **No Authentication**: Untuk demo, semua endpoint public (tidak butuh token)

