# Dead Code Analysis Report - LWR Events Plugin

**Analysis Date:** 2025-12-11
**Plugin:** LWR Events WordPress Plugin

---

## Summary Statistics

- **Commented out code blocks:** 5 instances
- **Unused functions/methods:** 10 instances
- **Entire unused class:** 1 instance (92 lines)
- **Duplicate operations:** 4 instances
- **Debug code:** 1 instance
- **Empty PHP tags:** 1 instance
- **Unreachable break statements:** 3 instances
- **Unused variables:** 4 instances
- **Unused meta field:** 1 instance

**Total estimated cleanup:** ~250+ lines of dead code can be safely removed

---

## 1. COMMENTED OUT CODE BLOCKS

### File: `LWREvents.php`
**Lines 60-67**
```php
//Update Checker
//$puc = Puc_v4_Factory::buildUpdateChecker(
//    'https://github.com/0x49b/lwrevents',
//    __FILE__,
//    'lwrevents',
//    1
//);
//$puc->setBranch('master');
```
**Type:** Commented out code block
**Explanation:** Old plugin update checker code that has been replaced with the newer version (v5) at lines 32-39.

---

### File: `core/LWREventsCore.php`
**Lines 24-25**
```php
//add_action( 'wp_ajax_signInUserForEvent', array($this, 'signInUserForEvent') );
//add_action( 'wp_ajax_nopriv_signInUserForEvent', array($this, 'signInUserForEvent') );
```
**Type:** Commented out code block
**Explanation:** Old AJAX action hooks that appear to have been replaced.

---

**Lines 238-239**
```php
//global $wpdb;
//$setting = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = '" . $option_name . "'", ARRAY_A);
```
**Type:** Commented out code block
**Explanation:** Old database query code replaced with `get_option()` function at line 240.

---

**Line 353**
```php
//echo json_encode($custom_posts->posts);
```
**Type:** Commented out debug code

---

**Line 343**
```php
//$round = count($tage);
```
**Type:** Commented out unused variable

---

## 2. FUNCTIONS/METHODS THAT ARE NEVER CALLED

### File: `core/LWREventsCPT.php`

#### Lines 36-43: `getEventTitle()`
```php
private function getEventTitle($eventID)
{
    global $wpdb;
    $title = $wpdb->get_row("SELECT post_title from " . $wpdb->prefix . "posts WHERE ID = '" . $eventID . "'", ARRAY_A);
    return $title['post_title'];
}
```
**Status:** Never called anywhere in codebase

---

#### Lines 46-56: `getUsersForList()`
```php
private function getUsersForList($eventID)
{
    global $wpdb;
    $users = $wpdb->get_results(" SELECT us.user_login, us.user_email, us.display_name, us.user_nicename, ev.status from " . $wpdb->prefix . "users us
                            JOIN " . $wpdb->prefix . "lwrevents_signin ev ON us.ID = ev.uid
                            JOIN " . $wpdb->prefix . "posts ps ON ev.eid = ps.ID
                            WHERE ps.post_type = 'lwrevents' AND ps.ID = " . $eventID . "", ARRAY_A);
    return $users;
}
```
**Status:** Never called anywhere in codebase

---

#### Lines 58-73: `getSignInString()`
```php
private function getSignInString($sid)
{
    switch ($sid) {
        case 2:
            return 'ja';
            break;
        case 1:
            return 'evtl';
            break;
        case 0:
            return 'nein';
            break;
    }
}
```
**Status:** Never called anywhere in codebase

---

#### Lines 75-81: `checkForComments()`
```php
private function checkForComments( $eventID )
{
    global $wpdb;
    $checkSQL = $wpdb->get_var( "SELECT COUNT(comment_ID) AS count FROM " . $wpdb->prefix . "comments WHERE comment_post_ID = '" . $eventID . "'" );
    return $checkSQL;
}
```
**Status:** Never called anywhere in codebase

---

#### Lines 83-89: `loadCommentsForEvent()`
```php
private function loadCommentsForEvent( $eventID )
{
    global $wpdb;
    $comments = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "comments WHERE comment_post_ID = '" . $eventID . "' AND comment_approved = 1 ORDER BY comment_date ASC", ARRAY_A );
    return $comments;
}
```
**Status:** Never called anywhere in codebase

---

#### Lines 167-176: `getExt()` and `setExt()`
```php
public function getExt() {
    return $this->ext;
}

public function setExt( $ext ) {
    this->ext = $ext;
}
```
**Status:** Never called anywhere in codebase. The `$ext` property is also never used.

---

### File: `core/LWREventsCore.php`

#### Lines 490-492: `eventTime()`
```php
function eventTime($eventID) {
    echo "CLOCK";
}
```
**Status:** Never called. Stub/placeholder. Actual functionality in `getEventTime()` at lines 501-519.

---

#### Lines 772-798: `getPagination()`
```php
function getPagination() {
    global $wp_query;
    $big = 999999999;
    $paginate_links = paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'next_text' => '&raquo;',
        'prev_text' => '&laquo',
        'add_args' => false,
    ));
    if ($paginate_links) : ?>
        <div class="post-pagination clearfix">
            <?php echo $paginate_links; ?>
        </div>
    <?php
    endif;
}
```
**Status:** Never called anywhere in the plugin

---

### File: `LWREvents.php`

#### Lines 231-254: `my_plugin_check_for_updates()`
```php
if (!function_exists('my_plugin_check_for_updates')) {
    function my_plugin_check_for_updates($update, $plugin_data, $plugin_file) {
        static $response = false;
        if (empty($plugin_data['UpdateURI']) || !empty($update))
            return $update;
        if ($response === false)
            $response = wp_remote_get($plugin_data['UpdateURI']);
        if (empty($response['body']))
            return $update;
        $custom_plugins_data = json_decode($response['body'], true);
        if (!empty($custom_plugins_data[$plugin_file]))
            return $custom_plugins_data[$plugin_file];
        else
            return $update;
    }
}
```
**Status:** Defined but never hooked into WordPress. Not connected to any filter or action.

---

## 3. ENTIRE UNUSED CLASS

### File: `core/LWREventsIcs.php`
**Lines 11-92: Complete `LWREventsIcs` class**
```php
class LWREventsIcs {
    const DT_FORMAT = 'Ymd\THis\Z';
    protected $properties = array();
    private $available_properties = array(
        'description',
        'dtend',
        'dtstart',
        'location',
        'summary',
        'url'
    );

    public function __construct($props) { ... }
    public function set($key, $val = false) { ... }
    public function to_string() { ... }
    private function build_props() { ... }
    private function sanitize_val($val, $key = false) { ... }
    private function format_timestamp($timestamp) { ... }
    private function escape_string($str) { ... }
}
```
**Status:** File is included at line 23 of `LWREvents.php` with comment "// ICS Creator, not in use yet". The entire class is never instantiated or used anywhere in the codebase. This is significant dead code (entire file with 92 lines).

---

## 4. DUPLICATE CODE / REDUNDANT OPERATIONS

### File: `LWREvents.php`

#### Lines 97 and 101 - Duplicate option creation
```php
// Line 97:
add_option('lwr_signin_for_users', 0);

// Line 101:
add_option('lwr_signin_for_users', false);
```
**Issue:** Same option added twice with different default values. Second call will never execute.

---

#### Lines 124 and 128 - Duplicate option deletion
```php
// Line 124:
delete_option('lwr_signin_for_users');

// Line 128:
delete_option('lwr_signin_for_users');
```
**Issue:** Same option deleted twice. Second deletion has no effect.

---

### File: `core/LWREventsCore.php`

#### Lines 684-686 and 693-696 - Duplicate database save
```php
// Lines 684-686:
$wpdb->replace($wpdb->prefix . 'options', array(
    'option_name' => 'lwr_events_contact_mail',
    'option_value' => $post['lwr_events_contact_mail'],
));

// Lines 693-696:
$wpdb->replace($wpdb->prefix . 'options', array(
    'option_name' => 'lwr_events_contact_mail',
    'option_value' => $post['lwr_events_contact_mail'],
));
```
**Issue:** The `lwr_events_contact_mail` option is saved twice with the same value. Second save is redundant.

---

## 5. DEBUG CODE LEFT IN PRODUCTION

### File: `core/LWREventsCore.php`

**Line 723**
```php
function getSettingsSelectList($list_name) {
    $setting = $this->getSettingsFromDB($list_name);

    var_dump($setting);  // Line 723 - DEBUG CODE

    if ($setting == 'DESC') {
        // ...
```
**Issue:** `var_dump()` statement will output debug information to the page when settings page is loaded.

---

## 6. EMPTY PHP TAGS

### File: `views/frontend/single-lwrevents.php`

**Line 118**
```php
<td><?php $lwr->eventMeta($post->ID, 'lwrVoraussetzung'); ?><?php ?></td>
```
**Issue:** Empty `<?php ?>` tag pair serves no purpose.

---

## 7. LOGIC ISSUES

### File: `core/LWREventsCPT.php`

**Lines 527-540**
```php
foreach ($events_meta as $key => $value) {
    if ($post->post_type == 'revision') {
        return;  // Line 529 - early return inside loop
    }
    // Rest of the code never executes if post is revision
    $value = implode(',', (array)$value);
    if (get_post_meta($post->ID, $key, false)) {
        update_post_meta($post->ID, $key, $value);
    } else {
        add_post_meta($post->ID, $key, $value);
    }
    if (!$value) {
        delete_post_meta($post->ID, $key);
    }
}
```
**Issue:** Revision check is inside the foreach loop, so function returns immediately on first iteration if post is a revision. Only first key-value pair is checked. This check should be outside the loop.

---

### File: `core/LWREventsCPT.php`

**Lines 62-70**
```php
switch ($sid) {
    case 2:
        return 'ja';
        break;  // Line 64 - unreachable
    case 1:
        return 'evtl';
        break;  // Line 67 - unreachable
    case 0:
        return 'nein';
        break;  // Line 70 - unreachable
}
```
**Issue:** All `break` statements are unreachable because each case has a `return` statement before it.

---

## 8. VARIABLES DEFINED BUT NEVER USED

### File: `core/LWREventsCPT.php`

**Line 516**
```php
$events_meta['lwrExtAllowed'] = $_POST['lwrExtAllowed'];
```
**Issue:** The `lwrExtAllowed` meta value is saved to database but never retrieved or used anywhere in the plugin code. Related to the unused `$ext` property.

---

### File: `core/LWREventsCore.php`

**Lines 48, 169-170**
```php
// Line 48:
$todayUnix = strtotime(date('d.m.Y H:i:s'));

// Lines 169-170:
$today = date('Y-m-d');
$todayUnix = strtotime(date('d.m.Y H:i:s'));
```
**Issue:** `$todayUnix` variable is defined in multiple functions (`lwrShortcodeListFuture()` and `lwrGetArchiveForCategory()`) but never used.

---

### File: `views/frontend/single-lwrevents.php`

**Line 64**
```php
$loop = new WP_Query(array('post_type' => 'lwrevents',));
```
**Issue:** `$loop` variable is created but never used. Template uses default WordPress loop with `have_posts()` instead.

---

## CLEANUP RECOMMENDATIONS

### High Priority (Remove Safely)
1. ✅ Delete `core/LWREventsIcs.php` entirely (92 lines) and remove the include from `LWREvents.php:23`
2. ✅ Remove all 10 unused functions/methods
3. ✅ Delete all commented-out code blocks (5 instances)
4. ✅ Remove `var_dump()` debug statement from `LWREventsCore.php:723`
5. ✅ Fix duplicate operations (remove second occurrences)

### Medium Priority (Logic Fixes)
6. ⚠️ Move revision check outside the foreach loop in `LWREventsCPT.php:527-540`
7. ⚠️ Remove unreachable `break` statements from switch cases
8. ⚠️ Clean up unused variables (`$todayUnix`, `$loop`)

### Low Priority (Minor Cleanup)
9. ✅ Remove empty PHP tag pair in `single-lwrevents.php:118`
10. ✅ Consider removing unused meta field `lwrExtAllowed` and related code

---

## Notes

- All identified dead code is safe to remove without affecting plugin functionality
- The revision check logic issue (item #6) is a potential bug that should be fixed
- Total cleanup would reduce codebase by ~250+ lines
- Version control (git) preserves all removed code if needed in the future

---

**End of Report**
