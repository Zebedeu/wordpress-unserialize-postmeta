<?php
/*
Plugin Name: Unserialize Post Meta
Description: Loops through posts with specified post meta and extracts that data out of serialied arrays into its own database rows. Meta keys must be specified manually in the php file. Activate the plugin, the code will run upon plugin activation, then deactivate the plugin. Check the database to see if the desired effect was achieved. Particularly useful if you stored post meta using WPAlchemy with WPALCHEMY_MODE_ARRAY and need to convert that existing post meta to WPALCHEMY_MODE_EXTRACT.
Version: 0.1
Author: Grant Kinney
License: GPL2
*/
/**
 * Unserialize Post Meta plugin for WordPress
 *
 * Note: only acts upon post meta when plugin is activated
 *	
 * @package WordPress
 * @subpackage WPAlchemy
 * @author Grant Kinney
 * @version 0.1
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * 
 */

register_activation_hook( __FILE__, 'cc_unserialize_postmeta' );

/**
 * cc_unserialize_postmeta function.
 * Uses specifed meta keys to find serialzed post meta, unserialize it, and store it back in its own row in the database.
 * @staticvar string $serialized_metakey
 * @staticvar string $extract_metakey
 * @access public
 * @return void
 */
function cc_unserialize_postmeta()
{
	// Specify which metakey that holds the serialized data and which metakey you want to extract
	/**
	 * serialized_metakey
	 * 
	 * (default value: '_serialized_metakey')
	 * 
	 * @var string
	 * @access public
	 */
	$serialized_metakey = '_serialized_metakey';
	/**
	 * extract_metakey
	 * 
	 * (default value: '_extracted_metakey')
	 * 
	 * @var string
	 * @access public
	 */
	$extract_metakey = '_extracted_metakey';
	
	// Get all posts with post meta that have the specified meta key
	$posts_array = get_posts( array(
		'meta_key' => $serialized_metakey,
		'posts_per_page' => -1,
		'nopaging' => true,
		'post_status' => 'any',
		)
	);
	
	// Loop through posts, extract requested post meta, and send it back to the database in it's own row!
	// Keep a list of updated posts and check that the unserialized postmeta has been stored
	$serialzed_post_list = array();
	$unserialized_post_list = array();
	foreach ($posts_array as $serialized_post)
	{
		$serialized_post_id = $serialized_post->ID;
		$serialized_postmeta = get_post_meta( $serialized_post_id, $serialized_metakey, true );
		if ( isset($serialized_postmeta[$extract_metakey]) )
		{
			$extracted_postmeta = $serialized_postmeta[$extract_metakey];
			update_post_meta( $serialized_post_id, $extract_metakey, $extracted_postmeta );
			$serialized_post_list[] = $serialized_post_id;
		}
		
		$unserialized_postmeta = get_post_meta( $serialized_post_id, $extract_metakey, true );
		if ( $unserialized_postmeta && $extracted_postmeta === $unserialized_postmeta )
		{
			$unserialized_post_list[] = $serialized_post_id;
		}
	}
	
	$post_check = array_diff($serialized_post_list, $unserialized_post_list);
	
	// Check to see if post meta was updated and store appropriate admin notice	in options table
	if ( 0 === count($posts_array) )
	{
		$message = '<div class="error">';
			$message .= '<p>';
				$message .= __( 'Error: no specified postmeta was found. Deactivate the plugin, specify different meta keys, then activate the plugin to try again.' );
			$message .= '</p>';
		$message .= '</div><!-- /.error -->';
	}
	elseif ( empty( $post_check ) )
	{	
		$message = '<div class="updated">';
			$message .= '<p>';
				$message .= __( 'All specified postmeta was unserialzed and saved back to the database. You can now deactivate the plugin.' );
			$message .= '</p>';
		$message .= '</div><!-- /.updated -->';
	}
	else
	{
		$message = '<div class="error">';
			$message .= '<p>';
				$message .= __( 'Error: not all postmeta was unserialized' );
			$message .= '</p>';
		$message .= '</div><!-- /.error -->';		
	}
	
	update_option( 'cc_unserialize_notice', $message );
}

add_action( 'admin_notices', 'cc_postmeta_notice' );

/**
 * cc_postmeta_notice function.
 * Displays an appropriate notice based on the results of the cc_unserialize_postmeta function.
 * @access public
 * @return void
 */
function cc_postmeta_notice()
{
	if ( $notice = get_option('cc_unserialize_notice') )
	{
		echo $notice;
	}
}

register_deactivation_hook( __FILE__, 'cc_unserialize_deactivate');

/**
 * cc_unserialize_deactivate function.
 * Cleanup plugin when deactivated
 * @access public
 * @return void
 */
function cc_unserialize_deactivate() {
	delete_option('cc_unserialize_notice'); 
}