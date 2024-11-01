<?php

require_once '../../../wp-config.php';

if(!function_exists('iif')) {
	function iif($argument, $true, $false=FALSE) {
		if($argument) { return $true; } else { return $false; }
	}
}

global $wpdb;

$toGet = array('like', 'dislike');

if($_POST['id'] AND $_POST['rating'] AND !$_COOKIE['postrated'.$_GET['id']]) {
	$r = $wpdb->get_var("SELECT rating_".$_POST['rating']." FROM ".$wpdb->posts." WHERE ID = '".$_POST['id']."'");
	$newRating = $r+1;
	$wpdb->query("UPDATE ".$wpdb->posts." SET rating_".$_POST['rating']." = '".$newRating."' WHERE ID = '".$_POST['id']."'");
	setcookie('postrated'.$_POST['id'], time(), time()+3600*24*365, '/');
	$_COOKIE['postrated'.$_POST['id']] = time();
}


/* Show the new ratings */

$i = 0;
foreach($toGet AS $g) {
	$i++;
	$r[$g] = $wpdb->get_var("SELECT rating_".$g." FROM ".$wpdb->posts." WHERE ID = '".$_POST['id']."'");
	if(isset($_COOKIE['postrated'.$_POST['id']]) OR $user_level > 9) { echo $r[$g].' '.$g.iif($r[$g] < 2, 's').' this post'; }
	else { echo '<a style="cursor: pointer;" onclick="loadContent(this, \''.$g.'\', \''.$_POST['id'].'\');">'.ucfirst($g).'</a>'; }
	if($i == 1) { echo ' - '; }
}

?>