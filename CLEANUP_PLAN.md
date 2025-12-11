# Dead Code Cleanup Plan - LWR Events Plugin

**Created:** 2025-12-11
**Estimated Impact:** ~250+ lines removed
**Risk Level:** Low (all identified code is unused)

---

## Pre-Cleanup Checklist

- [ ] Create a backup of the plugin or ensure git is tracking all changes
- [ ] Create a new git branch for cleanup: `git checkout -b cleanup-dead-code`
- [ ] Ensure you have a test environment to verify changes
- [ ] Review the full analysis in `DEAD_CODE_ANALYSIS.md`

---

## Phase 1: Safe Removals (High Priority)

### Step 1.1: Remove Commented-Out Code Blocks
**Risk:** None | **Time:** 5 minutes

#### File: `LWREvents.php`
- [ ] **Lines 60-67** - Remove commented update checker code
```php
// DELETE LINES 60-67:
//Update Checker
//$puc = Puc_v4_Factory::buildUpdateChecker(
//    'https://github.com/0x49b/lwrevents',
//    __FILE__,
//    'lwrevents',
//    1
//);
//$puc->setBranch('master');
```

#### File: `core/LWREventsCore.php`
- [ ] **Lines 24-25** - Remove commented AJAX hooks
```php
// DELETE LINES 24-25:
//add_action( 'wp_ajax_signInUserForEvent', array($this, 'signInUserForEvent') );
//add_action( 'wp_ajax_nopriv_signInUserForEvent', array($this, 'signInUserForEvent') );
```

- [ ] **Lines 238-239** - Remove commented database query
```php
// DELETE LINES 238-239:
//global $wpdb;
//$setting = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = '" . $option_name . "'", ARRAY_A);
```

- [ ] **Line 343** - Remove commented unused variable
```php
// DELETE LINE 343:
//$round = count($tage);
```

- [ ] **Line 353** - Remove commented debug echo
```php
// DELETE LINE 353:
//echo json_encode($custom_posts->posts);
```

**Test:** Verify plugin still loads without PHP errors

---

### Step 1.2: Delete Entire Unused ICS Class
**Risk:** None (never used) | **Time:** 2 minutes

- [ ] **Delete file:** `core/LWREventsIcs.php` (entire file, 92 lines)

- [ ] **Edit:** `LWREvents.php` - Remove the include statement at line 23
```php
// DELETE LINE 23:
require_once plugin_dir_path(__FILE__) . 'core/LWREventsIcs.php'; // ICS Creator, not in use yet
```

**Test:** Verify plugin loads without errors

---

### Step 1.3: Remove Debug Code
**Risk:** None (improves production code) | **Time:** 1 minute

#### File: `core/LWREventsCore.php`
- [ ] **Line 723** - Remove `var_dump()` statement
```php
// In function getSettingsSelectList(), DELETE LINE 723:
var_dump($setting);
```

**Test:** Load settings page and verify no debug output appears

---

### Step 1.4: Remove Empty PHP Tags
**Risk:** None | **Time:** 1 minute

#### File: `views/frontend/single-lwrevents.php`
- [ ] **Line 118** - Remove empty PHP tag pair
```php
// CHANGE LINE 118 FROM:
<td><?php $lwr->eventMeta($post->ID, 'lwrVoraussetzung'); ?><?php ?></td>

// TO:
<td><?php $lwr->eventMeta($post->ID, 'lwrVoraussetzung'); ?></td>
```

**Test:** View a single event on frontend

---

### Step 1.5: Fix Duplicate Operations
**Risk:** None (removes redundancy) | **Time:** 3 minutes

#### File: `LWREvents.php`
- [ ] **Line 101** - Remove duplicate `add_option()` call
```php
// DELETE LINE 101:
add_option('lwr_signin_for_users', false);
// Keep line 97 with value 0
```

- [ ] **Line 128** - Remove duplicate `delete_option()` call
```php
// DELETE LINE 128:
delete_option('lwr_signin_for_users');
// Keep line 124
```

#### File: `core/LWREventsCore.php`
- [ ] **Lines 693-696** - Remove duplicate contact mail save in `saveSettingsInDB()`
```php
// DELETE LINES 693-696:
$wpdb->replace($wpdb->prefix . 'options', array(
    'option_name' => 'lwr_events_contact_mail',
    'option_value' => $post['lwr_events_contact_mail'],
));
// Keep lines 684-686
```

**Test:** Test plugin activation/deactivation and settings save

---

## Phase 2: Remove Unused Functions (High Priority)

### Step 2.1: Remove Unused Methods from LWREventsCPT
**Risk:** Low (all are private/never called) | **Time:** 10 minutes

#### File: `core/LWREventsCPT.php`

- [ ] **Lines 36-43** - Delete `getEventTitle()` method
```php
// DELETE ENTIRE METHOD (lines 36-43)
private function getEventTitle($eventID) { ... }
```

- [ ] **Lines 46-56** - Delete `getUsersForList()` method
```php
// DELETE ENTIRE METHOD (lines 46-56)
private function getUsersForList($eventID) { ... }
```

- [ ] **Lines 58-73** - Delete `getSignInString()` method
```php
// DELETE ENTIRE METHOD (lines 58-73)
private function getSignInString($sid) { ... }
```

- [ ] **Lines 75-81** - Delete `checkForComments()` method
```php
// DELETE ENTIRE METHOD (lines 75-81)
private function checkForComments( $eventID ) { ... }
```

- [ ] **Lines 83-89** - Delete `loadCommentsForEvent()` method
```php
// DELETE ENTIRE METHOD (lines 83-89)
private function loadCommentsForEvent( $eventID ) { ... }
```

- [ ] **Lines 167-176** - Delete `getExt()` and `setExt()` methods
```php
// DELETE BOTH METHODS (lines 167-176)
public function getExt() { ... }
public function setExt( $ext ) { ... }
```

- [ ] **Line 20** - Delete the `$ext` property declaration
```php
// DELETE from property declarations:
private $ext;
```

**Test:** Verify all admin and frontend functionality works

---

### Step 2.2: Remove Unused Methods from LWREventsCore
**Risk:** Low (never called) | **Time:** 5 minutes

#### File: `core/LWREventsCore.php`

- [ ] **Lines 490-492** - Delete `eventTime()` stub function
```php
// DELETE ENTIRE FUNCTION (lines 490-492)
function eventTime($eventID) {
    echo "CLOCK";
}
```

- [ ] **Lines 772-798** - Delete `getPagination()` function
```php
// DELETE ENTIRE FUNCTION (lines 772-798)
function getPagination() { ... }
```

**Test:** Verify event display and all shortcodes work

---

### Step 2.3: Remove Unused Update Function
**Risk:** Low (never hooked) | **Time:** 2 minutes

#### File: `LWREvents.php`

- [ ] **Lines 231-254** - Delete `my_plugin_check_for_updates()` function
```php
// DELETE ENTIRE FUNCTION BLOCK (lines 231-254)
if (!function_exists('my_plugin_check_for_updates')) {
    function my_plugin_check_for_updates($update, $plugin_data, $plugin_file) { ... }
}
```

**Test:** Verify plugin updates still work with the active update checker (lines 32-39)

---

## Phase 3: Logic Fixes (Medium Priority)

### Step 3.1: Fix Revision Check Logic
**Risk:** Medium (fixes potential bug) | **Time:** 3 minutes

#### File: `core/LWREventsCPT.php`

- [ ] **Lines 527-540** - Move revision check OUTSIDE the foreach loop

**Current code:**
```php
foreach ($events_meta as $key => $value) {
    if ($post->post_type == 'revision') {
        return;
    }
    // ... rest of code
}
```

**Change to:**
```php
if ($post->post_type == 'revision') {
    return;
}

foreach ($events_meta as $key => $value) {
    // ... rest of code (without the if check)
}
```

**Test:**
- Create/edit an event
- Test with post revisions enabled
- Verify meta data saves correctly

---

### Step 3.2: Remove Unreachable Break Statements
**Risk:** None (cleanup) | **Time:** 2 minutes

#### File: `core/LWREventsCPT.php`

- [ ] **Lines 62-70** - Remove all `break;` statements after `return` in `getSignInString()`

**Note:** This function is being deleted in Step 2.1, so skip this if you've already deleted it.

---

### Step 3.3: Remove Unused Variables
**Risk:** None | **Time:** 5 minutes

#### File: `core/LWREventsCore.php`

- [ ] **Line 48** - Remove unused `$todayUnix` in `lwrShortcodeListFuture()`
```php
// DELETE LINE 48:
$todayUnix = strtotime(date('d.m.Y H:i:s'));
```

- [ ] **Lines 169-170** - Remove unused `$todayUnix` in `lwrGetArchiveForCategory()`
```php
// DELETE LINE 170:
$todayUnix = strtotime(date('d.m.Y H:i:s'));
```

#### File: `views/frontend/single-lwrevents.php`

- [ ] **Line 64** - Remove unused `$loop` variable
```php
// DELETE LINE 64:
$loop = new WP_Query(array('post_type' => 'lwrevents',));
```

**Test:** Verify shortcodes and event archives display correctly

---

## Phase 4: Optional Cleanup (Low Priority)

### Step 4.1: Remove Unused Meta Field
**Risk:** Low (data remains in DB but unused) | **Time:** 2 minutes

#### File: `core/LWREventsCPT.php`

- [ ] **Line 516** - Remove unused `lwrExtAllowed` meta field save
```php
// DELETE LINE 516:
$events_meta['lwrExtAllowed'] = $_POST['lwrExtAllowed'];
```

**Note:** This will leave existing data in the database but stop saving new data. Consider adding a migration to clean up old data if needed.

**Test:** Edit events and verify all other meta fields save correctly

---

## Post-Cleanup Checklist

### Testing
- [ ] Activate/deactivate plugin successfully
- [ ] Create a new event with all fields
- [ ] Edit an existing event
- [ ] View event on frontend (single and archive)
- [ ] Test all shortcodes
- [ ] Test event registration/sign-in functionality
- [ ] Test admin settings page
- [ ] Check for any PHP errors in debug log
- [ ] Test with WordPress debug mode enabled

### Code Review
- [ ] Search for any references to deleted functions (shouldn't find any)
- [ ] Verify no broken function calls
- [ ] Check that all includes/requires are valid
- [ ] Run PHP linter if available: `php -l LWREvents.php`

### Documentation
- [ ] Update plugin version number
- [ ] Add entry to changelog about code cleanup
- [ ] Mark this cleanup plan as completed

### Version Control
- [ ] Review all changes: `git diff`
- [ ] Stage changes: `git add .`
- [ ] Commit with message: `git commit -m "Remove dead code - cleaned up ~250 lines of unused code, functions, and comments"`
- [ ] Merge to master: `git checkout master && git merge cleanup-dead-code`
- [ ] Tag release if appropriate: `git tag -a v1.x.x -m "Code cleanup release"`

---

## Rollback Plan

If issues occur after cleanup:

1. **Immediate rollback:**
   ```bash
   git checkout master
   # or
   git reset --hard HEAD~1
   ```

2. **Selective rollback:**
   ```bash
   git revert <commit-hash>
   ```

3. **File-specific rollback:**
   ```bash
   git checkout HEAD~1 -- path/to/file.php
   ```

---

## Summary

### Files to Modify (8 files)
1. ✏️ `LWREvents.php` - Remove commented code, duplicates, unused function
2. ✏️ `core/LWREventsCore.php` - Remove comments, debug code, unused functions, duplicates, unused variables
3. ✏️ `core/LWREventsCPT.php` - Remove unused methods, fix logic, remove unused meta field
4. ✏️ `views/frontend/single-lwrevents.php` - Remove empty PHP tags, unused variable
5. ❌ `core/LWREventsIcs.php` - DELETE ENTIRE FILE

### Files to Test
- All admin pages (event creation, editing, settings)
- All frontend displays (single event, archives, shortcodes)
- Plugin activation/deactivation
- Event registration/sign-in functionality

### Expected Outcome
- ~250+ lines of code removed
- No functional changes (all removed code was unused)
- Cleaner, more maintainable codebase
- Slightly improved performance (less code to load)
- One potential bug fix (revision check logic)

---

## Estimated Time

- **Phase 1 (Safe Removals):** ~15 minutes
- **Phase 2 (Remove Functions):** ~20 minutes
- **Phase 3 (Logic Fixes):** ~10 minutes
- **Phase 4 (Optional):** ~5 minutes
- **Testing:** ~30 minutes
- **Total:** ~1.5 hours

---

## Notes

- All changes are low-risk since identified code is genuinely unused
- Take breaks between phases to test thoroughly
- Don't rush - verify each step works before proceeding
- Keep this plan open in a separate window while working
- Mark checkboxes as you complete each step

---

**Ready to begin? Start with Phase 1, Step 1.1!**

---

## Completion Checklist

- [ ] Phase 1 Complete (Safe Removals)
- [ ] Phase 2 Complete (Remove Functions)
- [ ] Phase 3 Complete (Logic Fixes)
- [ ] Phase 4 Complete (Optional Cleanup)
- [ ] All tests passed
- [ ] Changes committed to git
- [ ] Documentation updated
- [ ] Cleanup plan archived

**Completed Date:** ___________
**Final Notes:** ___________
