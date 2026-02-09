#!/bin/bash

# ===========================================
# Welfare System - Restore Script
# ===========================================

if [ -z "$1" ]; then
    echo "Usage: ./restore.sh <backup_date>"
    echo "Example: ./restore.sh 20260113_030000"
    exit 1
fi

BACKUP_DATE=$1
BACKUP_DIR="/var/www/welfare/backups"
DB_FILE="$BACKUP_DIR/db_${BACKUP_DATE}.sql.gz"
STORAGE_FILE="$BACKUP_DIR/storage_${BACKUP_DATE}.tar.gz"

echo "Restoring backup from: $BACKUP_DATE"

# Check if backup files exist
if [ ! -f "$DB_FILE" ]; then
    echo "Error: Database backup not found: $DB_FILE"
    exit 1
fi

if [ ! -f "$STORAGE_FILE" ]; then
    echo "Warning: Storage backup not found: $STORAGE_FILE"
fi

# Restore database
echo "Restoring database..."
gunzip -c $DB_FILE | docker exec -i welfare_postgres psql -U welfare_user welfare_system

if [ $? -eq 0 ]; then
    echo "Database restored successfully!"
else
    echo "Database restore failed!"
    exit 1
fi

# Restore storage
if [ -f "$STORAGE_FILE" ]; then
    echo "Restoring storage..."
    tar -xzf $STORAGE_FILE -C /var/www/welfare/
    echo "Storage restored successfully!"
fi

# Clear cache
echo "Clearing cache..."
docker exec welfare_app php artisan cache:clear
docker exec welfare_app php artisan config:clear

echo "Restore completed!"
