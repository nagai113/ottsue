<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php bloginfo('text_direction'); ?>" xml:lang="<?php bloginfo('language'); ?>">
	<head>
		<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
		<title>
			<?php
				global $page, $paged;
				wp_title( '|', true, 'right' );
				bloginfo( 'name' );
				$site_description = get_bloginfo( 'description', 'display' );
				if ( $site_description && ( is_home() || is_front_page() ) ) echo " | $site_description";
				if ( $paged >= 2 || $page >= 2 ) echo ' | ' . sprintf( __( 'Page %s' ), max( $paged, $page ) );
			?>
		</title>
		<link rel="profile" href="http://gmpg.org/xfn/11" />
		<?php 
		if ( get_option('free01_favicon_url') ) 
			echo '<link rel="shortcut icon" href="' . get_option('free01_favicon_url') . '" type="image/x-icon" />'; 
		else
			echo '<link rel="shortcut icon" type="image/ico" href="'.get_bloginfo( 'template_url' ).'/favico.ico" />';
		?>
		
		<link rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" type="text/css" media="all" />
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
		<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>"/>
		<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />

		<?php
			wp_enqueue_script( 'jquery' );
			
			if ( is_singular() && get_option( 'thread_comments' ) ) wp_enqueue_script( 'comment-reply' );
			wp_head();
		?>
		<?php
			$numTmpFontSize	=	(int)(get_option('free01_font_size', 14));
			if ($numTmpFontSize < 12) $numTmpFontSize = 12;

			$strFontFamily	=	get_option('free01_font_family', 'Helvetica');
			if ($strFontFamily == 'Droid Sans Mono')
			{
				echo '<link href="http://fonts.googleapis.com/css?family=Droid+Sans+Mono&amp;v1" rel="stylesheet" type="text/css" />';
				$strFontFamily	= '"Droid Sans Mono", Sans-Serif';
			}
			if ($strFontFamily == 'Arvo')
			{
				echo '<link href="http://fonts.googleapis.com/css?family=Arvo&amp;v1" rel="stylesheet" type="text/css" />';
				$strFontFamily	= '"Arvo", Sans-Serif';
			}
			if ($strFontFamily == 'Bentham')
			{
				echo '<link href="http://fonts.googleapis.com/css?family=Bentham&amp;v1" rel="stylesheet" type="text/css" />';
				$strFontFamily	= '"Bentham", Sans-Serif';
			}
			if ($strFontFamily == 'Helvetica')
			{
				$strFontFamily	= 'Helvetica, Arial, Sans-Serif';
			}
			if ($strFontFamily == 'Georgia')
			{
				$strFontFamily	= 'Georgia, Times, Times New Roman, Serif';
			}
			if ($strFontFamily == 'Arial')
			{
				$strFontFamily	= 'Arial, Sans-Serif';
			}

			$strTextColor	=	get_option('free01_text_color', '#000');
			$strLinkColor	=	get_option('free01_link_color', '#000');
			$strBgColor	=	get_option('free01_bg_color', '#fff');
		?>
		<style media="all" type="text/css">
			body, h1, h2, h3, h4, h5 { font-size: <?php echo $numTmpFontSize.'px' ?>;}
			body { font-family: <?php echo $strFontFamily ?>;}
			body { color: <?php echo $strTextColor ?>;}
			a, a:hover { color: <?php echo $strLinkColor ?>;}
			body, #wps-sidebar { background-color: <?php echo $strBgColor ?>;}
		
		</style>
	</head>
	<body <?php body_class(); ?>>
		<!--[if gt IE 8]> <div id="wps-site-wrapper" class="wpscls-ie wpscls-ie9"> <![endif]-->
		<!--[if IE 8]> <div id="wps-site-wrapper" class="wpscls-ie wpscls-ie8"> <![endif]-->
		<!--[if IE 7]> <div id="wps-site-wrapper" class="wpscls-ie wpscls-ie7"> <![endif]-->
		<!--[if IE 6]> <div id="wps-site-wrapper" class="wpscls-ie wpscls-ie6"> <![endif]-->
		<!--[if !IE]><!--> <div id="wps-site-wrapper"> <!--<![endif]-->
		
			<!-- Inner of the site	-->
  		<div id="wps-site-inner">
				
				<?php get_sidebar(); ?>
				
				<!-- Content of the site	-->
				<div id="wps-content">
				