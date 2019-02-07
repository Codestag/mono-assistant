<?php
if ( ! class_exists( 'Mono_Widget_Featured_Post' ) ) :
/**
 * Display static content from an specific page.
 *
 * @since Mono 1.0.0.
 *
 * @package Mono
 */
class Mono_Widget_Featured_Post extends Stag_Widget {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->widget_id          = 'mono_featured_post';
		$this->widget_cssclass    = 'mono_featured_post';
		$this->widget_description = esc_html__( 'Displays a post as a featured post.', 'mono-assistant' );
		$this->widget_name        = esc_html__( 'Section: Featured Post', 'mono-assistant' );
		$this->settings           = array(
			'page' => array(
				'type'        => 'text',
				'std'         => '',
				'description' => esc_html__( 'Enter a Post or Page ID to display as featured post.', 'mono-assistant' ),
				'label'       => esc_html__( 'Page ID:', 'mono-assistant' ),
				'datalist'    => get_all_page_ids(),
			),
			'show_excerpt' => array(
				'type' => 'checkbox',
				'std' => false,
				'label' => esc_html__( 'Show Post Excerpt', 'mono-assistant' ),
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

		$id           = absint( $instance['page'] );

		// Bail, if post doesn't exists.
		if ( false === get_post_status( $id ) ) return;

		$post         = get_post( $id );
		$show_excerpt = $instance['show_excerpt'];

		echo  $before_widget;

		$thumbnail_image = get_the_post_thumbnail_url( $id, 'full' );

		$subtitle = get_post_meta( $id, '_subtitle', true );
		global $mono_settings;

		?>

		<section class="post-cover post-cover-<?php echo esc_attr( $id ) ?>">
			<?php if ( has_post_thumbnail( $id ) ) : ?>
			<style type="text/css">
			.mono_featured_post .post-cover-<?php echo esc_attr( $id ); ?> { background-image: url(<?php echo esc_url( $thumbnail_image ); ?>); }
			</style>
			<?php endif; ?>

			<a href="<?php echo get_permalink( $id ); ?>" class="link-overlay"></a>

			<div class="container">

				<div class="inner-container">
					<div class="post-meta">

						<?php if ( false === $mono_settings->get_value( 'hide-author-avatar' ) ) : ?>
						<div class="author-avatar">
							<a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( $post->post_author ) ) ) ?>">
								<?php echo get_avatar( get_the_author_meta( 'email', $post->post_author ), '105' ); ?>
							</a>
						</div>
						<?php endif; ?>

						<span class="posted-on">
							<a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark"><time class="entry-date published" datetime="<?php echo esc_attr( get_the_time( 'c', $post->ID ) ); ?>"><?php echo get_the_time( get_option( 'date_format' ), $post->ID ) ?></time></a>
						</span>
						<span class="byline">
							<span class="author vcard">
								<a href="<?php echo get_author_posts_url( $post->post_author ); ?>">
									<?php the_author_meta( 'display_name', $post->post_author ); ?>
								</a>
							</span>
						</span>

						<?php

						if ( 'post' == get_post_type( $post->ID ) ) {
							/* translators: used between list items, there is a space after the comma */
							$categories_list = get_the_category_list( esc_html__( ', ', 'mono-assistant' ), '', $post->ID );
							if ( $categories_list && mono_categorized_blog() ) {
								printf( '<span class="cat-links">' . esc_attr__( 'In %1$s', 'mono-assistant' ) . '</span>', $categories_list );
							}
						}

						?>
					</div>

					<h1 class="entry-title">
						<span class="entry-title-primary"><?php echo esc_attr( $post->post_title ); ?></span>

						<?php if ( '' != $subtitle ) : ?>
						<span class="entry-subtitle"><?php echo esc_attr( $subtitle ); ?></span>
						<?php endif; ?>
					</h1>

					<?php $excerpt = wp_trim_words( $post->post_content, 35 ); ?>

					<?php if ( true == $show_excerpt ) : ?>
					<p class="entry-excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
				</div>

			</div>
		</section>

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

add_action( 'widgets_init', array( 'Mono_Widget_Featured_Post', 'register' ) );
