<?php
/**
 * Template Name: Front Page
 *
 * Magazine layout with several loop areas and a featured content area (slider). This template must be manually set for a page. Then the same page can be set as a Front page from Settings -> Reading -> Front page displays -> A static page.
 *
 * @package Oxygen
 * @subpackage Template
 */

get_header(); // Loads the header.php template. ?>

	<?php get_template_part( 'featured-content' ); // Loads the featured-content.php template. ?>
	
	<div class="aside">
	
		<?php get_template_part( 'menu', 'secondary' ); // Loads the menu-secondary.php template.  ?>
		
		<?php get_sidebar( 'primary' ); // Loads the sidebar-primary.php template. ?>
	
	</div>

	<?php do_atomic( 'before_content' ); // oxygen_before_content ?>
	
	<div class="content-wrap">

		<div id="content">		
	
			<?php do_atomic( 'open_content' ); // oxygen_open_content ?>
	
			<div class="hfeed">
				
				
				<div class="hfeed-more">				
					
					<?php $args = array( 'post__not_in' => get_option( 'sticky_posts' ), 'posts_per_page' => 12, 'meta_key' => '_oxygen_post_location', 'meta_value' => 'secondary' ); ?>
					
					<?php $loop = new WP_Query( $args ); ?>
					
					<?php if ( $loop->have_posts() ) : ?>
					
						<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
		
							<div id="post-<?php the_ID(); ?>" class="<?php hybrid_entry_class(); ?>">
										
								<?php echo apply_atomic_shortcode( 'entry_title', '[entry-title]' ); ?>
									
								<?php echo apply_atomic_shortcode( 'byline', '<div class="byline">' . __( '[entry-published] / by [entry-author] / in [entry-terms taxonomy="category"] [entry-edit-link before=" / "]', 'oxygen' ) . '</div>' ); ?>
	
							</div><!-- .hentry -->
		
						<?php endwhile; ?>			
		
					<?php else : ?>
		
					<?php endif; ?>				
		
				</div><!-- .hfeed-more -->


				<h4 class="section-title">お知らせ</h4>
<ul style="list-style:none">
  <?php query_posts('posts_per_page=5'); ?>
  <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
  <li>
  <span class="date">
  <?php the_time('Y年n月j日'); ?>
  <?php
    $days=30;
    $today=date('U'); $entry=get_the_time('U');
    $diff1=date('U',($today - $entry))/86400;
    if ($days > $diff1) {
  echo '<img src="images/new.gif" alt="New" />';
  }
  ?>
  </span>
    <a href="<?php the_permalink(); ?>"><?php the_title();?></a>
  </li>
  <?php endwhile; endif; ?>
  <?php wp_reset_query(); ?>
</ul>
			
			</div><!-- .hfeed -->
	
			<?php do_atomic( 'close_content' ); // oxygen_close_content ?>
	
		</div><!-- #content -->
	
		<?php do_atomic( 'after_content' ); // oxygen_after_content ?>

<?php get_footer(); // Loads the footer.php template. ?>