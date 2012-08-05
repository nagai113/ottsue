<?php /*

**************************************************************************

Plugin Name:  Regenerate Thumbnails
Plugin URI:   http://www.viper007bond.com/wordpress-plugins/regenerate-thumbnails/
Description:  Allows you to regenerate all thumbnails after changing the thumbnail sizes.
Version:      2.1.3
Author:       Viper007Bond
Author URI:   http://www.viper007bond.com/

**************************************************************************

Copyright (C) 2008-2010 Viper007Bond

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

class RegenerateThumbnails {
	var $menu_id;
	var $uripath;
	var $dirpath;

	// Plugin initialization
	function RegenerateThumbnails() {
		if ( ! function_exists( 'admin_url' ) )
			return false;
		
		$this->uripath = THEMIFY_URI . '/regenerate-thumbnails';
		$this->dirpath = THEMIFY_DIR . '/regenerate-thumbnails';

		add_action( 'admin_menu',                              array( &$this, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts',                   array( &$this, 'admin_enqueues' ) );
		add_action( 'wp_ajax_regeneratethumbnail',             array( &$this, 'ajax_process_image' ) );
		add_action( 'wp_ajax_collectposts', 				   array( &$this, 'ajax_collectposts' ) );
		add_action( 'wp_ajax_processposts', 				   array( &$this, 'ajax_processposts' ) );
		add_filter( 'media_row_actions',                       array( &$this, 'add_media_row_action' ), 10, 2 );
		add_filter( 'bulk_actions-upload',                     array( &$this, 'add_bulk_actions' ), 99 );
		add_action( 'admin_action_bulk_regenerate_thumbnails', array( &$this, 'bulk_action_handler' ) );
	}


	// Register the management page
	function add_admin_menu() {
		$this->menu_id = add_submenu_page('themify', __( 'Rebuild Thumbnails', 'themify' ), __( 'Rebuild Thumbnails', 'themify' ), 'manage_options', 'regenerate-thumbnails', array(&$this, 'regenerate_interface') );
	}


	// Enqueue the needed Javascript and CSS
	function admin_enqueues( $hook_suffix ) {
		if ( $hook_suffix != $this->menu_id )
			return;

		// WordPress 3.1 vs older version compatibility
		if ( wp_script_is( 'jquery-ui-widget', 'registered' ) )
			wp_enqueue_script( 'jquery-ui-progressbar', $this->uripath .  '/jquery-ui/jquery.ui.progressbar.min.js', array( 'jquery-ui-core', 'jquery-ui-widget' ), '1.8.6' );
		else
			wp_enqueue_script( 'jquery-ui-progressbar', $this->uripath . 'jquery-ui/jquery.ui.progressbar.min.1.7.2.js', array( 'jquery-ui-core' ), '1.7.2' );

		wp_enqueue_style( 'jquery-ui-regenthumbs', $this->uripath . '/jquery-ui/redmond/jquery-ui-1.7.2.custom.css', array(), '1.7.2' );
	}


	// Add a "Rebuild Thumbnails" link to the media row actions
	function add_media_row_action( $actions, $post ) {
		if ( 'image/' != substr( $post->post_mime_type, 0, 6 ) )
			return $actions;

		$url = wp_nonce_url( admin_url( 'admin.php?page=regenerate-thumbnails&goback=1&ids=' . $post->ID ), 'regenerate-thumbnails' );
		$actions['regenerate_thumbnails'] = '<a href="' . esc_url( $url ) . '" title="' . esc_attr( __( "Rebuild the thumbnails for this single image", 'themify' ) ) . '">' . __( 'Rebuild Thumbnails', 'themify' ) . '</a>';

		return $actions;
	}


	// Add "Rebuild Thumbnails" to the Bulk Actions media dropdown
	function add_bulk_actions( $actions ) {
		$delete = false;
		if ( ! empty( $actions['delete'] ) ) {
			$delete = $actions['delete'];
			unset( $actions['delete'] );
		}
		$actions['bulk_regenerate_thumbnails'] = __( 'Rebuild Thumbnails', 'themify' );

		if ( $delete )
			$actions['delete'] = $delete;

		return $actions;
	}


	// Handles the bulk actions POST
	function bulk_action_handler() {
		check_admin_referer( 'bulk-media' );

		if ( empty( $_POST['media'] ) && is_array( $_POST['media'] ) )
			return;

		$ids = implode( ',', array_map( 'intval', $_POST['media'] ) );

		// Can't use wp_nonce_url() as it escapes HTML entities
		wp_redirect( add_query_arg( '_wpnonce', wp_create_nonce( 'regenerate-thumbnails' ), admin_url( 'tools.php?page=regenerate-thumbnails&goback=1&ids=' . $ids ) ) );
		
		exit();
	}

	// The user interface plus thumbnail regenerator
	function regenerate_interface() {
		global $wpdb;

		?>

<div id="message" class="updated fade" style="display:none"></div>

<div class="wrap regenthumbs">
	<h2><?php _e('Rebuild Thumbnails', 'themify'); ?></h2>

<?php

		// If the button was clicked
		if ( ! empty( $_POST['regenerate-thumbnails'] ) || ! empty( $_REQUEST['ids'] ) ) {
			// Capability check
			if ( !current_user_can( 'manage_options' ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );

			// Form nonce check
			check_admin_referer( 'regenerate-thumbnails' );

			// Create the list of image IDs
			if ( ! empty( $_REQUEST['ids'] ) ) {
				$images = array_map( 'intval', explode( ',', trim( $_REQUEST['ids'], ',' ) ) );
				$ids = implode( ',', $images );
			} else {
				// Directly querying the database is normally frowned upon, but all
				// of the API functions will return the full post objects which will
				// suck up lots of memory. This is best, just not as future proof.
				if ( ! $images = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC" ) ) {
					echo '	<p>' . sprintf( __( "Unable to find any images. Are you sure <a href='%s'>some exist</a>?", 'themify' ), admin_url( 'upload.php?post_mime_type=image' ) ) . "</p></div>";
					return;
				}

				// Generate the list of IDs
				$ids = array();
				foreach ( $images as $image )
					$ids[] = $image->ID;
				$ids = implode( ',', $ids );
			}

			echo '	<p>' . __( "Please be patient while the thumbnails are regenerated. This can take a while if your server is slow or if you have many images. Do not navigate away from this page until this script is done or the thumbnails will not be resized. You will be notified via this page when the regenerating is completed.", 'themify' ) . '</p>';

			$count = count( $images );

			$text_goback = ( ! empty( $_GET['goback'] ) ) ? sprintf( __( 'To go back to the previous page, <a href="%s">click here</a>.', 'themify' ), 'javascript:history.go(-1)' ) : '';
			
			$text_failures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were %3$s failure(s). To try regenerating the failed images again, <a href="%4$s">click here</a>. %5$s', 'themify' ), "' + rt_successes + '", "' + rt_totaltime + '", "' + rt_errors + '", esc_url( wp_nonce_url( admin_url( 'admin.php?page=regenerate-thumbnails&goback=1' ), 'regenerate-thumbnails' ) . '&ids=' ) . "' + rt_failedlist + '", $text_goback );
			$text_nofailures = sprintf( __( 'All done! %1$s image(s) were successfully resized in %2$s seconds and there were 0 failures. %3$s', 'themify' ), "' + rt_successes + '", "' + rt_totaltime + '", $text_goback );
?>


	<noscript><p><em><?php _e( 'You must enable Javascript in order to proceed!', 'themify' ) ?></em></p></noscript>

	<div id="regenthumbs-bar" style="position:relative;height:25px;">
		<div id="regenthumbs-bar-percent" style="position:absolute;left:50%;top:50%;width:300px;margin-left:-150px;height:25px;margin-top:-9px;font-weight:bold;text-align:center;"></div>
	</div>

	<p><input type="button" class="button hide-if-no-js" name="regenthumbs-stop" id="regenthumbs-stop" value="<?php _e( 'Abort Resizing Images', 'themify' ) ?>" /></p>

	<h3 class="title"><?php _e( 'Debugging Information', 'themify' ) ?></h3>

	<p>
		<?php printf( __( 'Total Images: %s', 'themify' ), $count ); ?><br />
		<?php printf( __( 'Images Resized: %s', 'themify' ), '<span id="regenthumbs-debug-successcount">0</span>' ); ?><br />
		<?php printf( __( 'Resize Failures: %s', 'themify' ), '<span id="regenthumbs-debug-failurecount">0</span>' ); ?>
	</p>

	<ol id="regenthumbs-debuglist">
		<li style="display:none"></li>
	</ol>

	<script type="text/javascript">
	// <![CDATA[
		jQuery(document).ready(function($){
			var i;
			var rt_images = [<?php echo $ids; ?>];
			var rt_total = rt_images.length;
			var rt_count = 1;
			var rt_percent = 0;
			var rt_successes = 0;
			var rt_errors = 0;
			var rt_failedlist = '';
			var rt_resulttext = '';
			var rt_timestart = new Date().getTime();
			var rt_timeend = 0;
			var rt_totaltime = 0;
			var rt_continue = true;

			// Create the progress bar
			$("#regenthumbs-bar").progressbar();
			$("#regenthumbs-bar-percent").html( "0%" );

			// Stop button
			$("#regenthumbs-stop").click(function() {
				rt_continue = false;
				$('#regenthumbs-stop').val("<?php echo $this->esc_quotes( __( 'Stopping...', 'themify' ) ); ?>");
			});

			// Clear out the empty list element that's there for HTML validation purposes
			$("#regenthumbs-debuglist li").remove();

			// Called after each resize. Updates debug information and the progress bar.
			function RegenThumbsUpdateStatus( id, success, response ) {
				$("#regenthumbs-bar").progressbar( "value", ( rt_count / rt_total ) * 100 );
				$("#regenthumbs-bar-percent").html( Math.round( ( rt_count / rt_total ) * 1000 ) / 10 + "%" );
				rt_count = rt_count + 1;

				if ( success ) {
					rt_successes = rt_successes + 1;
					$("#regenthumbs-debug-successcount").html(rt_successes);
					$("#regenthumbs-debuglist").append("<li>" + response.success + "</li>");
				}
				else {
					rt_errors = rt_errors + 1;
					rt_failedlist = rt_failedlist + ',' + id;
					$("#regenthumbs-debug-failurecount").html(rt_errors);
					$("#regenthumbs-debuglist").append("<li>" + response.error + "</li>");
				}
			}

			// Called when all images have been processed. Shows the results and cleans up.
			function RegenThumbsFinishUp() {
				rt_timeend = new Date().getTime();
				rt_totaltime = Math.round( ( rt_timeend - rt_timestart ) / 1000 );

				$('#regenthumbs-stop').hide();

				if ( rt_errors > 0 ) {
					rt_resulttext = '<?php echo $text_failures; ?>';
				} else {
					rt_resulttext = '<?php echo $text_nofailures; ?>';
				}

				$("#message").html("<p><strong>" + rt_resulttext + "</strong></p>");
				$("#message").show();
			}

			// Regenerate a specified image via AJAX
			function RegenThumbs( id ) {
				$.ajax({
					type: 'POST',
					url: ajaxurl,
					data: { action: "regeneratethumbnail", id: id },
					success: function( response ) {
						if ( response.success ) {
							RegenThumbsUpdateStatus( id, true, response );
						}
						else {
							RegenThumbsUpdateStatus( id, false, response );
						}

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						}
						else {
							RegenThumbsFinishUp();
						}
					},
					error: function( response ) {
						RegenThumbsUpdateStatus( id, false, response );

						if ( rt_images.length && rt_continue ) {
							RegenThumbs( rt_images.shift() );
						} 
						else {
							RegenThumbsFinishUp();
						}
					}
				});
			}

			RegenThumbs( rt_images.shift() );
		});
	// ]]>
	</script>
	
<?php
		}

		// No button click? Display the form.
		else {
?>
<?php
/**
 * Collect posts which don't have a wp post thumbnail and fix them by attaching
 * the legacy feature image to the post and setting it as the post thumbnail 
 */
?>
<div class="listapost" style="float: left; width: 48%; margin-right: 2%;">
	<small><?php _e('Checking posts thumbnails...', 'themify'); ?> </small>
</div>
<?php
/**
 * Part of conversion from legacy feature image to post thumbnail.
 */
?>
<script type="text/javascript">
// <![CDATA[
jQuery(document).ready(function($){
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data:{
			action: 'collectposts'
		},
		success: function(response){

			if(response == true){
				jQuery('div.listapost').remove();
			}
			else{
				jQuery('div.listapost small').remove();
				jQuery('div.listapost').append( response.collectedposts );

				//Now let's process the posts!
				$('#showdetails').click(function(){
					$('.posttofix').slideToggle();
				});
				//flag to check if the user has already processed the posts
				postsAlreadyProcessed = 0;
				//number of posts to process
				postsToProcess = response.idstofix;
				postsToProcess = postsToProcess.length;
				jQuery('div.listapost').append('<div id="processedposts" style="display: none; height: 200px; overflow: scroll; overflow-x: hidden; overflow-y: scroll; border: 1px solid #EEE; padding: 0 10px; background: #F6F6F6; font-size: 11px; line-height: 120%;">');
				jQuery('#processposts').click(function(){
					if( 0 == postsAlreadyProcessed ){
						if(confirm('<?php _e('This will convert the Post Image and Feature Image custom field to WordPress Featured Image (if applicable).', 'themify'); ?>')){
							jQuery('#processedposts').show();
							jQuery.each(response.idstofix, function(){
								var postid = this.toString();
								jQuery('#processedposts').append('<p><?php _e('Processing post with ID ', 'themify'); ?> ' + postid + '...</p>');
								jQuery.post(
									ajaxurl,
									{
										action: 'processposts',
										postid: postid
									},
									function(response){
										jQuery('#processedposts').append( response );
										//decrement number of posts to process
										postsToProcess--;
										if( postsToProcess <= 0){
											jQuery('div.listapost').append('<p><?php _e('All done.', 'themify'); ?> <a href="<?php echo admin_url('admin.php?page=themify'); ?>"><?php _e('Have fun!', 'themify'); ?></a></p>');
										}
									}
								);
							});
							postsAlreadyProcessed = 1;
						}
						else{
							return false;
						}
					}
					else{
						alert('<?php _e('You already processed these posts. ', 'themify');?>');
					}
				});
			}
			
		},
		error: function(response){
			
		},
		dataType: 'json'
	});
});
// ]]>
</script>
<form method="post" action="" style="overflow: hidden">
	<?php wp_nonce_field('regenerate-thumbnails') ?>
	
	<h3><?php _e('Rebuild Thumbnails', 'themify'); ?></h3>
	
	<p><?php printf( __( "Use this tool to rebuild thumbnails for all images that you have uploaded to your blog. This is useful if you've changed any of the thumbnail dimensions on the <a href='%s'>media settings page</a>. Old thumbnails will be kept to avoid any broken images due to hard-coded URLs.", 'themify' ), admin_url( 'options-media.php' ) ); ?></p>

	<p><?php _e( "Thumbnail regeneration is not reversible, but you can just change your thumbnail dimensions back to the old values and click the button again if you don't like the results.", 'themify' ); ?></p>

	<p><input type="submit" class="button hide-if-no-js" name="regenerate-thumbnails" id="regenerate-thumbnails" value="<?php _e( 'Rebuild All Thumbnails', 'themify' ) ?>" /></p>

	<noscript>
		<p>
			<em><?php _e( 'You must enable Javascript in order to proceed!', 'themify' ) ?></em>
			</p>
	</noscript>
</form>
	
<?php
		} // End if button
?>
</div>

<?php
	}
	
	/**
	 * Part of conversion from legacy feature image to post thumbnail.
	 * Collect all posts that must and CAN be fixed.
	 * @since 1.1.3
	 */
	function ajax_collectposts(){
		//Get all CPT created by Themify
		$posttypes = themify_post_types();
		
		//Get list of posts that have a path in feature_image
		$themify_postlist = get_posts( array(
			'numberposts' => -1,
			'post_type' => $posttypes,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'post_image'
				),
				array(
					'key' => 'feature_image'
				)
			)
		));
		$html = '<style type="text/css">
		.posttofix{
			display: none;
			height: 500px;
			overflow: scroll;
			overflow-x: hidden;
			overflow-y: scroll;
			border: 1px solid #EEE;
			padding: 0 20px 0 10px;
			background: #F6F6F6;
			font-size: 11px;
			line-height: 120%;
		}
		.posttofix ol li{
			margin-bottom:20px;
		}
		.posttofix ol p{
			margin-top: -0.5em;
		}
		.posttofix ol h3 small{
			font-weight:normal;
			clear:both;
			display: block;
			margin-top: .5em;
		}
		</style>';
		//Start displaying errors
		$html .= '<h3>' . __('Post Image Migrator', 'themify') . '</h3>';
		$html .= '<p>'.__("Use this tool to convert the Themify Post Image and Feature Image custom field to WordPress Featured Image. If the image URL is not in the same domain than your WordPress site, the processor will skip it.", 'themify') . '</p><p><a href="#" id="processposts" class="button hide-if-no-js" title="' . __('Process all posts that can be migrated.', 'themify') . '">' . __('Process All Posts', 'themify') . '</a> &nbsp; <a href="#" id="showdetails">' . __('See Details', 'themify') . '</a></p>';
		$html .= '<div class="posttofix">';
		$html .= '<p id="fix-types">' . __('Filter view by:', 'themify') . ' ';
		$html .= '<a href="#" id="fix-all" style="margin-right: 10px;">' . __('All', 'themify') . '</a>';
		$fixtypes = array( 'post', 'slider', 'highlights', 'menu' );
		foreach($fixtypes as $type){
			if( post_type_exists($type) )
				$html .= '<a href="#" id="fix-' . $type . '" style="margin-right: 10px;">' . ucwords($type) . '</a>';
		}
		$html .= '</p>';
		$html .= '<ol>';
		//Initialize list of posts to be fixed
		$themify_postfix = array();
		foreach ($themify_postlist as $post) {
			//Get wp post thumbnail
			$thumbnailid = get_post_meta($post->ID, '_thumbnail_id', true);
			//Get themify legacy Post Image. We give priority to this field.
			$featimg = get_post_meta($post->ID, 'post_image', true);
			//If there was no URL here, we will try another field
			if( empty($featimg) ){
				//Get themify legacy Feature Image. If the Post Image field is empty, try this one.
				$featimg = get_post_meta($post->ID, 'feature_image', true);
			}			
			//Parse URL of legacy feature image to obtain the host later
			$featimgurl = parse_url($featimg);
			//Save ID, post type, title and a link to edit the post
			$postedit = '<li class="fix-all fix-' . $post->post_type . '">
				<h3>' . $post->post_title . '
						<small>ID: '. $post->ID .' | Type: ' . ucwords($post->post_type) . ' | <a href="' . get_edit_post_link($post->ID) . '">Edit post</a>
						</small>
				</h3>';
			
			if( $thumbnailid ){
				$thumbpost = get_post($thumbnailid);
				
				if($featimg == $thumbpost->guid){
					//$html .=  $postedit . '<p><strong style="color:#060;">' . __("Featured Image and Themify's Post Image match!", 'themify') . '</strong></p>';
				}
				else{
					
					//Display details and edit link for this post
					$html .= $postedit;
					///Parse URL of wp post thumbnail to obtain the host later
					$thumburl = parse_url($thumbpost->guid);
					//Display the hosts of the wp post thumbnail and legacy feature image
					$html .=  '<p><strong>' . __('Featured Image:', 'themify') . '</strong><br/>'. $thumbpost->guid . '</p>';
					$html .=  '<p><strong>' . __("Themify's Post Image:", 'themify') . '</strong><br/>' . $featimg . '</p>';
					
					if($featimgurl['host'] == $thumburl['host']){
						//Image is in the same server, so we can add it.
						$html .=  '<p style="color:#480;">' . __("The post image URL appears to be in the same server that this WordPress installation so it will be set as the Featured Image.", 'themify') . '</p>';
						//Add post to list of posts to be fixed
						$themify_postfix[] = $post;
					}
					else{
						//Legacy image is not in the same server so it can't be added as the post thumbnail
						$html .=  '<p style="color:#d00;">' . __("The post image URL is not in the same server than your WordPress installation so it can't be set as the Featured Image.", 'themify') . '</p>';
					}
				}
			}
			else{
				// Initialize WP Filesystem API
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				$url = wp_nonce_url('admin.php?page=regenerate-thumbnails','themify-regen_thumbs');
				if (false === ($creds = request_filesystem_credentials($url, '', true, false, null) ) ) {
					return true;
				}
				if ( ! WP_Filesystem($creds) ) {
					request_filesystem_credentials($url, '', true, false, null);
					return true;
				}
				global $wp_filesystem, $blog_id;
				//get site or blog upload dir
				$updir = wp_upload_dir();
				
				//since WPMS redirects the upload dir, we need to build the path to the image in the server
				if( is_multisite() ){
					//if $blog_id has been correctly instanced and it's a blog
					if ( isset($blog_id) && $blog_id > 0) {
						//split image url in two and remove /files string
						$imgexp = explode('/files', $featimg);
						//if we have the last portion with the directories path ordered by date
						if (isset($imgexp[1])) {
							//get server path to the image
							$serverimgpath = $updir['basedir'] . $imgexp[1];
						} else {
							$html .=  '<p style="color:#d00;">' . __('Image not found. The path to the image is broken.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
						}
					} else {
						$html .=  '<p style="color:#d00;">' . __('Multisite reference OK but bad blog ID.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
					}
				} else {
					//split image url in two and remove /files string
					$imgexp = explode('/uploads', $featimg);
					$serverimgpath = $updir['basedir'] . $imgexp[1];
				}
				
				//Display details and edit link for this post
				$html .= $postedit;
				//get home url to check later
				$homeurl = home_url();
				///Parse home URL to obtain the host later
				$localurl = parse_url( $homeurl );
				
				//same domain or host
				if($featimgurl['host'] == $localurl['host']){
					//check if file exists
					if($wp_filesystem->exists($serverimgpath)){
						if( is_multisite() ){
							//multisite install, same domain
							$featimginfo = pathinfo($featimg);
							$featimgpath = $featimginfo['dirname'];
							$featimgpath = split('files', $featimgpath);
							if( home_url().'/' == $featimgpath[0]){
								$html .=  '<p style="color:#480;">' . __('No featured image set but the post image can be set as the featured image.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
								$themify_postfix[] = $post;
							} else {
								$html .=  '<p style="color:#d00;">' . __('No Featured Image set and the post image is in a different site from your multisite installation.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
							}
						}
						else {
							//is single wordpress
							$existe = $wp_filesystem->is_file($serverimgpath);
							$fiinfo = pathinfo($featimg);
							$fipath = $fiinfo['dirname'];
							$fipath = split('/wp-content/uploads', $fipath);
							/*ob_start();
							dumpit($serverimgpath);
							dumpit($existe);
							dumpit($fipath);
							dumpit($homeurl);
							//$html .= ob_get_contents();
							ob_end_clean();*/
							if($homeurl != $fipath[0]){
								//single wordpress, same domain or host, different subdirectory
								$html .=  '<p style="color:#d00;">' . __('The URL for the post image is in a different subdirectory from your host.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
							}
							else{
								$html .=  '<p style="color:#480;">' . __('No Featured Image set but the post image can be set as the featured image.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
									$themify_postfix[] = $post;
							}
						}
					}
					else {
						$html .=  '<p style="color:#d00;">' . __('The referenced post image does not exist.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
					}
				}
				else{
					$html .=  '<p style="color:#d00;">' . __('The post image is not in the same server than your WordPress installation so it can\'t be set as the featured image.', 'themify') . '<br/><small>' . $featimg . '</small></p>';
				}
			}
			$html .=  '</li>';
		}
		$html .=  '</ol></div><!-- END posts to fix --><br/>';
		
		$html .= "
			<script type='text/javascript'>
			jQuery(document).ready(function() {
				jQuery('#fix-types a').click(function(){
					jQuery('.fix-all').fadeOut();
					jQuery('.' + jQuery(this).attr('id')).fadeIn();
				});
			});
			</script>
		";
		
		$idstofix = array();
		foreach($themify_postfix as $post){
			$idstofix[] = $post->ID;
		}
		/////////////////////////////////////////////////////////////
		// THIS IS JUST TO MAKE IT FAIL SO WE CAN SEE THE OUTPUT!! //
						$themify_postfix[] = '';
		// IT MUST BE REMOVED FOR PRODUCTION                       //
		/////////////////////////////////////////////////////////////
		if( empty($themify_postfix) ){
			echo 'true';
			die();
		}
		else echo json_encode( array('collectedposts' => $html, 'idstofix' => $idstofix) );
		
		die();
	}
	/**
	 * Part of conversion from legacy feature image to post thumbnail.
	 * Process posts that must be fixed. Image is:
	 * 1) attached to post
	 * 2) inserted into media library and thumbnail sizes are generated
	 * 3) set as the post thumbnail
	 * @since 1.1.3
	 */
	function ajax_processposts(){
		//Get ID of post sent by AJAX
		$postid = $_POST['postid'];
		//get legacy image URI from Post Image to attach into media library and set as post thumbnail
		$url = get_post_meta( $postid, 'post_image', true );

		//If there was no URL here, we will try another field
		if( !isset($url) || '' == $url ){
			//Get themify legacy Feature Image. If the Post Image field is empty, try this one.
			$url = get_post_meta($postid, 'feature_image', true);
		}
		if ( is_multisite() ) {
			//if is multisite, truncate on files remove everything but year/month folders and filename
			$datefile = split('files', $url);
		}
		else{
			//if it's a single install, truncate on uploads remove everything but year/month folders and filename
			$datefile = split('uploads', $url);
		}

		//get upload directory location
		$upload_dir = wp_upload_dir();
		
		//build path using the wp upload dir and filename
		$filename = $upload_dir['basedir'] . $datefile[1];
		//check file type
		$wp_filetype = wp_check_filetype( basename( $filename ) );
		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title' 	 => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'	 => '',
			'post_status'	 => 'inherit',
			'guid'		  	 => $url
		);
		//insert image into media library
		$attach_id = wp_insert_attachment( $attachment, $filename, $postid );
		//If image could not be inserted as attachment
		if( 0 == $attach_id ) {
			_e('Image could not be attached.', 'themify');
			die();
		}
		//include image.php for function wp_generate_attachment_metadata() to work
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		//generate metadata and image sizes as set on Settings\Media in WP Admin
		$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );

		//write metadata into attachment
		if( false == wp_update_attachment_metadata( $attach_id, $attach_data ) ){
			echo sprintf( '<p>'.__('Metadata for image %s could not be written.', 'themify').'</p>', $attach_id);
			die();
		}
		//set as post thumbnail
		if( false == set_post_thumbnail( $postid, $attach_id ) ){
			echo sprintf('<p>'.__('Could not set the image %s as thumbnail for post %s.', 'themify').'</p>', $attach_id, $postid);
			die();
		}
		update_post_meta($postid, 'feature_size', 'blank');
		//display success message
		echo sprintf('<p>' .__('Image added to Media Gallery with ID %s and set as Featured Image for post with ID %s', 'themify'). '</p>', $attach_id.'<br/><small>'.$url.'</small><br/>', $postid, get_the_title($postid));
		//finish script execution
		die();
	}

	// Process a single image ID (this is an AJAX handler)
	function ajax_process_image() {
		@error_reporting( 0 ); // Don't break the JSON result

		header( 'Content-type: application/json' );

		$id = (int) $_REQUEST['id'];
		$image = get_post( $id );

		if ( ! $image || 'attachment' != $image->post_type || 'image/' != substr( $image->post_mime_type, 0, 6 ) )
			die( json_encode( array( 'error' => sprintf( __( 'Failed resize: %s is an invalid image ID.', 'themify' ), esc_html( $_REQUEST['id'] ) ) ) ) );

		if ( !current_user_can( 'manage_options' ) )
			$this->die_json_error_msg( $image->ID, __( "Your user account doesn't have permission to resize images", 'themify' ) );

		$fullsizepath = get_attached_file( $image->ID );

		if ( false === $fullsizepath || ! file_exists( $fullsizepath ) )
			$this->die_json_error_msg( $image->ID, sprintf( __( 'The originally uploaded image file cannot be found at %s', 'themify' ), '<code>' . esc_html( $fullsizepath ) . '</code>' ) );

		@set_time_limit( 900 ); // 5 minutes per image should be PLENTY

		$metadata = wp_generate_attachment_metadata( $image->ID, $fullsizepath );

		if ( is_wp_error( $metadata ) )
			$this->die_json_error_msg( $image->ID, $metadata->get_error_message() );
		if ( empty( $metadata ) )
			$this->die_json_error_msg( $image->ID, __( 'Unknown failure reason.', 'themify' ) );

		// If this fails, then it just means that nothing was changed (old value == new value)
		wp_update_attachment_metadata( $image->ID, $metadata );

		die( json_encode( array( 'success' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.', 'themify' ), esc_html( get_the_title( $image->ID ) ), $image->ID, timer_stop() ) ) ) );
	}


	// Helper to make a JSON error message
	function die_json_error_msg( $id, $message ) {
		die( json_encode( array( 'error' => sprintf( __( '&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s', 'themify' ), esc_html( get_the_title( $id ) ), $id, $message ) ) ) );
	}


	// Helper function to escape quotes in strings for use in Javascript
	function esc_quotes( $string ) {
		return str_replace( '"', '\"', $string );
	}
}

// Start up this plugin
add_action( 'init', 'RegenerateThumbnails' );
function RegenerateThumbnails() {
	global $RegenerateThumbnails;
	$RegenerateThumbnails = new RegenerateThumbnails();
}

?>