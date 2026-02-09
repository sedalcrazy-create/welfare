#!/bin/bash

# ===========================================
# Welfare System - Daily Backup Script
# ===========================================

BACKUP_DIR="/var/www/welfare/backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Create backup directory
mkdir -p $BACKUP_DIR

echo "[$DATE] Starting backup..."

# Database backup
echo "Backing up database..."
docker exec welfare_postgres pg_dump -U welfare_user welfare_system > $BACKUP_DIR/db_$DATE.sql

if [ $? -eq 0 ]; then
    echo "Database backup successful: db_$DATE.sql"
    gzip $BACKUP_DIR/db_$DATE.sql
else
    echo "Database backup failed!"
fi

# Storage backup
echo "Backing up storage..."
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C /var/www/welfare storage/

if [ $? -eq 0 ]; then
    echo "Storage backup successful: storage_$DATE.tar.gz"
else
    echo "Storage backup failed!"
fi

# Remove old backups
echo "Removing backups older than $RETENTION_DAYS days..."
find $BACKUP_DIR -type f -mtime +$RETENTION_DAYS -delete

echo "[$DATE] Backup completed!"

# List current backups
echo ""
echo "Current backups:"
ls -lh $BACKUP_DIR/
