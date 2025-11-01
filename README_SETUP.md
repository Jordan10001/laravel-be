# Laravel Backend Setup - Credential Vault

Backend Laravel yang 100% kompatibel dengan frontend Next.js (`feku`).

## 🎯 Tujuan

Backend ini dibuat untuk menyediakan API yang identik dengan backend Go (`project-be-go`), sehingga frontend yang sama bisa menggunakan kedua backend secara bergantian tanpa perlu modifikasi.

## 📋 Prerequisites

- PHP 8.2 atau lebih tinggi
- Composer
- MySQL 8.0 atau lebih tinggi
- XAMPP (recommended) atau server web lain

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

**PENTING**: Gunakan Google OAuth credentials yang sama dengan backend Go!

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

Ini akan membuat tabel:
- `users` - User accounts (Google OAuth)
- `vaults` - Password vaults
- `credentials` - Stored credentials (encrypted)

### 6. Start Server

```bash
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

## 🔧 Configure Frontend

Edit file `.env` di folder `feku`:

```env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

Kemudian restart frontend:

```bash
cd feku
npm run dev
```

## 📡 API Endpoints

Semua endpoint API identik dengan backend Go. Lihat `API_COMPATIBILITY.md` untuk detail lengkap.

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

## 🧪 Testing

### Test dengan Postman

1. **Login**:
   - Buka browser: `http://localhost:8000/auth/google`
   - Login dengan Google
   - Copy `user_id` dari URL redirect

2. **Create Vault**:
   ```bash
   POST http://localhost:8000/api/v1/vaults
   Content-Type: application/json

   {
     "owner_user_id": "your-user-id",
     "name": "My Vault",
     "description": "Test vault"
   }
   ```

3. **Create Credential**:
   ```bash
   POST http://localhost:8000/api/v1/credentials
   Content-Type: application/json

   {
     "vault_id": "vault-id",
     "username": "test@example.com",
     "password": "SecurePassword123",
     "url": "https://example.com"
   }
   ```

### Test dengan Frontend

1. Pastikan backend Laravel berjalan di `http://localhost:8000`
2. Update `.env` di folder `feku`:
   ```env
   NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
   ```
3. Restart frontend
4. Buka `http://localhost:3000`
5. Test semua fitur (login, create vault, create credential, dll)

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

## 🔄 Switching Between Backends

### Untuk menggunakan backend Go:
```env
# feku/.env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8080
```

### Untuk menggunakan backend Laravel:
```env
# feku/.env
NEXT_PUBLIC_API_BASE_URL=http://localhost:8000
```

**Tidak perlu ubah kode frontend!** 🎉

## ⚠️ Troubleshooting

### CORS Error
- Pastikan `HandleCors` middleware aktif
- Check response headers include `Access-Control-Allow-Origin: *`

### Database Connection Error
- Pastikan MySQL berjalan
- Check credentials di `.env`
- Pastikan database `password` sudah dibuat

### Google OAuth Error
- Check `GOOGLE_CLIENT_ID` dan `GOOGLE_CLIENT_SECRET`
- Pastikan redirect URI di Google Console: `http://localhost:8000/auth/google/callback`
- Tambahkan `http://localhost:3000` di Authorized JavaScript origins

### Encryption Error
- Pastikan `APP_KEY` sudah di-set (run `php artisan key:generate`)
- Jangan ganti `APP_KEY` setelah ada data encrypted

## 📝 Notes

- **UUID**: Semua ID menggunakan UUID (bukan auto-increment)
- **Timestamps**: Semua timestamp dalam format ISO 8601
- **Cascade Delete**: Menghapus vault akan menghapus semua credentials di dalamnya
- **No Authentication**: Untuk demo, semua endpoint public (tidak butuh token)

## 🤝 Contributing

Jangan ubah struktur API response! Backend ini harus 100% kompatibel dengan frontend yang sudah ada.

## 📄 License

Same as project
