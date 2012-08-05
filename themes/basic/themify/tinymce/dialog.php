<?php
/**
 * Bootstrap file for getting the ABSPATH constant to wp-load.php
 * This is requried when a plugin requires access not via the admin screen.
 *
 * If the wp-load.php file is not found, then an error will be displayed
 *
 * @package themify
 * @since 1.1.1.0
 * @author Elio Rivero
 */

if ( !defined('WP_LOAD_PATH') ) {

	$fullpath = explode( 'wp-content', __FILE__ );
	
	$wploadpath = $fullpath[0];
	
	//$wploadpath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . '/' ;
	if (file_exists( $wploadpath . 'wp-load.php') )
		define( 'WP_LOAD_PATH', $wploadpath);
}

// let's load WordPress
require_once( WP_LOAD_PATH . 'wp-load.php');

global $wpdb;

load_theme_textdomain( 'themify', TEMPLATEPATH.'/languages' );

// check for rights
if ( !is_user_logged_in() || !current_user_can('edit_posts') )
	wp_die(__('You are not allowed to be here', 'themify'));

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?php echo $_GET['title'] . ' ' . __('Shortcode Options', 'themify'); ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		
		<link href="<?php echo admin_url( '/css/colors-fresh.css'); ?>"  rel="stylesheet" />
		<style type="text/css">
		#shortcode-options{
			font-family: Arial, sans-serif;
		}
		#shortcode-options select{
			height: 30px;
		}
		#shortcode-options p{
			margin-bottom: 5px;
		}
		#shortcode-options .label-inner{
			margin-bottom: 5px;
			display: block;
			cursor: pointer;
			font-size: 12px;
		}
		#shortcode-options .description{
			margin-bottom: 10px;
		}
		</style>
		<script src="<?php	echo includes_url( '/js/jquery/jquery.js'); ?>"	language="javascript" type="text/javascript" ></script>
		<script src="<?php	echo includes_url( '/js/tinymce/tiny_mce_popup.js');   ?>"	language="javascript" type="text/javascript" ></script>
		<script src="<?php	echo includes_url( '/js/tinymce/utils/form_utils.js'); ?>"	language="javascript" type="text/javascript" ></script>
		<script language="javascript" type="text/javascript">
		function init() {
			tinyMCEPopup.resizeToInnerSize();
		}
		function themifyRepaint(sc_content){
			if(window.tinyMCE) {
				window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '<p>'+sc_content+'</p>');
				tinyMCEPopup.editor.execCommand('mceRepaint');
				tinyMCEPopup.close();
			}
		}
		</script>
		<base target="_self" />
	</head>
	<body id="wp-admin" onload="tinyMCEPopup.executeOnLoad('init();');document.body.style.display='';" style="display: none">
		
		<script language="javascript" type="text/javascript">
		var shortcode_type = '<?php echo $_GET['shortcode'] ?>';
		<?php
		if( isset($_GET['selection']) )
			echo "var selection = '" . $_GET['selection'] . "';";
		?>
		function themify_scparams(scparams, sctype, allowempty){
			var sccontent = '';
			jQuery.each(scparams, function(index, v){
				if( '' != jQuery('#' + v + '_' + sctype).val() )
					sccontent += v + "=\"" + jQuery('#' + v + '_' + sctype).val() + "\" ";
			});
			if( '' != sccontent || undefined != allowempty )
				return sccontent;
			else
				tinyMCEPopup.close();
		}
		function themify_insert_shortcode(type) {
			var sc_content = '';
			
			switch(type){
				case 'video':
					sc_content = '[' + type + ' ' + themify_scparams(Array('src', 'width', 'height'), type) + ']';
				break;
				
				case 'img':
					sc_content = '[' + type + ' ' + themify_scparams(Array('src', 'w', 'h'), type) + ']';
				break;
				
				case 'button':
					sc_content = '[' + type + ' ' + themify_scparams(Array('style', 'color', 'link', 'text', 'target'), type, true) + ']'
					+ jQuery('#label' + '_' + type).val() + '[/' + type + ']';
				break;
				
				case 'hr':
					sc_content = '[' + type + ' ' + themify_scparams(Array('color', 'width', 'border_width'), type) + ']';
				break;
				
				case 'box':
					sc_content = '[' + type + ' ' + themify_scparams(Array('style'), type, true) + ']' + selection + '[/' + type + ']';
				break;
				
				case 'map':
					sc_content = '[' + type + ' ' + themify_scparams(Array('address', 'width', 'height', 'zoom'), type) + ']';
				break;
				
				case 'author_box':
					sc_content = '[' + type + ' ' + themify_scparams(Array('avatar', 'avatar_size', 'style', 'author_link'), type) + ']';
				break;
				
				case 'flickr':
					sc_content = '[' + type + ' ' + themify_scparams(Array('user', 'set', 'group', 'limit', 'size', 'display'), type) + ']';
				break;
				
				case 'post_slider':
					sc_content = '[' + type + ' ' + themify_scparams( Array( 'limit', 'category', 'image', 'image_w', 'image_h', 'post_meta', 'more_text', 'visible', 'scroll', 'auto', 'wrap', 'speed', 'slider_nav', 'width', 'height', 'class', 'image_size' ), type ) + ']';
				break;
				
				case 'list_posts':
					sc_content = '[' + type + ' ' + themify_scparams(Array('limit', 'category', 'image', 'image_w', 'image_h', 'post_meta', 'more_text', 'post_date', 'style', 'image_size'), type) + ']';
				break;
				
				case 'slider':
					sc_content = '[' + type + ' ' + themify_scparams(Array( 'visible', 'scroll', 'auto', 'wrap', 'speed', 'slider_nav', 'class'), type, true) + ']' + selection + '[/' + type + ']';
				break;
			}
			
			themifyRepaint( '<p>' + sc_content + '</p>');
			
			return;
		}
		</script>
		
		<form name="shortcode-options" id='shortcode-options' action="#">
			<div class="panel current" style="margin-bottom: 0px;">
			<?php
			switch( $_GET['shortcode'] ){
				case 'video':
					$fields = array(
						array(
							'id' => 'src_video',
							'type' => 'text',
							'value' => 'http://',
							'label' => __('Enter Video URL:', 'themify')
						),
						array(
							'id' => 'width_video',
							'type' => 'text',
							'label' => __('Video Width (in px or %):', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'height_video',
							'type' => 'text',
							'label' => __('Video Height (in px or %):', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						)
					);
					break;
				case 'img':
					$fields = array(
						array(
							'id' => 'src_img',
							'type' => 'text',
							'value' => 'http://',
							'label' => __('Original Image URL:', 'themify')
						),
						array(
							'id' => 'w_img',
							'type' => 'text',
							'label' => __('Image Width (in px):', 'themify'),
							'help' => __('Example: 300px.', 'themify')
						),
						array(
							'id' => 'h_img',
							'type' => 'text',
							'label' => __('Image Height (in px):', 'themify'),
							'help' => __('Example: 300px.', 'themify')
						)
					);
					break;
				case 'button':
					$fields = array(
						array(
							'id' => 'label_button',
							'type' => 'text',
							'label' => __('Button Text:', 'themify')
						),
						array(
							'id' => 'style_button',
							'type' => 'text',
							'label' => __('Button Style:', 'themify'),
							'help' => __('You can combine (eg "large yellow rounded") the following options:', 'themify')
							. '<br/><ul><li>'
							. __('Available colors: yellow, orange, blue, green, red, black, purple, gray, light-yellow, light-blue, light-green, pink, lavender', 'themify') . '</li>'
							. '<li>' .
							__('Available sizes: small, large, xlarge', 'themify') . '</li>'
							. '<li>' .
							__('Available styles: flat, rect, rounded, embossed', 'themify') . '</li>'
							. '</ul>' . __('Example: ') . 'large red rounded'
						),
						array(
							'id' => 'color_button',
							'type' => 'text',
							'label' => __('Custom Background Color:', 'themify'),
							'help' => __('Enter color in hexadecimal format. For example, #ddd.', 'themify')
						),
						array(
							'id' => 'link_button',
							'type' => 'text',
							'value' => 'http://',
							'label' => __('Button Link:', 'themify')
						),
						array(
							'id' => 'text_button',
							'type' => 'text',
							'label' => __('Custom Button Text Color:', 'themify'),
							'help' => __('Enter color in hexadecimal format. For example, #000.', 'themify')
						),
						array(
							'id' => 'target_button',
							'type' => 'text',
							'label' => __('Link Target:', 'themify'),
							'help' => sprintf( __('Entering %s will open link in a new window (leave blank for default).', 'themify'), '<strong>_blank</strong>')
						)
					);
					break;
				case 'hr':
					$fields = array(
						array(
							'id' => 'color_hr',
							'type' => 'text',
							'label' => __('Rule Color:', 'themify'),
							'help' => __('Example: pink, red, light-gray, dark-gray, black, orange, yellow, white.', 'themify')
						),
						array(
							'id' => 'width_hr',
							'type' => 'text',
							'label' => __('Horizontal Width (in px or %):', 'themify'),
							'help' => __('Example: 50px or 50%.', 'themify')
						),
						array(
							'id' => 'border_width_hr',
							'type' => 'text',
							'label' => __('Border Width (in px):', 'themify'),
							'help' => __('Example: 1px.', 'themify')
						)
					);
					break;
				case 'box':
					$fields = array(
						array(
							'id' => 'style_box',
							'type' => 'text',
							'label' => __('Box Style:', 'themify'),
							'help' => __('You can combine (eg "yellow map rounded") the following options:', 'themify')
							. '<br/><ul><li>'
							. __('Available colors: blue, green, red, purple, yellow, orange, pink, lavender, gray, black, light-yellow, light-blue, light-green', 'themify') . '</li>'
							. '<li>' .
							__('Available icons: announcement, comment, question, upload, download, highlight, map, warning, info, note, contact', 'themify') . '</li>'
							. '<li>' .
							__('Available styles: rounded, shadow', 'themify') . '</li>'
							. '</ul>'
						)
					);
					break;
				case 'map':
					$fields = array(
						array(
							'id' => 'address_map',
							'type' => 'text',
							'label' => __('Location Address:', 'themify'),
							'help' => __('Example: 238 Street Ave., Toronto, Ontario, Canada')
						),
						array(
							'id' => 'width_map',
							'type' => 'text',
							'label' => __('Map Width (in px or %):', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'height_map',
							'type' => 'text',
							'label' => __('Map Height (in px or %):', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'zoom_map',
							'type' => 'selectbasic',
							'options' => array( '1', '2', '3', '4', '5', '6', '7', '8' ),
							'label' => __('Map Zoom Level:', 'themify'),
							'help' => __('Default = 8', 'themify')
						)
					);
					break;
				case 'author_box':
					$fields = array(
						array(
							'id' => 'avatar_author_box',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Author profile\'s avatar:', 'themify'),
							'help' => __('Default = yes')
						),
						array(
							'id' => 'avatar_size_author_box',
							'type' => 'text',
							'label' => __('Avatar image size:', 'themify'),
							'help' => __('Default = 48.', 'themify')
						),
						array(
							'id' => 'style_author_box',
							'type' => 'text',
							'label' => __('Author box style:', 'themify'),
							'help' => __('You can combine (eg "yellow rounded") the following options:', 'themify')
							. '<br/><ul><li>'
							. __('Available colors: blue, green, red, purple, yellow, orange, pink, lavender, gray, black, light-yellow, light-blue, light-green', 'themify') . '</li>'
							. '<li>' .
							__('Available icons: announcement, comment, question, upload, download, highlight, map, warning, info, note, contact', 'themify') . '</li>'
							. '<li>' .
							__('Note that you may also add your custom css class (eg. "yellow custom-class")', 'themify') . '</li>'
							. '</ul>'
						),
						array(
							'id' => 'author_link_author_box',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show author profile link:', 'themify'),
							'help' => __('Default = no', 'themify')
						)
					);
					break;
				case 'flickr':
					$fields = array(
						array(
							'id' => 'user_flickr',
							'type' => 'text',
							'label' => __('Flickr ID:', 'themify'),
							'help' => sprintf( __('Example: 52839779@N02. Use %s to find your user ID', 'themify'), '<a href="http://idgettr.com/" target="_blank">idGettr.com</a>' )
						),
						array(
							'id' => 'set_flickr',
							'type' => 'text',
							'label' => __('Flickr Set ID:', 'themify')
						),
						array(
							'id' => 'group_flickr',
							'type' => 'text',
							'label' => __('Flickr Group ID:', 'themify')
						),
						array(
							'id' => 'limit_flickr',
							'type' => 'selectbasic',
							'options' => array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ),
							'label' => __('Number of items to show:', 'themify'),
							'help' => __('Default = 8.', 'themify')
						),
						array(
							'id' => 'size_flickr',
							'type' => 'selectbasic',
							'options' => array(	's', 't', 'm' ),
							'label' => __('Photo Size:', 'themify'),
							'help' => __('Enter s, t or m. Default = s.', 'themify')
						),
						array(
							'id' => 'display_flickr',
							'type' => 'select',
							'options' => array(
								__('Latest', 'themify') => 'latest',
								__('Random', 'themify') => 'random',
							),
							'label' => __('Display:', 'themify'),
							'help' => __('Display latest photos or random (default = latest)', 'themify')
						)
					);
					break;
				case 'post_slider':
					$fields = array(
						array(
							'id' => 'limit_post_slider',
							'type' => 'text',
							'label' => __('Number of Posts to Query:', 'themify'),
							'help' => __('Default = 5', 'themify')
						),
						array(
							'id' => 'category_post_slider',
							'type' => 'text',
							'label' => __('Categories to include', 'themify'),
							'help' => __('Enter the category ID numbers (eg. 2,5,6) or leave blank for default (all categories). Use minus number to exclude category (eg. category=-1 will exclude category 1).', 'themify')
						),
						array(
							'id' => 'image_post_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Image:', 'themify'),
							'help' => __('Default = yes', 'themify')
						),
						array(
							'id' => 'image_w_post_slider',
							'type' => 'text',
							'label' => __('Post Image Width:', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'image_h_post_slider',
							'type' => 'text',
							'label' => __('Post Image Height:', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'image_size_post_slider',
							'type' => 'select',
							'options' => array(
								__('Thumbnail', 'themify') => 'thumbnail',
								__('Medium', 'themify') => 'medium',
								__('Large', 'themify') => 'large',
								__('Original', 'themify') => 'full'
							),
							'label' => __('Post Image Size:', 'themify'),
							'help' => __('Use this if you have disabled img.php', 'themify')
						),
						array(
							'id' => 'title_post_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no',
							),
							'label' => __('Show Post Title:', 'themify'),
							'help' => __('Default = yes', 'themify')
						),
						array(
							'id' => 'display_post_slider',
							'type' => 'select',
							'options' => array(
								__('Content', 'themify') => 'yes',
								__('Excerpt', 'themify') => 'no'
							),
							'label' => __('Show Post Text:', 'themify'),
							'help' => __('Default = none, neither content or excerpt are displayed.', 'themify')
						),
						array(
							'id' => 'post_meta_post_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Meta:', 'themify'),
							'help' => __('Default = no.', 'themify')
						),
						array(
							'id' => 'more_text_post_slider',
							'type' => 'text',
							'label' => __('More Text:', 'themify'),
							'help' => __('Only available if display=content and post has more tag.', 'themify')
						),
						array(
							'id' => 'visible_post_slider',
							'type' => 'text',
							'label' => __('Number of posts visible at the same time:', 'themify'),
							'help' => __('Default = 1.', 'themify')
						),
						array(
							'id' => 'scroll_post_slider',
							'type' => 'text',
							'label' => __('Number of items to scroll:', 'themify'),
							'help' => __('Default = 1.', 'themify')
						),
						array(
							'id' => 'auto_post_slider',
							'type' => 'text',
							'label' => __('Auto play slider in number of seconds:', 'themify'),
							'help' => __('Default = 0, the slider will not auto play.', 'themify')
						),
						array(
							'id' => 'wrap_post_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Wrap slider posts:', 'themify'),
							'help' => __('Default = yes, the slider will loop back to the first item', 'themify')
						),
						array(
							'id' => 'speed_post_slider',
							'type' => 'select',
							'options' => array(
								__('Normal', 'themify') => 'normal',
								__('Slow', 'themify') => 'slow',
								__('Fast', 'themify') => 'fast'
							),
							'label' => __('Animation speed:', 'themify')
						),
						array(
							'id' => 'slider_nav_post_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show slider navigation:', 'themify'),
							'help' => __('Default = yes.', 'themify')
						),
						array(
							'id' => 'width_post_slider',
							'type' => 'text',
							'label' => __('Slider div tag width:', 'themify')
						),
						array(
							'id' => 'height_post_slider',
							'type' => 'text',
							'label' => __('Slider div tag height:', 'themify')
						),
						array(
							'id' => 'class_post_slider',
							'type' => 'text',
							'label' => __('Custom CSS class name:', 'themify')
						)
					);
					break;
				case 'list_posts':
					$fields = array(
						array(
							'id' => 'limit_list_posts',
							'type' => 'text',
							'label' => __('Number of Posts to Query:', 'themify'),
							'help' => __('Default = 5', 'themify')
						),
						array(
							'id' => 'category_list_posts',
							'type' => 'text',
							'label' => __('Categories to include', 'themify'),
							'help' => __('Enter the category ID numbers (eg. 2,5,6) or leave blank for default (all categories). Use minus number to exclude category (eg. category=-1 will exclude category 1).', 'themify')
						),
						array(
							'id' => 'image_list_posts',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Image:', 'themify'),
							'help' => __('Default = yes', 'themify')
						),
						array(
							'id' => 'image_w_list_posts',
							'type' => 'text',
							'label' => __('Post Image Width:', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'image_h_list_posts',
							'type' => 'text',
							'label' => __('Post Image Height:', 'themify'),
							'help' => __('Example: 400px or 94%.', 'themify')
						),
						array(
							'id' => 'image_size_list_posts',
							'type' => 'select',
							'options' => array(
								__('Thumbnail', 'themify') => 'thumbnail',
								__('Medium', 'themify') => 'medium',
								__('Large', 'themify') => 'large',
								__('Original', 'themify') => 'full'
							),
							'label' => __('Post Image Size:', 'themify'),
							'help' => __('Use this if you have disabled img.php', 'themify')
						),
						array(
							'id' => 'title_list_posts',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Title:', 'themify'),
							'help' => __('Default = yes', 'themify')
						),
						array(
							'id' => 'display_list_posts',
							'type' => 'select',
							'options' => array(
								__('Content', 'themify') => 'yes',
								__('Excerpt', 'themify') => 'no'
							),
							'label' => __('Show Post Text:', 'themify'),
							'help' => __('Default = none, neither content or excerpt are displayed.', 'themify')
						),
						array(
							'id' => 'post_meta_list_posts',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Meta:', 'themify'),
							'help' => __('Default = no.', 'themify')
						),
						array(
							'id' => 'more_text_list_posts',
							'type' => 'text',
							'label' => __('More Text:', 'themify'),
							'help' => __('Only available if display=content and post has more tag.', 'themify')
						),
						array(
							'id' => 'post_date_list_posts',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no'
							),
							'label' => __('Show Post Date:', 'themify'),
							'help' => __('Default = no.', 'themify')
						),
						array(
							'id' => 'style_list_posts',
							'type' => 'select',
							'options' => array(
								__('Post list', 'themify') => 'list-post',
								__('4 Grid', 'themify') => 'grid4',
								__('3 Grid', 'themify') => 'grid3',
								__('2 Grid', 'themify') => 'grid2',
								__('2 Grid-thumb', 'themify') => 'grid2-thumb',
								__('List-thumb', 'themify') => 'list-thumb-image'
							),
							'label' => __('Layout Style:', 'themify'),
							'help' => __('Default = list-post.', 'themify')
						)
					);
					break;
				case 'slider':
					$fields = array(
						array(
							'id' => 'visible_slider',
							'type' => 'text',
							'label' => __('Number of items visible at the same time:', 'themify'),
							'help' => __('Default = 1.', 'themify')
						),
						array(
							'id' => 'scroll_slider',
							'type' => 'text',
							'label' => __('Number of items to scroll:', 'themify'),
							'help' => __('Default = 1.', 'themify')
						),
						array(
							'id' => 'auto_slider',
							'type' => 'text',
							'label' => __('Auto play slider in number of seconds:', 'themify'),
							'help' => __('Default = 0, the slider will not auto play.', 'themify')
						),
						array(
							'id' => 'wrap_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no',
							),
							'label' => __('Wrap slider items:', 'themify'),
							'help' => __('Default = yes, the slider will loop back to the first item', 'themify')
						),
						array(
							'id' => 'speed_slider',
							'type' => 'select',
							'options' => array(
								__('Normal', 'themify') => 'normal',
								__('Slow', 'themify') => 'slow',
								__('Fast', 'themify') => 'fast'
							),
							'label' => __('Animation speed:', 'themify')
						),
						array(
							'id' => 'slider_nav_slider',
							'type' => 'select',
							'options' => array(
								__('Yes', 'themify') => 'yes',
								__('No', 'themify') => 'no',
							),
							'label' => __('Show slider navigation:', 'themify'),
							'help' => __('Default = yes.', 'themify')
						),
						array(
							'id' => 'class_slider',
							'type' => 'text',
							'label' => __('Custom CSS class name:', 'themify')
						),
						array(
							'type' => 'info',
							'info' => __('See <a href="http://themify.me/docs/shortcodes#slider">documentation</a> for more details', 'themify'),
						)
					);
					break;
			}
			
			foreach ($fields as $field) {
				?>
				<p>
					<?php if(isset($field['id']) && isset($field['label'])){ ?>
						<label for="<?php echo $field['id'] ?>"><span class="label-inner"><?php echo $field['label']; ?></span>
					<?php }	?>
						<?php
						if('text' == $field['type']){
						?>
							<input type="text" style="padding: 5px; width:200px;" id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>" placeholder="<?php if(isset($field['value'])) echo $field['value']; ?>" />
						<?php
						} elseif('select' == $field['type']){
						?>
							<select style="padding: 5px; width:200px;" id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>" >
								<?php
								echo '<option value=""></option>';
								foreach ($field['options'] as $key => $value) {
									echo '<option value="' . $value . '">' . $key . '</option>';
								}
								?>
							</select>
						<?php
						} elseif('selectbasic' == $field['type']){
						?>
							<select style="padding: 5px; width:200px;" id="<?php echo $field['id'] ?>" name="<?php echo $field['id'] ?>" >
								<?php
								echo '<option value=""></option>';
								foreach ($field['options'] as $value) {
									echo '<option value="' . $value . '">' . $value . '</option>';
								}
								?>
							</select>
						<?php
						} elseif ('info' == $field['type']){
						?>
							<p><?php if(isset($field['info'])) echo $field['info']; ?></p>
						<?php
						}
						?>
					<?php if(isset($field['id']) && isset($field['label'])){ ?>
					</label>
					<?php } ?>
				</p>
				<div class="description"><?php if(isset($field['help'])) echo $field['help']; ?></div>
			<?php
			}
			
			?>
			</div><!--/panel current-->
			<div class="mceActionPanel submitbox" style="border-top: 1px solid #CCC;padding-top: 5px;">
				<div id="delete-action" style="float: left;">
					<a class="submitdelete deletion" onclick="tinyMCEPopup.close();" style="text-decoration: underline;cursor:pointer;padding: 0 2px;"><?php _e('Cancel', 'themify'); ?></a>
				</div>
		
				<div id="wp-link-update" style="float: right;">
					<input class="button-primary" type="submit" id="wp-link-submit" name="insert" value="<?php _e('Insert', 'ilc'); ?>" onclick="themify_insert_shortcode(shortcode_type);" style="padding: 4px 8px;border-radius: 10px;cursor:pointer;" />
				</div>
			</div>
			
		</form>
		
	</body>
</html>	
<?php
//finish box
?>