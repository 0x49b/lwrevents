#!/bin/bash

# Kairos Rollback Script
# This script restores the plugin from backup if the migration fails
# Created: 2025-12-11

set -e  # Exit on error

PLUGIN_DIR="/Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents"

echo "=========================================="
echo "Kairos Rollback Script"
echo "=========================================="
echo ""

# Find the most recent backup
BACKUP_DIR=$(ls -dt /Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents-backup-* 2>/dev/null | head -1)

if [ -z "$BACKUP_DIR" ]; then
    echo "❌ Error: No backup found!"
    echo "Cannot rollback without a backup directory."
    exit 1
fi

echo "Found backup: $BACKUP_DIR"
echo ""
read -p "⚠️  This will delete current plugin files and restore from backup. Continue? (y/N) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Rollback cancelled."
    exit 0
fi

echo ""
echo "Rolling back..."

# Remove current plugin directory
rm -rf "$PLUGIN_DIR"

# Restore from backup
cp -r "$BACKUP_DIR" "$PLUGIN_DIR"

echo "✓ Rollback complete!"
echo ""
echo "⚠️  IMPORTANT NEXT STEPS:"
echo "1. Reactivate the plugin in WordPress admin"
echo "2. The plugin should be back to 'LWR Events'"
echo ""
echo "Backup preserved at: $BACKUP_DIR"
echo ""
