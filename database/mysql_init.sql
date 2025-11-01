-- MySQL Database Initialization Script
-- Converted from PostgreSQL schema

-- Drop tables if exist (for clean setup)
DROP TABLE IF EXISTS credentials;
DROP TABLE IF EXISTS vaults;
DROP TABLE IF EXISTS users;

-- Users table
CREATE TABLE users (
    id CHAR(36) PRIMARY KEY,
    provider_id VARCHAR(255) NULL,
    provider_name VARCHAR(255) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NULL,
    picture_url TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT email_not_empty CHECK (LENGTH(COALESCE(email, '')) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create index on provider lookup
CREATE INDEX idx_users_provider ON users(provider_id, provider_name);

-- Vaults table
CREATE TABLE vaults (
    id CHAR(36) PRIMARY KEY,
    owner_user_id CHAR(36) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vault_owner FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_vaults_owner ON vaults(owner_user_id);

-- Credentials table
CREATE TABLE credentials (
    id CHAR(36) PRIMARY KEY,
    vault_id CHAR(36) NOT NULL,
    username VARCHAR(255) NULL,
    password_encrypted TEXT NULL,
    url VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cred_vault FOREIGN KEY (vault_id) REFERENCES vaults(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_credentials_vault ON credentials(vault_id);
