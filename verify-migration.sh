#!/bin/bash

# Kairos Migration Verification Script
# Checks if the migration was successful
# Created: 2025-12-11

PLUGIN_DIR="/Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents"

echo "=========================================="
echo "Kairos Migration Verification"
echo "=========================================="
echo ""

cd "$PLUGIN_DIR"

ERRORS=0

# Check if main plugin file exists
echo "Checking file structure..."
if [ -f "Kairos.php" ]; then
    echo "✓ Kairos.php exists"
else
    echo "❌ Kairos.php not found"
    ((ERRORS++))
fi

# Check if core files exist
if [ -f "core/KairosCore.php" ]; then
    echo "✓ core/KairosCore.php exists"
else
    echo "❌ core/KairosCore.php not found"
    ((ERRORS++))
fi

if [ -f "core/KairosCPT.php" ]; then
    echo "✓ core/KairosCPT.php exists"
else
    echo "❌ core/KairosCPT.php not found"
    ((ERRORS++))
fi

if [ -f "core/KairosWidget.php" ]; then
    echo "✓ core/KairosWidget.php exists"
else
    echo "❌ core/KairosWidget.php not found"
    ((ERRORS++))
fi

if [ -f "core/KairosCalendarWidget.php" ]; then
    echo "✓ core/KairosCalendarWidget.php exists"
else
    echo "❌ core/KairosCalendarWidget.php not found"
    ((ERRORS++))
fi

echo ""
echo "Checking for old file references..."

# Check if old files are still referenced
if grep -q "LWREventsCore.php" Kairos.php 2>/dev/null; then
    echo "❌ Found reference to old LWREventsCore.php"
    ((ERRORS++))
else
    echo "✓ No references to LWREventsCore.php"
fi

if grep -q "LWREventsCPT.php" Kairos.php 2>/dev/null; then
    echo "❌ Found reference to old LWREventsCPT.php"
    ((ERRORS++))
else
    echo "✓ No references to LWREventsCPT.php"
fi

echo ""
echo "Checking class definitions..."

# Check if new classes are defined
if grep -q "class Kairos" Kairos.php; then
    echo "✓ Kairos class found"
else
    echo "❌ Kairos class not found"
    ((ERRORS++))
fi

if grep -q "class KairosCore" core/KairosCore.php; then
    echo "✓ KairosCore class found"
else
    echo "❌ KairosCore class not found"
    ((ERRORS++))
fi

if grep -q "class KairosCPT" core/KairosCPT.php; then
    echo "✓ KairosCPT class found"
else
    echo "❌ KairosCPT class not found"
    ((ERRORS++))
fi

echo ""
echo "Checking for old class references..."

# Check if old class names are still used
OLD_CLASSES=$(grep -r "new LWREvents\|LWREventsCore\|LWREventsCPT\|LWREventsWidget\|LWREventsCalendarWidget" --include="*.php" . | grep -v "backup" | wc -l)

if [ "$OLD_CLASSES" -gt 0 ]; then
    echo "⚠️  Found $OLD_CLASSES references to old class names:"
    grep -rn "new LWREvents\|LWREventsCore\|LWREventsCPT\|LWREventsWidget\|LWREventsCalendarWidget" --include="*.php" . | grep -v "backup" | head -10
    echo ""
    echo "Run this command to see all occurrences:"
    echo "grep -rn \"new LWREvents\|LWREventsCore\|LWREventsCPT\|LWREventsWidget\|LWREventsCalendarWidget\" --include=\"*.php\" ."
else
    echo "✓ No old class references found"
fi

echo ""
echo "=========================================="
if [ $ERRORS -eq 0 ]; then
    echo "✅ Migration verification PASSED"
    echo "=========================================="
    echo ""
    echo "Next steps:"
    echo "1. Go to WordPress admin → Plugins"
    echo "2. Deactivate 'LWR Events' (or 'Kairos' if already showing)"
    echo "3. Reactivate the plugin"
    echo "4. Test functionality"
else
    echo "❌ Migration verification FAILED with $ERRORS errors"
    echo "=========================================="
    echo ""
    echo "Consider running: ./rollback-kairos.sh"
fi
echo ""
