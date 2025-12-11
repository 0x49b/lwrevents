#!/bin/bash

# Kairos Migration Script - Conservative Approach
# This script completes the renaming from LWR Events to Kairos
# Created: 2025-12-11

set -e  # Exit on error

PLUGIN_DIR="/Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents"
BACKUP_DIR="/Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents-backup-$(date +%Y%m%d-%H%M%S)"

echo "=========================================="
echo "Kairos Migration Script"
echo "=========================================="
echo ""

# Step 1: Create backup
echo "Step 1: Creating backup..."
cp -r "$PLUGIN_DIR" "$BACKUP_DIR"
echo "✓ Backup created at: $BACKUP_DIR"
echo ""

# Step 2: Rename remaining classes in files
echo "Step 2: Renaming class LWREventsCPT to KairosCPT..."
cd "$PLUGIN_DIR"

# Update class definition
sed -i '' 's/class LWREventsCPT/class KairosCPT/g' core/LWREventsCPT.php

# Update class instantiations
sed -i '' 's/new LWREventsCPT()/new KairosCPT()/g' LWREvents.php

# Update class references
sed -i '' "s/'LWREventsCPT'/'KairosCPT'/g" LWREvents.php

echo "✓ LWREventsCPT renamed to KairosCPT"
echo ""

echo "Step 3: Renaming class LWREventsWidget to KairosWidget..."

# Update class definition
sed -i '' 's/class LWREventsWidget/class KairosWidget/g' core/LWREventsWidget.php

# Update class instantiations
sed -i '' 's/new LWREventsWidget()/new KairosWidget()/g' LWREvents.php

# Update widget registration
sed -i '' "s/register_widget('LWREventsWidget')/register_widget('KairosWidget')/g" core/LWREventsWidget.php

# Update class comment
sed -i '' 's/Class LWREventsWidget ends here/Class KairosWidget ends here/g' core/LWREventsWidget.php

echo "✓ LWREventsWidget renamed to KairosWidget"
echo ""

echo "Step 4: Renaming class LWREventsCalendarWidget to KairosCalendarWidget..."

# Update class definition
sed -i '' 's/class LWREventsCalendarWidget/class KairosCalendarWidget/g' core/LWREventsCalendarWidget.php

# Update class instantiations
sed -i '' 's/new LWREventsCalendarWidget()/new KairosCalendarWidget()/g' LWREvents.php

# Update widget registration
sed -i '' "s/register_widget('LWREventsCalendarWidget')/register_widget('KairosCalendarWidget')/g" core/LWREventsCalendarWidget.php

# Update class comment
sed -i '' 's/Class LWREventsCalendarWidget ends here/Class KairosCalendarWidget ends here/g' core/LWREventsCalendarWidget.php

echo "✓ LWREventsCalendarWidget renamed to KairosCalendarWidget"
echo ""

echo "Step 5: Updating asset handles..."

# Update backend assets
sed -i '' "s/'lwrevents-backend'/'kairos-backend'/g" LWREvents.php

# Update frontend assets
sed -i '' "s/'lwrjquery'/'kairos-jquery'/g" LWREvents.php
sed -i '' "s/'lwrevents-ics'/'kairos-ics'/g" LWREvents.php
sed -i '' "s/'lwrevents-filesaver'/'kairos-filesaver'/g" LWREvents.php
sed -i '' "s/'lwrevents-blob'/'kairos-blob'/g" LWREvents.php
sed -i '' "s/'lwrevent-style'/'kairos-style'/g" LWREvents.php
sed -i '' "s/'fontawesome'/'kairos-fontawesome'/g" LWREvents.php

echo "✓ Asset handles updated"
echo ""

echo "Step 6: Updating CPT function name..."
sed -i '' 's/lwr_events_cpt_config/kairos_cpt_config/g' core/LWREventsCPT.php
sed -i '' 's/lwr_events_cpt_config/kairos_cpt_config/g' LWREvents.php

echo "✓ CPT function renamed"
echo ""

echo "Step 7: Updating widget function names..."
sed -i '' 's/lwr_load_widget/kairos_load_widget/g' core/LWREventsWidget.php
sed -i '' 's/lwr_load_widget/kairos_load_widget/g' LWREvents.php

sed -i '' 's/lwr_load_cal_widget/kairos_load_cal_widget/g' core/LWREventsCalendarWidget.php
sed -i '' 's/lwr_load_cal_widget/kairos_load_cal_widget/g' LWREvents.php

echo "✓ Widget function names updated"
echo ""

echo "Step 8: Renaming PHP files..."

# Rename core files
mv core/LWREventsCore.php core/KairosCore.php
echo "✓ Renamed: LWREventsCore.php → KairosCore.php"

mv core/LWREventsCPT.php core/KairosCPT.php
echo "✓ Renamed: LWREventsCPT.php → KairosCPT.php"

mv core/LWREventsWidget.php core/KairosWidget.php
echo "✓ Renamed: LWREventsWidget.php → KairosWidget.php"

mv core/LWREventsCalendarWidget.php core/KairosCalendarWidget.php
echo "✓ Renamed: LWREventsCalendarWidget.php → KairosCalendarWidget.php"

# Rename main plugin file
mv LWREvents.php Kairos.php
echo "✓ Renamed: LWREvents.php → Kairos.php"

echo ""

echo "Step 9: Updating file paths in includes..."

# Update includes in Kairos.php
sed -i '' "s|core/LWREventsCore.php|core/KairosCore.php|g" Kairos.php
sed -i '' "s|core/LWREventsCPT.php|core/KairosCPT.php|g" Kairos.php
sed -i '' "s|core/LWREventsWidget.php|core/KairosWidget.php|g" Kairos.php
sed -i '' "s|core/LWREventsCalendarWidget.php|core/KairosCalendarWidget.php|g" Kairos.php

# Update include in settings view
sed -i '' "s|core/LWREventsCore.php|core/KairosCore.php|g" views/backend/lwr-settings-view.php

echo "✓ File paths updated"
echo ""

echo "Step 10: Updating text domains..."

# Update text domains in widgets
sed -i '' "s/'LWREventsWidget_domain'/'kairos'/g" core/KairosWidget.php
sed -i '' "s/'LWREventsCalendarWidget_domain'/'kairos'/g" core/KairosCalendarWidget.php

echo "✓ Text domains updated"
echo ""

echo "=========================================="
echo "Migration Complete!"
echo "=========================================="
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
echo "⚠️  IMPORTANT NEXT STEPS:"
echo "1. Deactivate the plugin in WordPress admin"
echo "2. Reactivate it (it will now show as 'Kairos')"
echo "3. Test all functionality:"
echo "   - Create/edit events"
echo "   - Check widgets"
echo "   - Test shortcodes (both old and new)"
echo "   - Check frontend display"
echo "   - Test user sign-ups"
echo ""
echo "If something goes wrong, run: ./rollback-kairos.sh"
echo ""
