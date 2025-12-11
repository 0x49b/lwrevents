# Kairos Migration Plan - Phase 2

## Overview
This plan outlines the complete migration from "LWR Events" to "Kairos" including code, files, and database changes.

## ⚠️ IMPORTANT: Pre-Migration Steps

### 1. Backup Everything
- [ ] Create database backup
- [ ] Create full plugin directory backup
- [ ] Document current plugin version
- [ ] Note all active installations

### 2. Test Environment
- [ ] Test migration on staging/local environment FIRST
- [ ] Verify functionality before production deployment

---

## Migration Phases

### Phase A: File and Directory Renaming

#### Main Plugin File
- [ ] Rename: `LWREvents.php` → `Kairos.php`

#### Core Files
- [ ] Rename: `core/LWREventsCore.php` → `core/KairosCore.php`
- [ ] Rename: `core/LWREventsCPT.php` → `core/KairosCPT.php`
- [ ] Rename: `core/LWREventsWidget.php` → `core/KairosWidget.php`
- [ ] Rename: `core/LWREventsCalendarWidget.php` → `core/KairosCalendarWidget.php`

#### Plugin Directory (Optional - Breaking Change)
- [ ] Consider: Rename plugin directory `lwrevents/` → `kairos/`
  - **WARNING**: This will deactivate the plugin on all sites
  - **Alternative**: Keep directory name for backwards compatibility

---

### Phase B: Class Renaming

#### Main Classes
- [ ] `LWREvents` → `Kairos`
- [ ] `LWREventsCore` → `KairosCore`
- [ ] `LWREventsCPT` → `KairosCPT`
- [ ] `LWREventsWidget` → `KairosWidget`
- [ ] `LWREventsCalendarWidget` → `KairosCalendarWidget`

#### Update all class instantiations
- [ ] Update all `new LWREventsCore()` → `new KairosCore()`
- [ ] Update all `new LWREventsCPT()` → `new KairosCPT()`
- [ ] Update all `new LWREventsWidget()` → `new KairosWidget()`
- [ ] Update all `new LWREventsCalendarWidget()` → `new KairosCalendarWidget()`

#### Update all class references in:
- [ ] add_action/add_filter callbacks
- [ ] add_shortcode callbacks
- [ ] register_widget calls

---

### Phase C: Function and Method Renaming

#### Lifecycle Functions
- [ ] `lwr_events_activate()` → `kairos_activate()`
- [ ] `lwr_events_deactivate()` → `kairos_deactivate()`
- [ ] `lwr_events_uninstall()` → `kairos_uninstall()`

#### Custom Post Type Functions
- [ ] `lwr_events_cpt_config()` → `kairos_cpt_config()`
- [ ] `lwr_events_mbx()` → `kairos_mbx()`
- [ ] `lwr_save_events_mbx()` → `kairos_save_mbx()`

#### Template Functions
- [ ] `lwr_frontview_templates()` → `kairos_frontview_templates()`
- [ ] `lwr_custom_post_type_template()` → `kairos_custom_post_type_template()`

#### Widget Functions
- [ ] `lwr_events_widget_init()` → `kairos_widget_init()`
- [ ] `lwr_load_widget()` → `kairos_load_widget()`
- [ ] `lwr_load_cal_widget()` → `kairos_load_cal_widget()`

#### Asset Loading Functions
- [ ] `lwr_events_loadStylesAndJSBackend()` → `kairos_load_backend_assets()`
- [ ] `lwr_events_loadStylesAndJSFrontend()` → `kairos_load_frontend_assets()`

#### Shortcode Functions
- [ ] `lwrShortcodeListFuture()` → `kairosShortcodeListFuture()`
- [ ] `lwrShortcodeList()` → `kairosShortcodeList()`

---

### Phase D: Custom Post Type & Taxonomy

#### Post Type Slug
**⚠️ CRITICAL - SEO IMPACT**
- Current: `lwrevents`
- Option 1: Keep `lwrevents` for URL compatibility (RECOMMENDED)
- Option 2: Change to `kairos` (requires redirects)

#### If changing post type slug:
- [ ] Update: `register_post_type('lwrevents')` → `register_post_type('kairos')`
- [ ] Create 301 redirects from old URLs to new URLs
- [ ] Update any hardcoded URLs in theme/content

#### Template Files
- [ ] Keep filenames: `single-lwrevents.php`, `archive-lwrevents.php` (if keeping post type slug)
- [ ] OR rename: `single-kairos.php`, `archive-kairos.php` (if changing slug)

---

### Phase E: Shortcode Names

**⚠️ BREAKING CHANGE FOR USERS**

Current shortcodes:
- `[lwrevents-list-future]`
- `[lwrevents-list]`

Options:
1. **Backwards Compatible** (RECOMMENDED):
   - Keep old shortcodes working
   - Add new shortcodes: `[kairos-list-future]`, `[kairos-list]`
   - Register both versions

2. **Clean Break**:
   - Only use new shortcodes
   - Document migration for users

---

### Phase F: Database Tables

#### Table Renaming
**Current table**: `{prefix}lwrevents_signin`

Options:

1. **Keep existing table** (RECOMMENDED - No data migration needed):
   - Update code references but keep table name
   - No risk of data loss

2. **Rename table** (Requires migration):
   - New name: `{prefix}kairos_signin`
   - Migration SQL:
     ```sql
     RENAME TABLE {prefix}lwrevents_signin TO {prefix}kairos_signin;
     ```

#### Update all table references in code:
- [ ] Update all `$wpdb->prefix . 'lwrevents_signin'` references
- [ ] Search files: LWREventsCore.php, LWREvents.php

---

### Phase G: WordPress Options (wp_options table)

#### Current Options:
- `lwr_empty_events`
- `lwr_events_contact_mail`
- `lwr_signin_for_users`
- `lwr_future_max`
- `lwr_all_max`
- `lwr_archiv_max`

#### Migration Approach:

**Option 1: Migrate to new option names** (RECOMMENDED):
- Copy old values to new option names
- Keep old options for rollback ability
- Clean up old options after successful migration

Migration code:
```php
$old_options = [
    'lwr_empty_events' => 'kairos_empty_events',
    'lwr_events_contact_mail' => 'kairos_contact_mail',
    'lwr_signin_for_users' => 'kairos_signin_for_users',
    'lwr_future_max' => 'kairos_future_max',
    'lwr_all_max' => 'kairos_all_max',
    'lwr_archiv_max' => 'kairos_archiv_max',
];

foreach ($old_options as $old => $new) {
    $value = get_option($old);
    if ($value !== false) {
        add_option($new, $value);
    }
}
```

**Option 2: Keep existing option names**:
- No migration needed
- Less clean but safer

---

### Phase H: Post Meta Keys

#### Current Meta Keys:
- `lwrOrt`
- `lwrDatumVon`, `lwrDatumVonSQL`, `lwrDatumZeitVonUnix`
- `lwrDatumBis`, `lwrDatumBisSQL`, `lwrDatumZeitBisUnix`
- `lwrTage`
- `lwrZeitVon`, `lwrZeitBis`
- `lwrAnmeldenZeit`, `lwrAnmelden`
- `lwrOK`, `lwrMailOK`
- `lwrMaxTN`
- `lwrVoraussetzung`, `lwrAusruestung`
- `lwrExtAllowed`

#### Recommendation: **Keep existing meta keys**
- Migrating post meta is complex and risky
- Current keys work fine
- Prefix doesn't appear to end users
- Code can use new variable names internally

---

### Phase I: Constants and Global Variables

#### Constants
- [ ] `LWR_PLUGIN_PATH` → `KAIROS_PLUGIN_PATH`

#### JavaScript Variables
- [ ] Check for any `lwrevents` JavaScript variables
- [ ] Update AJAX action names if needed

---

### Phase J: Asset Handles (CSS/JS)

#### Current Handles:
- `lwrjquery`
- `lwrevents-backend`
- `lwrevents-ics`
- `lwrevents-filesaver`
- `lwrevents-blob`
- `lwrevent-style`

#### Update to:
- `kairos-jquery`
- `kairos-backend`
- `kairos-ics`
- `kairos-filesaver`
- `kairos-blob`
- `kairos-style`

---

### Phase K: AJAX Actions

Search for and update any AJAX action names:
- [ ] `wp_ajax_user_sign_event` → Consider keeping or update
- [ ] `wp_ajax_update_sign_table` → Consider keeping or update

---

### Phase L: Text Domains

#### Current Text Domains:
- `LWREventsWidget_domain`
- `LWREventsCalendarWidget_domain`

#### Update to:
- `kairos`
- `kairos` (use same domain for all)

#### Update in:
- [ ] All `__()` function calls
- [ ] All `_e()` function calls
- [ ] Plugin header: `Text Domain: kairos`

---

### Phase M: GitHub Repository

- [ ] Update repository name: `lwrevents` → `kairos`
- [ ] Update all GitHub URLs in code
- [ ] Update README
- [ ] Create new release

---

## Migration Implementation Order

### Step 1: Preparation
1. Full backup
2. Create staging environment
3. Update GitHub repository

### Step 2: Code Changes (Non-Breaking)
1. Update include/require paths for renamed files
2. Update class names and instantiations
3. Update function names
4. Update constants
5. Update asset handles
6. Update text domains

### Step 3: File Renaming
1. Rename core files
2. Update all file references
3. Rename main plugin file LAST

### Step 4: Database Migration (Optional)
1. Create migration script
2. Copy options to new names
3. Optionally rename table
4. Test thoroughly

### Step 5: Testing
1. Test all functionality
2. Test widgets
3. Test shortcodes
4. Test post creation/editing
5. Test front-end display
6. Test settings page

### Step 6: Deployment
1. Deactivate plugin
2. Replace plugin files
3. Reactivate plugin
4. Run any migration routines
5. Verify functionality

---

## Backwards Compatibility Recommendations

For minimal disruption:

### ✅ CHANGE:
- Plugin name (display)
- Class names (internal)
- Function names (internal)
- File names (internal)
- Constants (internal)
- Asset handles (low impact)
- Text domains (translation)

### ⚠️ KEEP (or support both):
- Post type slug (`lwrevents`)
- Shortcode names (support both old and new)
- Database table names
- Post meta keys
- Option names (or migrate with fallback)

---

## Risk Assessment

| Change Type | Risk Level | Impact | Recommendation |
|-------------|-----------|---------|----------------|
| Display names | Low | User-facing only | Change |
| Class names | Low | Internal only | Change |
| File names | Medium | Requires path updates | Change with care |
| Post type slug | **HIGH** | **SEO, URLs** | **Keep or redirect** |
| Shortcodes | **HIGH** | **User content** | **Support both** |
| Database tables | **HIGH** | **Data loss risk** | **Keep or careful migration** |
| Post meta | **HIGH** | **Data loss risk** | **Keep existing** |
| Options | Medium | Settings loss | Migrate with fallback |

---

## Rollback Plan

If issues occur:
1. Deactivate Kairos
2. Restore from backup
3. Reactivate as LWR Events
4. All data should be intact if table/meta keys weren't changed

---

## Estimated Time

- **Conservative approach** (keep post type, tables, meta): 4-6 hours
- **Full migration** (change everything): 8-12 hours + extensive testing

---

## Post-Migration Checklist

- [ ] Plugin activates without errors
- [ ] Settings page loads correctly
- [ ] Can create new events
- [ ] Can edit existing events
- [ ] Front-end display works
- [ ] Widgets display correctly
- [ ] Shortcodes work
- [ ] User sign-ups work
- [ ] Excel export works
- [ ] No PHP errors in logs
- [ ] No JavaScript errors in console
