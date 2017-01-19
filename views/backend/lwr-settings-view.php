<?php
/**
 * Created by PhpStorm.
 * User: Florian Thiévent
 * Date: 22.11.2016
 * Time: 16:02
 */

    include_once(LWR_PLUGIN_PATH . '/core/LWREventsCore.php'); //Core Include
    $lwr = new LWREventsCore();
    if ($_POST) {
        $lwr->saveSettingsInDB($_POST);
    }
?>

<div class="wrap">
    <h2>LWR Events Einstellungen</h2>
    <hr/>
    <form name="lwrevents_settings" method="post" action="./edit.php?post_type=lwrevents&page=lwr-settings">
        <table>
            <tr>
                <td>Sortierung Liste zukünftige Anlässe</td>
                <td>
                    <select name="lwr_sort_list_future">
                        <?php print($lwr->getSettingsSelectList('lwr_sort_list_future')); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Sortierung Liste alle Anlässe</td>
                <td>
                    <select name="lwr_sort_list">
                        <?php print($lwr->getSettingsSelectList('lwr_sort_list')); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><?php submit_button($text = 'speichern', $type = 'primary', $name = 'lwrevents_submit', $wrap = true, $other_attributes = null); ?></td>
            </tr>
        </table>
    </form>
</div>