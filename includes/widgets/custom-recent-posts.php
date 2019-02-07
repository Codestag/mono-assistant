<?php
if ( ! class_exists( 'Mono_Custom_Recent_Posts' ) ) :
/**
 * Displays latest blog posts.
 *
 * @since 1.0.0.
 * @package Mono
 */
class Mono_Custom_Recent_Posts extends Stag_Widget {
	public function __construct() {
		$this->widget_id          = 'mono_custom_recent_posts';
		$this->widget_cssclass    = 'mono_custom_recent_posts';
		$this->widget_description = esc_html__( 'Displays recent posts from Blog.', 'mono-assistant' );
		$this->widget_name        = esc_html__( 'Custom Recent Posts', 'mono-assistant' );
		$this->settings           = array(
			'title' => array(
				'type'  => 'text',
				'std'   => 'Latest Posts',
				'label' => esc_html__( 'Title:', 'mono-assistant' ),
			),
			'count' => array(
				'type'  => 'number',
				'std'   => '3',
				'label' => esc_html__( 'Number of posts to show:', 'mono-assistant' ),
			),
			'post_date' => array(
				'type'  => 'checkbox',
				'std'   => true,
				'label' => esc_html__( 'Display Post Date?', 'mono-assistant' ),
			),
			'category' => array(
				'type'  => 'category',
				'std'   => '0',
				'label' => esc_html__( 'Post Category:', 'mono-assistant' ),
			),
		);

		parent::__construct();
	}

	/**
	 * Widget function.
	 *
	 * @see WP_Widget
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	function widget( $args, $instance ) {
		if ( $this->get_cached_widget( $args ) )
			return;

		ob_start();

		extract( $args );

		$title      = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$count      = $instance['count'];
		$show_date  = $instance['post_date'];
		$category   = $instance['category'];

		$posts      = wp_get_recent_posts( array( 'post_type' => 'post', 'numberposts' => $count, 'post_status' => 'publish', 'category' => $category ), OBJECT );
		$posts_page = get_option( 'page_for_posts' );

		if ( 0 == $posts_page ) {
			$posts_page = home_url();
		} else {
			$posts_page = get_permalink( $posts_page );
		}

		global $post;

		echo  $before_widget;

		if ( $title ) :
			echo  $before_title;
			echo  $title;
			echo  $after_title;
		endif;

		?>

		<ul>
			<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
				<li<?php if ( has_post_thumbnail() ) echo ' class="has-thumbnail"'; ?>>
					<?php
					if ( has_post_thumbnail() ) {
						echo get_the_post_thumbnail( get_the_ID(), array( 70, 70 ) );
					}
					?>

					<div class="recent-post-meta">
						<?php if ( $show_date ) : ?>
							<span class="post-date"><?php echo get_the_date(); ?></span>
						<?php endif; ?>

						<a href="<?php the_permalink(); ?>"><?php get_the_title() ? the_title() : the_ID(); ?></a>
					</div>
				</li>
			<?php endforeach;

			remove_all_filters( 'subtitle_view_supported' );
			wp_reset_postdata();

			?>
		</ul>

		<?php
		echo  $after_widget;

		$content = ob_get_clean();

		echo  $content;

		$this->cache_widget( $args, $content );
	}

	/**
	 * Registers the widget with the WordPress Widget API.
	 *
	 * @return void.
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}
}
endif;

add_action( 'widgets_init', array( 'Mono_Custom_Recent_Posts', 'register' ) );
