<?php
/**
 * User: Florian Thiévent
 * Date: 22.11.2016
 * Time: 16:02
 */

include_once( LWR_PLUGIN_PATH . '/core/LWREventsCore.php' ); //Core Include
$lwr = new LWREventsCore();
if ( $_POST ) {
	if ( $lwr->saveSettingsInDB( $_POST ) ) {
		print( '<div class="notice notice-success"><p>Einstellungen gespeichert.</p></div>' );
	}
}
?>
<div class="wrap">
    <h2>LWR Events Einstellungen</h2>
    <hr/>
    <form name="lwrevents_settings" method="post" action="./edit.php?post_type=lwrevents&page=lwr-settings">
        <table>
            <tr>
                <td colspan="5"><h3>Zukünftige Anlässe</h3></td>
            </tr>
            <tr>
                <td>Sortierung</td>
                <td colspan="2"><select name="lwr_sort_list_future">
						<?php print( $lwr->getSettingsSelectList( 'lwr_sort_list_future' ) ); ?>
                    </select></td>
                <td>Max. Anzahl</td>
                <td colspan="2"><input type="number" max="999" class="small-text" name="lwr_future_max" value="<?php print( $lwr->getSettingsFromDB( 'lwr_future_max' ) ); ?>"/></td>
            </tr>

            <tr>
                <td colspan="5"><h3>Alle Anlässe</h3></td>
            </tr>
            <tr>
                <td>Sortierung</td>
                <td  colspan="2"><select name="lwr_sort_list">
						<?php print( $lwr->getSettingsSelectList( 'lwr_sort_list' ) ); ?>
                    </select></td>
                <td>Max. Anzahl</td>
                <td colspan="2"><input type="number" max="999" class="small-text" name="lwr_all_max" value="<?php print( $lwr->getSettingsFromDB( 'lwr_all_max' ) ); ?>"/></td>
            </tr>

            <tr>
                <td colspan="5"><h3>Archiv Seiten</h3></td>
            </tr>
            <tr>
                <td>Sortierung</td>
                <td  colspan="2"><select name="lwr_sort_list_archive">
						<?php print( $lwr->getSettingsSelectList( 'lwr_sort_list_archive' ) ); ?>
                    </select></td>
                <td>Max. Anzahl</td>
                <td colspan="2"><input type="number" max="999" class="small-text" name="lwr_archiv_max" value="<?php print( $lwr->getSettingsFromDB( 'lwr_archiv_max' ) ); ?>"/></td>
            </tr>
            <tr>
                <td><h3>Diverses</h3></td>
            </tr>
            <tr>
                <td colspan="2">Kontakt E-Mail</td>
                <td colspan="3"><input name="lwr_events_contact_mail" type="text" class="regular-text" value="<?php print( $lwr->getSettingsFromDB( 'lwr_events_contact_mail' ) ); ?>"></td>
            </tr>
            <tr>
                <td colspan="2">Hinweis keine Anlässe</td>
                <td colspan="3"><input name="lwr_empty_events" type="text" class="regular-text" value="<?php print( $lwr->getSettingsFromDB( 'lwr_empty_events' ) ); ?>"></td>
            </tr>
            <tr>
                <td colspan="2">Anmeldung öffentlich</td>
                <td><input name="lwr_signin_for_users" type="checkbox" value="1" <?php print( $lwr->getSettingsFromDB( 'lwr_signin_for_users' ) ); ?>/></td>
            </tr>
            <tr><td colspan="5"><br/><br/></td></tr>
            <tr>
                <td><button type="submit" name="submit" id="submit" class="button button-primary">speichern</button></td>
                <td colspan="4"><button type="reset" name="secondary" id="secondary" class="button button-secondary">zurücksetzen</button></td>
            </tr>
        </table>
    </form>

</div>

