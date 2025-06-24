# DABS Database Files

This folder contains all database-related files for the Daily Activity Briefing System (DABS).

## Files

- `schema.sql` - Complete database structure with tables, indexes, and initial data
- `sample_data.sql` - Sample data for testing and demonstration
- `updates/` - Database update scripts for version upgrades

## How to Import

### Method 1: Using phpMyAdmin
1. Open phpMyAdmin in your web browser
2. Create a new database called `dabs_system`
3. Select the database
4. Click "Import" tab
5. Choose `schema.sql` file
6. Click "Go" to import

### Method 2: Using MySQL Command Line
```bash
mysql -u your_username -p
CREATE DATABASE dabs_system;
USE dabs_system;
SOURCE path/to/schema.sql;
