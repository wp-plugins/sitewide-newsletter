<?php
/*
Plugin Name: Sitewide newsletters
Description: Allows site administrators to send a newsletter to all users
Version: 0.1
Author: Chris Taylor
Author URI: http://www.stillbreathing.co.uk
Plugin URI: http://www.stillbreathing.co.uk/projects/mu-sitewide-newsletters/
*/
// when the admin menu is built
add_action('admin_menu', 'sitewide_newsletters_add_admin');

// add the admin newsletters button
function sitewide_newsletters_add_admin() {
	global $current_user;
	add_submenu_page('wpmu-admin.php', 'Sitewide newsletters', 'Sitewide newsletters', 10, 'sitewide_newsletters', 'sitewide_newsletters');
}

// build the newsletters form
function sitewide_newsletters()
{
	global $current_user, $wpdb;
	$users = $wpdb->get_var( "select count(user_email) from ".$wpdb->users." where user_activation_key = '' and spam = 0 and deleted = 0" );
	
	// if sending a newsletter
	if ( @$_POST["newsletter"] != "" && @$_POST["subject"] != "" )
	{
		$newsletter = stripslashes( trim( $_POST["newsletter"] ) );
		$subject = stripslashes( trim( $_POST["subject"] ) );
		
		$message_headers = 'From: "' . get_site_option("site_name") . '" <' . get_site_option("admin_email") . '>' . "\r\n" .
		'Reply-To: ' . get_site_option("admin_email") . '' . "\r\n" .
		'X-Mailer: PHP/' . phpversion();
		
		$emails = $wpdb->get_results( "select user_email from ".$wpdb->users." where user_activation_key = '' and spam = 0 and deleted = 0" );
		
		$failed = "";
		$sent = 0;
		
		if (@$_POST["test"] == "")
		{
		
			foreach ($emails as $email)
			{
				$e = $email->user_email;
				if ( wp_mail( $e, $subject, $newsletter, $message_headers ) )
				{
					$sent++;
				} else {
					$failed .= $e . "\r\n";
				}
			}
			
		} else {
		
			if ( wp_mail( get_site_option("admin_email"), $subject, $newsletter, $message_headers ) )
			{
				$sent++;
			} else {
				$failed .= $e . "\r\n";
			}
		
		}
		$message = "<p>Your message has been sent to " . $sent . " email addresses (" . $users . " users in total).</p>";
		if ($failed != "")
		{
			$message .= '<p>Failed addresses:</p><p><textarea cols="30" rows="12">' . $failed . '</textarea></p>';
		}
	}
	
	print '
	<div class="wrap">
	
	<h2>Sitewide Newsletter</h2>
	
	' . $message . '
	
	<p>Enter your newsletter below which will be emailed to ' . $users . ' users. The email will appear to the recipient as coming from "' . get_site_option("site_name") . ' &lt;' . get_site_option("admin_email") . '&gt;".</p>
	
	<form action="wpmu-admin.php?page=sitewide_newsletters" method="post">
	
		<fieldset>
		
		<legend>Send a newsletter</legend>
			
		<p><label for="subject" style="float: left;width: 15%;">Subject</label><input type="text" name="subject" id="subject" style="width: 80%" /></p>
			
		<p><label for="newsletter" style="float: left;width: 15%;">Newsletter</label><textarea name="newsletter" id="newsletter" cols="30" rows="6" style="width: 80%"></textarea></p>
		
		<p><label for="subject" style="float: left;width: 15%;">Test newsletter</label><input type="checkbox" name="test" id="test" /> This will just send the newsletter to ' . get_site_option("admin_email") . '</p>
		
		<p><label for="send_sitewide_newsletter" style="float: left;width: 15%;">Send newsletter</label><input type="submit" name="send_sitewide_newsletter" id="send_sitewide_newsletter" value="Send newsletter" class="button" /></p>
		
		</fieldset>

	</form>
	
	</div>
	';
}
?>