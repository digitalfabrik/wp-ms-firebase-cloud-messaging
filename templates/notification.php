
<?php
function write_firebase_notification_form() {

	if( get_site_option( 'fbn_force_network_settings' ) == '2' || ( get_site_option( 'fbn_force_network_settings' ) == '1' && get_blog_option( $blog_id, 'fbn_use_network_settings' ) == '1' ) ) {
		$groups = get_site_option( 'fbn_groups' );
	} else {
		$groups = get_blog_option( get_current_blog_id(), 'fbn_groups' );
	}

	$select = "<select name='fbn_groups'>";
	foreach( explode( ' ', $groups ) as $group ) {
		$select .= "<option>$group</option>";
	}
	$select .= "</select>";

	$header = "<h1>".get_admin_page_title()."</h1>
<form method='post'>
	".wp_nonce_field( 'ig-fb-send-nonce' )."
    <div class='notification-editor'>
        <p>".__('Please fill in the title and message for the red underlined language. Optionally, translations can be filled in as well.', 'firebase-notifications')."</p>
        <p>".__('Languages without manual translation receive:', 'firebase-notifications')."</p>
        <fieldset>
            <ul>
                ".( class_exists('TranslationService') ? "<li><input type='radio' id='at' name='pn-translate' value='at' checked='checked'><label for='at'> ".__('Automatic translation', 'firebase-notifications')."</label></li>":"")."
                <li><input type='radio' id='no' name='pn-translate' value='no' ".( !class_exists('TranslationService') ? " checked='checked'" : "")."><label for='no'> ".__('No message', 'firebase-notifications')."</label></li>
                <li><input type='radio' id='or' name='pn-translate' value='or'><label for='or'> ".__('Message in original language (marked red)', 'firebase-notifications')."</label></li>
            </ul>
        </fieldset>
        <p>".__('Groups', 'firebase-notifications').": $select</p>
        <button>".__('Send Notification', 'firebase-notifications')."</button>
		<div class='pn-tabs'>
";

	$footer = "
		</div>
	</div>
</form>
";

	$tabs = "";
	$languages = icl_get_languages();
	foreach( $languages as $key => $value ) {
		$default = ($value['active'] == "1" ? "deflang" : $value['code'] );
		$tabs .= "
            <div class='pn-tab'>
                <input type='radio' id='tab-".$value['code']."' name='tab-group-1'" . ($value['active'] == "1" ? " checked" : "" ) . ">
                <label for='tab-".$value['code']."'>".$value['translated_name']."</label>
                <div class='pn-clear'></div>
                <div class='pn-content'>
                    Fooooo
                    <table class='tabtable'>
                        <tr><td>".__('Title', 'firebase-notifications')."</td><td><input name='pn-title_".$value['code']."' type='text' class='pn-title' maxlength='50'></td></tr>
                        <tr><td>".__('Message', 'firebase-notifications')."</td><td><textarea name='pn-message_".$value['code']."' class='pn-message' maxlength='140'></textarea></td></tr>
                        <tr><td colspan='2'>".fcm_sent_list_html( $value['code'] )."</tr>
                    </table>
                </div> 
			</div>
";
	}
	return $header.$tabs.$footer;
}
?>