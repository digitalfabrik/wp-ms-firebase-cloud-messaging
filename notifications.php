<?php

function write_firebase_notification() {
	//send message if nonce is valid
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'ig-fb-send-nonce' ) && current_user_can('publish_pages') ) {
		$languages = icl_get_languages();
		$items = array();
		foreach( $languages as $key => $value ) {
			$items[$key]  = array( 'title' => $_POST['pn-title_'.$key], 'message' => $_POST['pn-message_'.$key], 'lang' => $key, 'translate' => $_POST['pn-translate'], 'group' => $_POST['fbn_groups'] );
		}
		$fcm = new FirebaseNotificationsService();
		$fcm->translate_send_notifications( $items );
	}

	wp_enqueue_style( 'ig-fb-style-send', plugin_dir_url(__FILE__) . '/css/send.css' );
	wp_enqueue_script( 'ig-fb-js-send', plugin_dir_url(__FILE__) . '/js/send.js' );
	// display form
	require_once( __DIR__ . '/templates/notification.php');
	echo write_firebase_notification_form();
}

function fcm_sent_list_html( $lang ) {
	$fcmdb = New FirebaseNotificationsDatabase();
	$messages = $fcmdb->messages_by_language( $lang , $amount = 10 );
	$foo = "<table>";
	foreach( $messages as $message ){
		$foo .= "<tr><td>" . $message['request']['notification']['title'] . ", " .  $message['timestamp'] . "</td></tr>";
	}
	$foo .= "</table>";
	return $foo;
}

?>
