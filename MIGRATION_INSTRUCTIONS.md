# Kairos Migration Instructions

This guide will help you complete the migration from "LWR Events" to "Kairos".

## 📋 Pre-Migration Checklist

- [ ] WordPress database backup created
- [ ] You have SSH/terminal access to the server
- [ ] The plugin is currently working
- [ ] You've tested on a staging/local environment first (HIGHLY RECOMMENDED)

## 🚀 Migration Steps

### Step 1: Make Scripts Executable

```bash
cd /Users/florianthievent/workspace/private/wpdevelopment/wp-content/plugins/lwrevents

chmod +x migrate-to-kairos.sh
chmod +x rollback-kairos.sh
chmod +x verify-migration.sh
```

### Step 2: Run the Migration Script

```bash
./migrate-to-kairos.sh
```

**What this script does:**
1. ✅ Creates a timestamped backup of your entire plugin
2. ✅ Renames all remaining classes (KairosCPT, KairosWidget, KairosCalendarWidget)
3. ✅ Updates all class references throughout the codebase
4. ✅ Updates asset handles (CSS/JS)
5. ✅ Renames all PHP files (LWREvents.php → Kairos.php, etc.)
6. ✅ Updates all include/require paths
7. ✅ Updates text domains for translations

**Estimated time:** 5-10 seconds

### Step 3: Verify the Migration

```bash
./verify-migration.sh
```

This will check:
- ✅ All new files exist
- ✅ All old file references are updated
- ✅ Class definitions are correct
- ✅ No leftover old class names

### Step 4: Reactivate in WordPress

1. Go to WordPress Admin → Plugins
2. Find the plugin (might still show as "LWR Events" until reactivated)
3. **Deactivate** the plugin
4. **Activate** the plugin (will now show as "Kairos")

### Step 5: Test Functionality

Test these critical features:
- [ ] Plugin appears in admin menu as "Kairos"
- [ ] Settings page loads (`Kairos Einstellungen`)
- [ ] Can create new events
- [ ] Can edit existing events
- [ ] Events display on frontend
- [ ] Widgets appear and work
- [ ] Old shortcodes still work: `[lwrevents-list]`, `[lwrevents-list-future]`
- [ ] New shortcodes work: `[kairos-list]`, `[kairos-list-future]`
- [ ] User sign-up functionality works
- [ ] No PHP errors in debug log

## 🔄 If Something Goes Wrong

### Rollback to Previous Version

```bash
./rollback-kairos.sh
```

This will:
1. Delete the migrated files
2. Restore from the backup created in Step 2
3. Return you to "LWR Events"

Then reactivate the plugin in WordPress admin.

## 📂 What Changed vs What Stayed

### ✅ Changed (Internal - Safe):
- Plugin display name: "LWR Events" → "Kairos"
- Class names: `LWREvents`, `LWREventsCore`, etc. → `Kairos`, `KairosCore`, etc.
- File names: `LWREvents.php` → `Kairos.php`, etc.
- Function names: `lwr_events_activate()` → `kairos_activate()`, etc.
- Asset handles: `lwrevents-backend` → `kairos-backend`, etc.
- Constants: `LWR_PLUGIN_PATH` → `KAIROS_PLUGIN_PATH`

### ⚠️ Kept (External - Backwards Compatible):
- Post type slug: Still `lwrevents` (URLs unchanged)
- Database table: Still `{prefix}lwrevents_signin` (no data migration needed)
- Post meta keys: Still `lwrOrt`, `lwrDatumVon`, etc. (no data loss)
- WordPress options: Still `lwr_empty_events`, etc. (settings preserved)
- Old shortcodes: `[lwrevents-list]` still works (new ones added too)
- Template files: Still `single-lwrevents.php`, `archive-lwrevents.php`

## 📊 Migration Summary

| Item | Before | After | Breaking Change? |
|------|--------|-------|------------------|
| Plugin Name | LWR Events | Kairos | No |
| Main File | LWREvents.php | Kairos.php | No |
| CPT Slug | lwrevents | lwrevents | No |
| Database Table | lwrevents_signin | lwrevents_signin | No |
| Old Shortcodes | ✓ Works | ✓ Still works | No |
| New Shortcodes | ✗ N/A | ✓ Works | No |
| Settings | Preserved | Preserved | No |
| Event Data | Preserved | Preserved | No |

## 🐛 Troubleshooting

### Plugin doesn't show up after migration
**Solution:** The plugin directory is still named `lwrevents`. WordPress might cache the old name. Try:
1. Deactivate
2. Reactivate
3. Clear WordPress cache
4. Refresh admin page

### "Class not found" errors
**Solution:**
1. Run `./verify-migration.sh` to check for issues
2. Check that all files were renamed
3. Look for typos in class names

### Widgets disappeared
**Solution:** Widgets use different internal names now. You may need to:
1. Go to Appearance → Widgets
2. Re-add the Kairos widgets
3. Configure them again

### Old shortcodes stopped working
**Solution:** This shouldn't happen. If it does:
1. Check `Kairos.php` line ~227 for the backwards-compatible shortcode registration
2. Ensure both old and new shortcodes are registered

## 📝 Notes

- **Backup Location:** Check the script output for exact path
- **Backup Retention:** The backup is NOT automatically deleted. Keep it until you're confident everything works.
- **GitHub Repository:** After successful migration, update your GitHub repo name from `lwrevents` to `kairos` and push changes.

## 🆘 Support

If you encounter issues:
1. Check the verification output: `./verify-migration.sh`
2. Check WordPress debug log for errors
3. Rollback if needed: `./rollback-kairos.sh`
4. Review the migration plan: `KAIROS_MIGRATION_PLAN.md`

---

**Conservative Approach Benefits:**
- ✅ No URLs changed (no SEO impact)
- ✅ No data migration needed (no data loss risk)
- ✅ Old shortcodes still work (no content breaks)
- ✅ Settings preserved (no reconfiguration)
- ✅ Easy rollback if needed

Good luck with your migration! 🎉
