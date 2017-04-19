<?php

/**
 * Created by PhpStorm.
 * User: Florian
 * Date: 19.08.16
 * Time: 09:03
 */
class LWREventsCPT {

	//Tage in der Checkbox der Metabox
	private $mo = "";
	private $di = "";
	private $mi = "";
	private $do = "";
	private $fr = "";
	private $sa = "";
	private $so = "";
	private $ext = "";

	/**
	 * @return string
	 */
	public function getExt() {
		return $this->ext;
	}

	/**
	 * @param string $ext
	 */
	public function setExt( $ext ) {
		$this->ext = $ext;
	}

	/**
	 * LWREventsCPT constructor.
	 */
	function __construct() {
		add_filter( 'manage_lwrevents_posts_columns', array( $this, 'lwr_event_table_head' ), 10, 2 );
		add_action( 'manage_lwrevents_posts_custom_column', array( $this, 'lwr_event_table_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'lwr_event_remove_row_actions' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'lwr_events_add_mbx' ) );
		add_action( 'save_post', array( $this, 'lwr_save_events_mbx' ), 1, 2 );
		add_action( 'admin_head', array( $this, 'check_post_type_and_remove_media_buttons' ) );

		//If user clicks Download, generate it and load it
		if ( $_GET['download'] == 1 && $_GET['eid'] != '' ) {
			$this->generateExcel( $_GET['eid'] );
		}
	}

	/**
	 * Update Row Actions
	 *
	 * @param $actions
	 *
	 * @return mixed
	 */
	function lwr_event_remove_row_actions( $actions ) {
		if ( get_post_type() === 'lwrevents' ) { //remove "slider" post_type to whatever post_type you want the row-actions to hide
			unset( $actions['view'] );    // view
			unset( $actions['inline hide-if-no-js'] );  // quick edit
			unset( $actions['edit'] );    // edit
			unset( $actions['trash'] );    // trash
		}

		//return $actions array
		return $actions;
	}

	/**
	 * Customize Table Heads in Admin Backend
	 *
	 * @param $defaults
	 *
	 * @return mixed
	 */
	public static function lwr_event_table_head( $defaults ) {
		$defaults['lwrOrt']      = 'Ort';
		$defaults['lwrDatum']    = 'am/von';
		$defaults['lwrAnmelden'] = 'Anmelden bis';
		$defaults['lwrMaxTN']    = 'max TN';
		$defaults['lwrOK']       = 'OK';
		$defaults['lwrAction']   = 'TN Liste';
		unset( $defaults['date'] );

		return $defaults;
	}

	/**
	 * Populate Customized Table in Admin with Data
	 *
	 * @param $column_name
	 * @param $post_id
	 */
	public function lwr_event_table_content( $column_name, $post_id ) {
		if ( $column_name == 'lwrOrt' ) {
			echo get_post_meta( $post_id, 'lwrOrt', true );
		}
		if ( $column_name == 'lwrDatum' ) {
			echo get_post_meta( $post_id, 'lwrDatumVon', true );
		}
		if ( $column_name == 'lwrAnmelden' ) {
			echo get_post_meta( $post_id, 'lwrAnmelden', true );
		}
		if ( $column_name == 'lwrMaxTN' ) {
			echo get_post_meta( $post_id, 'lwrMaxTN', true );
		}
		if ( $column_name == 'lwrOK' ) {
			echo get_post_meta( $post_id, 'lwrOK', true );
		}
		if ( $column_name == 'lwrAction' ) {
			echo "<a href='./edit.php?post_type=lwrevents&download=1&eid=" . $post_id . "'><i class=\"fa fa-file-excel-o\" aria-hidden=\"true\"></i></a>";
		}

	}

	public function generateExcel( $eventID ) {

		$lwrcore    = new LWREventsCore();
		$eventtitle = $this->getEventTitle( $eventID );
		$eventdate  = $lwrcore->getEventMeta( $eventID, 'lwrDatumVon' );
		$eventusers = $this->getUsersForList( $eventID );

		// New Excel Object
		$excel = new PHPExcel();
		header( 'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' );
		header( 'Content-Disposition: attachment;filename="' . $eventtitle . '.xlsx"' );
		header( 'Cache-Control: max-age=0' );

		// Set File Properties
		$excel->getProperties()
		      ->setCreator( "LWR Events" )
		      ->setLastModifiedBy( "LWR Events" )
		      ->setTitle( $eventtitle );

		// Write Header Line
		$excel->setActiveSheetIndex( 0 )
		      ->mergeCells( 'A1:I1' )
		      ->setCellValue( 'A1', "Teilnehmerliste für: " . $eventtitle . " am " . $eventdate );

		$excel->getActiveSheet()
		      ->setTitle( "Liste der Anmeldungen" );

		// Write HeaderRow
		$excel->setActiveSheetIndex( 0 )
		      ->setCellValue( 'A2', 'Name' )
		      ->setCellValue( 'B2', 'Spitzname' )
		      ->setCellValue( 'C2', 'E-Mail' )
		      ->setCellValue( 'D2', 'Status' );

		//Write Data

		for ( $i = 0; $i < count( $eventusers ); $i ++ ) {
			$d = $i + 3;
			$excel->setActiveSheetIndex( 0 )
			      ->setCellValue( "A" . $d, $eventusers[ $i ]['display_name'] )
			      ->setCellValue( "B" . $d, $eventusers[ $i ]['user_nicename'] )
			      ->setCellValue( "C" . $d, $eventusers[ $i ]['user_email'] )
			      ->setCellValue( "D" . $d, $this->getSignInString( $eventusers[ $i ]['status'] ) );
		}

		//Autosize on Column
		foreach (
			range( 'A:I', $excel->getActiveSheet()
			                    ->getHighestDataColumn() ) as $col
		) {
			$excel->getActiveSheet()
			      ->getColumnDimension( $col )
			      ->setAutoSize( true );
		}

		//Add Comments to 2nd Sheet
		if ( $this->checkForComments( $eventID ) >= 1 ) {

			$comments = $this->loadCommentsForEvent( $eventID );
			$ci       = 2;
			$excel->createSheet();
			$excel->setActiveSheetIndex( 1 )
			      ->mergeCells( 'A1:I1' )
			      ->setCellValue( 'A1', "Kommentare für: " . $eventtitle . " am " . $eventdate );
			$excel->getActiveSheet()
			      ->setTitle( "Kommentare zu den Anmeldungen" );

			foreach ( $comments as $comment ) {
				$excel->setActiveSheetIndex( 1 )
				      ->setCellValue( "A" . $ci, $comment['comment_author'] )
				      ->setCellValue( "B" . $ci, $comment['comment_date'] )
				      ->setCellValue( "C" . $ci, $comment['comment_content'] );
				$ci ++;
			}

			foreach (
				range( 'A:I', $excel->getActiveSheet()
				                    ->getHighestDataColumn() ) as $col
			) {
				$excel->getActiveSheet()
				      ->getColumnDimension( $col )
				      ->setAutoSize( true );
			}

		}
		$objWriter = PHPExcel_IOFactory::createWriter( $excel, 'Excel2007' );
		$objWriter->save( 'php://output' );
	}

	private function checkForComments( $eventID ) {
		global $wpdb;
		$checkSQL = $wpdb->get_var( "Select COUNT(comment_ID) as count from " . $wpdb->prefix . "comments WHERE comment_post_ID = '" . $eventID . "'" );

		return $checkSQL;
	}

	private function loadCommentsForEvent( $eventID ) {
		global $wpdb;
		$comments = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "comments WHERE comment_post_ID = '" . $eventID . "' AND comment_approved = 1 ORDER BY comment_date ASC", ARRAY_A );

		return $comments;
	}

	private function getEventTitle( $eventID ) {
		global $wpdb;

		$title = $wpdb->get_row( "SELECT post_title from " . $wpdb->prefix . "posts WHERE ID = '" . $eventID . "'", ARRAY_A );

		return $title['post_title'];

	}

	private function getUsersForList( $eventID ) {
		global $wpdb;

		$users = $wpdb->get_results( " SELECT us.user_login, us.user_email, us.display_name, us.user_nicename, ev.status from " . $wpdb->prefix . "users us
                                JOIN " . $wpdb->prefix . "lwrevents_signin ev ON us.ID = ev.uid
                                JOIN " . $wpdb->prefix . "posts ps ON ev.eid = ps.ID
                                WHERE ps.post_type = 'lwrevents' AND ps.ID = " . $eventID . "", ARRAY_A );

		return $users;
	}

	private function getSignInString( $sid ) {

		switch ( $sid ) {
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

	/**
	 * @return string
	 */
	public function getMo() {
		return $this->mo;
	}

	/**
	 * @param string $mo
	 */
	public function setMo( $mo ) {
		$this->mo = $mo;
	}

	/**
	 * @return string
	 */
	public function getDi() {
		return $this->di;
	}

	/**
	 * @param string $di
	 */
	public function setDi( $di ) {
		$this->di = $di;
	}

	/**
	 * @return string
	 */
	public function getMi() {
		return $this->mi;
	}

	/**
	 * @param string $mi
	 */
	public function setMi( $mi ) {
		$this->mi = $mi;
	}

	/**
	 * @return string
	 */
	public function getDo() {
		return $this->do;
	}

	/**
	 * @param string $do
	 */
	public function setDo( $do ) {
		$this->do = $do;
	}

	/**
	 * @return string
	 */
	public function getFr() {
		return $this->fr;
	}

	/**
	 * @param string $fr
	 */
	public function setFr( $fr ) {
		$this->fr = $fr;
	}

	/**
	 * @return string
	 */
	public function getSa() {
		return $this->sa;
	}

	/**
	 * @param string $sa
	 */
	public function setSa( $sa ) {
		$this->sa = $sa;
	}

	/**
	 * @return string
	 */
	public function getSo() {
		return $this->so;
	}

	/**
	 * @param string $so
	 */
	public function setSo( $so ) {
		$this->so = $so;
	}

	/**
	 * Remove Mediabutton from Editor
	 */
	function check_post_type_and_remove_media_buttons() {
		global $current_screen;
		if ( 'lwrevents' == $current_screen->post_type ) {
			remove_action( 'media_buttons', 'media_buttons' );
		}
	}

	/**
	 * Custom Post type to add a new Event
	 */
	public static function lwr_events_cpt_config() {

		// Set UI labels for Custom Post Type
		$labels = array(
			'name'               => __( 'LWR Events' ),
			'singular_name'      => __( 'LWR Event' ),
			'menu_name'          => __( 'LWR Events' ),
			'parent_item_colon'  => __( 'Parent Movie' ),
			'all_items'          => __( 'Alle Anlässe' ),
			'view_item'          => __( 'Anlass Vorschau' ),
			'add_new_item'       => __( 'Neuer Anlass hinzufügen' ),
			'add_new'            => __( 'Anlass hinzufügen' ),
			'edit_item'          => __( 'Anlass bearbeiten' ),
			'update_item'        => __( 'Anlass aktualisieren' ),
			'search_items'       => __( 'Anlass suchen' ),
			'not_found'          => __( 'Nichts gefunden' ),
			'not_found_in_trash' => __( 'Nichts gefunden im Papierkorb' ),
		);

		// Set other options for Custom Post Type
		$args = array(
			'label'               => __( 'lwrevents' ),
			'description'         => __( 'LWR Events ist ein Plugin zur organisation von Events' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'comments', 'excerpt' ),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 95,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'page',
		);

		// Registering your Custom Post Type
		register_post_type( 'lwrevents', $args );
		register_taxonomy( "Sportart", 'lwrevents', array(
			"hierarchical"   => true,
			"label"          => "Sportarten",
			"singular_label" => "Sportart",
			"rewrite"        => true
		) );
	}

	/**
	 * Add the Metabox to the Post
	 */
	function lwr_events_add_mbx() {
		add_meta_box( 'lwr_events_mbx', 'Anlass Details', array(
			$this,
			'lwr_events_mbx'
		), 'lwrevents', 'normal', 'default' );
	}

	/**
	 * Extract all Days from Metaarray
	 *
	 * @param $tagearray
	 */
	function extractDaysFromArray( $tagearray ) {

		$tage = explode( ',', $tagearray );
		//$round = count($tage);

		foreach ( $tage as $tag ) {
			switch ( $tag ) {
				case 'mo':
					$this->setMo( 'checked' );
					break;
				case 'di':
					$this->setDi( 'checked' );
					break;
				case 'mi':
					$this->setMi( 'checked' );
					break;
				case 'do':
					$this->setDo( 'checked' );
					break;
				case 'fr':
					$this->setFr( 'checked' );
					break;
				case 'sa':
					$this->setSa( 'checked' );
					break;
				case 'so':
					$this->setSo( 'checked' );
					break;
			}
		}
	}

	/**
	 * Metabox Main Config
	 */
	function lwr_events_mbx() {

		global $post;

		// Noncename needed to verify where the data originated
		echo '<input type="hidden" name="eventmeta_noncename" id="eventmeta_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';

		// Get the location data if its already been entered
		$lwrOrt           = get_post_meta( $post->ID, 'lwrOrt', true );
		$lwrDatumVon      = get_post_meta( $post->ID, 'lwrDatumVon', true );
		$lwrDatumBis      = get_post_meta( $post->ID, 'lwrDatumBis', true );
		$lwrZeitVon       = get_post_meta( $post->ID, 'lwrZeitVon', true );
		$lwrZeitBis       = get_post_meta( $post->ID, 'lwrZeitBis', true );
		$lwrAnmelden      = get_post_meta( $post->ID, 'lwrAnmelden', true );
		$lwrAnmeldenZeit  = get_post_meta( $post->ID, 'lwrAnmeldenZeit', true );
		$lwrMaxTN         = get_post_meta( $post->ID, 'lwrMaxTN', true );
		$lwrOK            = get_post_meta( $post->ID, 'lwrOK', true );
		$lwrMailOK        = get_post_meta( $post->ID, 'lwrMailOK', true );
		$lwrVoraussetzung = get_post_meta( $post->ID, 'lwrVoraussetzung', true );
		$lwrAusruestung   = get_post_meta( $post->ID, 'lwrAusruestung', true );

		// Read Days from Array to set checked or not
		$this->extractDaysFromArray( get_post_meta( $post->ID, 'lwrTage', true ) );
		// Echo out the field
		echo '
        <table>
            <tr>
                <td><label for="lwrOrt">Ort</label></td><td colspan="3"><input type="text" class="regular-text" name="lwrOrt" id="lwrOrt" value="' . $lwrOrt . '" /></td>
            </tr>
            <tr>
                <td><label for="lwrDatumVon">Datum von - bis </label></td>
                <td><input type="text" name="lwrDatumVon" id="lwrDatumVon" value="' . $lwrDatumVon . '" /> &nbsp;&nbsp;-&nbsp;&nbsp;</td>
                <td><input type="text"  name="lwrDatumBis" id="lwrDatumBis" value="' . $lwrDatumBis . '" /></td>
                <td><span class="description">Wird nur ein Startdatum angegeben ist der Anlass nur an diesem Tag.</span></td>
            </tr>
            <tr>
                <td><label for="lwrZeitVon">Zeit von - bis </label></td>
                <td><input type="text" name="lwrZeitVon" id="lwrZeitVon" value="' . $lwrZeitVon . '" /> &nbsp;&nbsp;-&nbsp;&nbsp;</td>
                <td><input type="text"  name="lwrZeitBis" id="lwrZeitBis" value="' . $lwrZeitBis . '" /></td>
            </tr>
            <tr>
                <td><label>Tage</label></td>
                <td colspan="3">
                    <label><input type="checkbox" name="lwrTage[]" value="mo" ' . $this->getMo() . ' /> Montag</label>
                    <label><input type="checkbox" name="lwrTage[]" value="di" ' . $this->getDi() . '/> Dienstag</label>
                    <label><input type="checkbox" name="lwrTage[]" value="mi" ' . $this->getMi() . '/> Mittwoch</label>
                    <label><input type="checkbox" name="lwrTage[]" value="do" ' . $this->getDo() . '/> Donnerstag</label>
                    <label><input type="checkbox" name="lwrTage[]" value="fr" ' . $this->getFr() . '/> Freitag</label>
                    <label><input type="checkbox" name="lwrTage[]" value="sa" ' . $this->getSa() . '/> Samstag</label>
                    <label><input type="checkbox" name="lwrTage[]" value="so" ' . $this->getSo() . '/> Sonntag</label>
                </td>
            </tr>
            <tr>
                <td><label for="lwrAnmelden">Anmelden bis</label></td>
                <td><input type="text" name="lwrAnmelden" id="lwrAnmelden" value="' . $lwrAnmelden . '" /></td>
                <td colspan="2"> Wird dieses Feld nicht ausgefüllt, wird das Datum des Anlasses übernommen.<!--Zeit<input type="text" name="lwrAnmeldenZeit" id="lwrAnmeldenZeit" value="' . $lwrAnmeldenZeit . '" /--> </td>
            </tr>
            <tr>
                <td><label for="lwrMaxTN">max. Teilnehmer</label></td><td colspan="3"><input type="text" name="lwrMaxTN" id="lwrMaxTN" value="' . $lwrMaxTN . '" /></td>
            </tr>
            <tr>
                <td><label for="lwrOK">Namen OK</label></td><td colspan="3"><input type="text" class="regular-text" name="lwrOK" id="lwrOK" value="' . $lwrOK . '" /></td>
            </tr>
            <tr>
                <td><label for="lwrMailOK">E-Mail OK</label></td>
                <td colspan="2"><input type="email" class="regular-text" name="lwrMailOK" id="lwrMailOK" value="' . $lwrMailOK . '" /></td>
                <td><span class="description">Sollen die Mails an mehrere Adressen gesendet werden, diese kommasepariert eingeben</span></td>
            </tr>
            <tr>
                <td><label for="lwrVoraussetzung">Voraussetzung</label></td>
                <td colspan="3">
                <textarea id="lwrVoraussetzung" name="lwrVoraussetzung" cols="46" rows="4">' . $lwrVoraussetzung . '</textarea>
                </td>
            </tr>
            <tr>
                <td><label for="lwrAusruestung">Ausrüstung</label></td>
                <td colspan="3">
                <textarea id="lwrAusruestung" name="lwrAusruestung" cols="46" rows="4">' . $lwrAusruestung . '</textarea>
                </td>
            </tr>
        </table>
        ';

	}

	/**
	 * Save the Metabox Data
	 *
	 * @param $post_id
	 * @param $post
	 *
	 * @return int|void
	 */
	function lwr_save_events_mbx( $post_id, $post ) {

		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( ! wp_verify_nonce( $_POST['eventmeta_noncename'], plugin_basename( __FILE__ ) ) ) {
			return $post->ID;
		}

		// Is the user allowed to edit the post or page?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		// OK, we're authenticated: we need to find and save the data
		// We'll put it into an array to make it easier to loop though.

		$events_meta['lwrOrt'] = $_POST['lwrOrt'];

		$events_meta['lwrDatumVon']        = $_POST['lwrDatumVon'];
		$events_meta['lwrDatumVonSQL']     = $this->transferDateToSQL( $_POST['lwrDatumVon'] );
		$events_meta['lwrDatumZeitVonUnix'] = strtotime( $this->transferDateToSQL( $_POST['lwrDatumVon'] ) . ' ' . $_POST['lwrZeitVon'] . ':00' );
		$events_meta['lwrDatumBis']        = $_POST['lwrDatumBis'];
		$events_meta['lwrDatumBisSQL']     = $this->transferDateToSQL( $_POST['lwrDatumBis'] );
		$events_meta['lwrDatumZeitBisUnix'] = strtotime( $this->transferDateToSQL( $_POST['lwrDatumBis'] ) . ' ' . $_POST['lwrZeitBis'] . ':00' );


		$events_meta['lwrTage']    = $_POST['lwrTage'];
		$events_meta['lwrZeitVon'] = $_POST['lwrZeitVon'];
		$events_meta['lwrZeitBis'] = $_POST['lwrZeitBis'];

		$events_meta['lwrAnmeldenZeit']  = $_POST['lwrAnmeldenZeit'];
		$events_meta['lwrOK']            = $_POST['lwrOK'];
		$events_meta['lwrMailOK']        = $_POST['lwrMailOK'];
		$events_meta['lwrMaxTN']         = $_POST['lwrMaxTN'];
		$events_meta['lwrVoraussetzung'] = $_POST['lwrVoraussetzung'];
		$events_meta['lwrAusruestung']   = $_POST['lwrAusruestung'];
		$events_meta['lwrExtAllowed']    = $_POST['lwrExtAllowed'];

		if ( $_POST['lwrAnmelden'] != '' ) {
			$events_meta['lwrAnmelden'] = $_POST['lwrAnmelden'];
		} else {
			$events_meta['lwrAnmelden'] = $_POST['lwrDatumVon'];
		}


		// Add values of $events_meta as custom fields

		foreach ( $events_meta as $key => $value ) {          // Cycle through the $events_meta array!
			if ( $post->post_type == 'revision' ) {
				return;
			}     // Don't store custom data twice
			$value = implode( ',', (array) $value );           // If $value is an array, make it a CSV (unlikely)
			if ( get_post_meta( $post->ID, $key, false ) ) {    // If the custom field already has a value
				update_post_meta( $post->ID, $key, $value );
			} else {                                        // If the custom field doesn't have a value
				add_post_meta( $post->ID, $key, $value );
			}
			if ( ! $value ) {
				delete_post_meta( $post->ID, $key );
			} // Delete if blank
		}

	}

	/**
	 * Transfer date from humanreadable Date to SQL Date
	 *
	 * @param $date
	 *
	 * @return string
	 */
	function transferDateToSQL( $date ) {
		$date    = explode( '.', $date );
		$sqlDate = $date[2] . '-' . $date[1] . '-' . $date[0];

		return $sqlDate;
	}

}