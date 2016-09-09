# Inventory System

PHP / Postgres based inventory system, originally developed for the BGO-OD experiment. 

Attention! This system may only be used in protected internal networks as it is
prone to SQL-Injection. Never ever have it accessible by the possibly malign public!

## Setup instructions

### Database

```
CREATE DATABASE inventory;
CREATE USER inventory WITH PASSWORD 'myPassword';
GRANT ALL PRIVILEGES ON DATABASE inventory to inventory;

cat schema.sql | psql inventory
```

### Website

Use website-subfolder as webroot. 

### Configuration

Create `config.local.php` and override settings from `config.php`. 

Add a logo and favicon! 
