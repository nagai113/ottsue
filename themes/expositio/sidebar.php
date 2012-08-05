				<!-- Left site bar	-->
  			<div id="wps-sidebar">
					
					<h1><a href="<?php echo site_url('') ?>" title="<?php echo __('Home page', 'free01') ?>"><?php echo bloginfo( 'name' ); ?> </a> </h1>
					
					<!-- Main menu	-->
					<div class="wpscls-menu-main">
						<?php wp_nav_menu(array('menu' => __('Main menu', 'free01'), 'theme_location' => __('Main menu', 'free01'), 'depth' => 0, 'walker' => new extended_walker)); ?>
						<div class="clr"></div>
					</div>
					<!-- /Main menu	-->
					
					<!-- Cate post	-->
					<div class="wpscls-cate-post">
						<?php
						$arrArgs = array(
							'type'                     => 'post',
							'child_of'                 => 0,
							'orderby'                  => 'name',
							'order'                    => 'ASC',
							'taxonomy'                 => 'category',
							'pad_counts'               => false 
						);
							
						$rows_cate	=	get_categories($arrArgs);
						$strResult	=	'';
						foreach($rows_cate as $numKey	=>	$row_cate) {
							$strResult	.=	'<h4>'.$row_cate->cat_name.'</h4>';
							
							$arrArgs = array(
								'cat'                    	=> $row_cate->cat_ID,
								'posts_per_page'           => 0,
								'post_type'              	=> '',
								'orderby'                 => 'menu_order',
								'order'                   => 'ASC'
							);
							
							$the_query = new WP_Query( $arrArgs );
							$rows_tmp	=	$the_query->posts;
							
							if (count($rows_tmp) > 0)
							{
								$strResult	.=	'<ul class="wpscls-cate-post-list">';
								foreach ($rows_tmp as $numKey01 => $row_tmp)
								{
									$strClass	=	'wps-item' ;
									$strClass	.=	(is_singular() && $row_tmp->ID	==	get_the_ID()) ? ' current' : '' ;
									$strResult	.=	'<li>'.'<a href="'.get_permalink( $row_tmp->ID ).'" title="'.$row_tmp->post_title.'" class="'.$strClass.'">'.$row_tmp->post_title.'</a>'.'</li>';
								}
								$strResult	.=	'</ul>';
							}
						}
						echo $strResult;
						?>
						<div class="clr"></div>
					</div>
					<!-- /Cate post	-->
					
					<!-- Copyright	-->
					<div class="wpscls-copyright">
						<?php
							$numStartYear	=	2011;
							$strYear	=	date('Y') > $numStartYear ? $numStartYear.' - '.date('Y') : $numStartYear;
						?>
						<?php echo sprintf(__('&copy; %s'), $strYear) ?>
						<br />
						<?php echo sprintf(__('Theme by %s'), '<a class="wpscls-logowps" href="'.'http://wpshower.com/'.'"> WPShower </a>') ?>
					</div>
					<!-- /Copyright	-->
					
					<div class="clr"></div>
				</div>
				<!-- /Left site bar	-->