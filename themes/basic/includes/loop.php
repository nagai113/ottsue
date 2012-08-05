<?php if(!is_single()) : global $more; $more = 0; endif; //enable more link ?>

<?php global $post_query_category, $post_layout, $display_content, $hide_title, $unlink_title, $hide_meta, $hide_date, $hide_image, $unlink_image, $image_width, $image_height, $height, $width; ?>

<?php $categories = wp_get_post_categories(get_the_ID()); ?>
<?php foreach($categories as $cat): ?>
	<?php $class .= " cat-".$cat; ?>
<?php endforeach; ?>

<article id="post-<?php the_ID(); ?>" <?php post_class("post clearfix $class"); ?>>
	
	<?php if ( themify_get('external_link') != ''): ?>
		<?php $link = themify_get("external_link"); ?>
	<?php elseif ( themify_get('lightbox_link') != ''): ?>
		<?php $link = themify_get("lightbox_link")."' class='lightbox' rel='prettyPhoto[post]"; ?>
	<?php else: ?>
		<?php $link = get_permalink(); ?>
	<?php endif; ?>
		
	<?php if(is_single() && $hide_image != "yes"): ?>				

		<?php if($unlink_image == "yes"):  ?>
			<?php themify_image("field_name=post_image, image, wp_thumb&setting=image_post_single&w=".$width."&h=".$height."&before=<figure class='post-image " . themify_get('setting-image_post_single_align') . "'>&after=</figure>"); ?>
		<?php else: ?>
			<?php themify_image("field_name=post_image, image, wp_thumb&setting=image_post_single&w=".$width."&h=".$height."&before=<figure class='post-image " . themify_get('setting-image_post_single_align') . "'><a href='".urlencode($link)."'>&after=</a></figure>"); ?>
		<?php endif; ?>   
	
	<?php elseif($post_query_category != "" && $hide_image != "yes"): ?>
					
		<?php if($unlink_image == "yes"):  ?>			
			<?php themify_image("field_name=post_image, image, wp_thumb&w=".$width."&h=".$height."&before=<figure class='post-image'>&after=</figure>"); ?>
		<?php else: ?>
			<?php themify_image("field_name=post_image, image, wp_thumb&w=".$width."&h=".$height."&before=<figure class='post-image'><a href='".urlencode($link)."'>&after=</a></figure>"); ?>
		<?php endif; ?>   

	<?php else: ?>
			
		<?php if($hide_image != "yes"): ?>
			<?php if($unlink_image == "yes"):  ?>		
				<?php themify_image("field_name=post_image, image, wp_thumb&setting=image_post&w=".$width."&h=".$height."&before=<figure class='post-image " . themify_get('setting-image_post_align') . "'>&after=</figure>"); ?>
			<?php else: ?>
				<?php themify_image("field_name=post_image, image, wp_thumb&setting=image_post&w=".$width."&h=".$height."&before=<figure class='post-image " . themify_get('setting-image_post_align') . "'><a href='".urlencode($link)."'>&after=</a></figure>"); ?>
			<?php endif; ?>  
		<?php endif; ?>
			
	<?php endif; //post image ?>

	<div class="post-content">
	
		<?php if($hide_date != "yes"): ?>
			<time datetime="<?php the_time('o-m-d') ?>" class="post-date" pubdate><?php the_time('M j, Y') ?></time>
		<?php endif; //post date ?>

		<?php if($hide_title != "yes"): ?>
			<?php if($unlink_title == "yes"): ?>
				<h1 class="post-title"><?php the_title(); ?></h1>
			<?php else: ?>
				<h1 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
			<?php endif; //unlink post title ?> 
		<?php endif; //post title ?>    

		<?php if($hide_meta != 'yes'): ?>
			<p class="post-meta"> 
				<span class="post-author"><?php the_author_posts_link() ?></span>
				<span class="post-category"><?php the_category(', ') ?></span>
				<?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
				<?php if ( comments_open() ) : ?>
					<span class="post-comment"><?php comments_popup_link( __( '0 Comment', 'themify' ), __( '1 Comment', 'themify' ), __( '% Comments', 'themify' ) ); ?></span>
				<?php endif; //post comment ?>
			</p>
		<?php endif; //post meta ?>    
		
		<?php if($display_content == 'excerpt'): ?>
	
			<?php the_excerpt(); ?>
	
		<?php elseif($display_content == 'none'): ?>
	
		<?php else: ?>
		
			<?php the_content(__((themify_check('setting-default_more_text')) ? themify_get('setting-default_more_text') : 'More &rarr;','themify')); ?>
		
		<?php endif; //display content ?>
		
		<?php edit_post_link(__('Edit', 'themify'), '[', ']'); ?>
		
	</div>
	<!-- /.post-content -->
	
</article>
<!-- /.post -->