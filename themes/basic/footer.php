	</div>
	<!-- /body -->
		
	<div id="footerwrap">
		<footer id="footer" class="pagewidth clearfix">
			
			<div class="footer-widgets clearfix">

				<?php 
					$footer_widget_option = (themify_get('setting-footer_widgets') == "") ? "footerwidget-3col" : themify_get('setting-footer_widgets');
					if($footer_widget_option != ""){ ?>
						  <?php
						  $columns = array('footerwidget-4col' 	=> array('col4-1','col4-1','col4-1','col4-1'),
												 'footerwidget-3col'	=> array('col3-1','col3-1','col3-1'),
												 'footerwidget-2col' 	=> array('col4-2','col4-2'),
												 'footerwidget-1col' 	=> array('') );
						  $x=0;
						  ?>
						<?php foreach($columns[$footer_widget_option] as $col): ?>
								<?php 
									 $x++;
									 if($x == 1){ 
										  $class = "first"; 
									 } else {
										  $class = "";	
									 }
								?>
								<div class="<?php echo $col;?> <?php echo $class; ?>">
									 <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Footer_Widget_'.$x) ) ?>
								</div>
						  <?php endforeach; ?>
				<?php } ?>

			</div>
			<!-- /.footer-widgets -->
	
			<p class="back-top"><a href="#header">&uarr;</a></p>
		
			<?php if (function_exists('wp_nav_menu')) {
				wp_nav_menu(array('theme_location' => 'footer-nav' , 'fallback_cb' => '' , 'container'  => '' , 'menu_id' => 'footer-nav' , 'menu_class' => 'footer-nav')); 
			} ?>
	
			<div class="footer-text clearfix">
				<div class="one"><?php if(themify_get('setting-footer_text_left') != ""){ echo themify_get('setting-footer_text_left'); } else { echo '&copy; <a href="'.get_option('home').'">'.get_bloginfo('name').'</a> '.date('Y'); } ?></div>
				<div class="two"><?php if(themify_get('setting-footer_text_right') != ""){ echo themify_get('setting-footer_text_right'); } else { echo 'Powered by <a href="http://wordpress.org">WordPress</a>  &bull; <a href="http://themify.me">Themify WordPress Themes</a>'; } ?></div>
			</div>
			<!-- /footer-text --> 
	
		</footer>
		<!-- /#footer --> 
	</div>
	<!-- /#footerwrap -->
	
</div>
<!-- /#pagewrap -->

<?php
/**
 *  Stylesheets and Javascript files are enqueued in theme-functions.php
 */
?>

<!-- wp_footer -->
<?php wp_footer(); ?>

</body>
</html>