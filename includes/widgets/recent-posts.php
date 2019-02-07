<?php
if ( ! class_exists( 'Mono_Recent_Posts' ) ) :
/**
 * Displays latest blog posts.
 *
 * @since 1.0.0.
 * @package Mono
 */
class Mono_Recent_Posts extends Stag_Widget {
	public function __construct() {
		$this->widget_id          = 'mono_recent_posts';
		$this->widget_cssclass    = 'mono_recent_posts';
		$this->widget_description = esc_html__( 'Displays recent posts from Blog.', 'mono-assistant' );
		$this->widget_name        = esc_html__( 'Section: Recent Posts', 'mono-assistant' );
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
			'columns' => array(
				'type'        => 'number',
				'std'         => '2',
				'max'         => '4',
				'description' => esc_html__( 'Choose the number of post columns between 1 - 4', 'mono-assistant' ),
				'label'       => esc_html__( 'Number of Columns:', 'mono-assistant' ),
			),
			'widget_background' => array(
				'type'  => 'image',
				'std'   => null,
				'label' => esc_html__( 'Widget Image Background:', 'mono-assistant' ),
				'description' => esc_html__( 'Only applicable for Compact widget layout', 'mono-assistant' ),
			),
			'layout' => array(
				'label'   => esc_html__( 'Layout:', 'mono-assistant' ),
				'type'    => 'select',
				'std'     => 'grid',
				'options' => array(
					'grid'    => esc_html__( 'Grid', 'mono-assistant' ),
					'compact' => esc_html__( 'Compact', 'mono-assistant' ),
					'tile'    => esc_html__( 'Tile', 'mono-assistant' ),
				),
			),
			'category' => array(
				'type'  => 'categories',
				'std'   => '0',
				'label' => esc_html__( 'Post Category:', 'mono-assistant' ),
			),
			'button_text' => array(
				'type'  => 'text',
				'std'   => esc_html__( 'See All Posts', 'mono-assistant' ),
				'label' => esc_html__( 'Button Text:', 'mono-assistant' ),
			),
			'post_meta' => array(
				'type'  => 'checkbox',
				'std'   => false,
				'label' => esc_html__( 'Hide Post Meta?', 'mono-assistant' ),
			),
			'post_excerpt' => array(
				'type'  => 'checkbox',
				'std'   => false,
				'label' => esc_html__( 'Hide Post Excerpt?', 'mono-assistant' ),
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

		$layout = esc_attr( $instance['layout'] );

		echo  $before_widget;

		// Select the layout
		if ( 'grid' == $layout ) {
			$this->layout_grid( $args, $instance );
		} else if ( 'compact' == $layout ) {
			$this->layout_compact( $args, $instance );
		} else if ( 'tile' == $layout ) {
			$this->layout_tile( $args, $instance );
		}

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

	public function layout_grid( $args, $instance ) {
		extract( $args );

		$title             = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$count             = $instance['count'];
		$category          = $instance['category'];
		$columns           = absint( $instance['columns'] );
		$hide_post_meta    = $instance['post_meta'];
		$hide_post_excerpt = $instance['post_excerpt'];
		$button_text       = $instance['button_text'];

		$posts      = wp_get_recent_posts( array( 'post_type' => 'post', 'numberposts' => $count, 'post_status' => 'publish', 'category' => $category ), OBJECT );
		$posts_page = get_option( 'page_for_posts' );

		// If category not selected.
		if ( 0 === absint( $category ) ) {
			if ( 0 === absint( $posts_page ) ) {
				$posts_page = home_url();
			} else {
				$posts_page = get_permalink( $posts_page );
			}
		} else {
			// if category selected.
			$posts_page = get_category_link( absint( $category ) );
		}

		global $post;

		?>

		<div class="recent-posts-layout-grid">
			<div class="container">
				<div class="title-wrap">
					<?php if ( $title ) :
						echo  $before_title . $title . $after_title;
					endif; ?>

					<?php if ( '' != $button_text ) : ?>
					<a href="<?php echo esc_url( $posts_page ); ?>" class="button"><?php echo esc_attr( $button_text ); ?></a>
					<?php endif; ?>
				</div>

				<div class="posts-list grid">
					<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
						<?php
						$title_attribute = sprintf(
							__( 'Read now &#8212; %s', 'mono-assistant' ),
							the_title_attribute( array( 'echo' => false ) )
						);

						$post_class = 'grid__col';
						$post_class .= ' grid__col--1-of-' . $columns;

						?>

						<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>

							<?php if ( has_post_thumbnail() ) : ?>
							<div class="post-thumbnail">
								<a href="<?php the_permalink(); ?>" rel="bookmark">
									<?php the_post_thumbnail( 'large' ); ?>
								</a>
							</div>
							<?php endif; ?>

							<?php if ( ! $hide_post_meta ) : ?>
							<div class="post-meta">
								<?php mono_posted_on(); ?>
							</div>
							<?php endif; ?>

							<header class="entry-header">
								<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_attribute ); ?>" rel="bookmark">
									<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
								</a>
							</header>

							<?php if ( ! $hide_post_excerpt ) : ?>
							<div class="entry-excerpt">
								<?php the_excerpt(); ?>
							</div>
							<?php endif; ?>
						</article><!-- #post-## -->

					<?php endforeach;

					remove_all_filters( 'subtitle_view_supported' );

					wp_reset_postdata();

					?>
				</div>
			</div>
		</div>

		<?php
	}

	public function layout_compact( $args, $instance ) {
		extract( $args );

		$title             = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$count             = $instance['count'];
		$category          = $instance['category'];
		$columns           = absint( $instance['columns'] );
		$hide_post_meta    = $instance['post_meta'];
		$hide_post_excerpt = $instance['post_excerpt'];
		$widget_background = esc_url( $instance['widget_background'] );
		$button_text       = $instance['button_text'];

		$posts      = wp_get_recent_posts( array( 'post_type' => 'post', 'numberposts' => $count, 'post_status' => 'publish', 'category' => $category ), OBJECT );
		$posts_page = get_option( 'page_for_posts' );

		// If category not selected.
		if ( 0 === absint( $category ) ) {
			if ( 0 === absint( $posts_page ) ) {
				$posts_page = home_url();
			} else {
				$posts_page = get_permalink( $posts_page );
			}
		} else {
			// if category selected.
			$posts_page = get_category_link( absint( $category ) );
		}

		global $post;
		?>

		<div class="recent-posts-layout-compact">
			<?php if ( '' != $widget_background ) : ?>
			<div class='widget-background' style="background-image:url(<?php echo esc_url( $widget_background ) ?>);"></div>
			<?php endif; ?>

			<div class="container">
				<div class="title-wrap">
					<?php if ( $title ) :
						echo  $before_title . $title . $after_title;
					endif; ?>

					<?php if ( '' != $button_text ) : ?>
					<a href="<?php echo esc_url( $posts_page ); ?>" class="button"><?php echo esc_attr( $button_text ); ?></a>
					<?php endif; ?>
				</div>

				<div class="grid">
					<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
						<?php
						$title_attribute = sprintf(
							__( 'Read now &#8212; %s', 'mono-assistant' ),
							the_title_attribute( array( 'echo' => false ) )
						);

						$post_class = 'grid__col';
						$post_class .= ' grid__col--1-of-' . $columns;

						?>

						<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>

							<?php if ( ! $hide_post_meta ) : ?>
							<div class="post-meta">
								<?php mono_posted_on(); ?>
							</div>
							<?php endif; ?>

							<header class="entry-header">
								<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_attribute ); ?>" rel="bookmark">
									<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
								</a>
							</header>

							<?php if ( ! $hide_post_excerpt ) : ?>
							<div class="entry-excerpt">
								<?php the_excerpt(); ?>
							</div>
							<?php endif; ?>
						</article><!-- #post-## -->

					<?php endforeach;

					remove_all_filters( 'subtitle_view_supported' );

					wp_reset_postdata();

					?>
				</div>
			</div>
		</div>

		<?php
	}

	public function layout_tile( $args, $instance ) {
		extract( $args );

		$title             = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$count             = $instance['count'];
		$category          = $instance['category'];
		$columns           = absint( $instance['columns'] );
		$hide_post_meta    = $instance['post_meta'];
		$hide_post_excerpt = $instance['post_excerpt'];
		$widget_background = esc_url( $instance['widget_background'] );
		$button_text       = $instance['button_text'];

		$posts      = wp_get_recent_posts( array( 'post_type' => 'post', 'numberposts' => $count, 'post_status' => 'publish', 'category' => $category ), OBJECT );
		$posts_page = get_option( 'page_for_posts' );

		// If category not selected.
		if ( 0 === absint( $category ) ) {
			if ( 0 === absint( $posts_page ) ) {
				$posts_page = home_url();
			} else {
				$posts_page = get_permalink( $posts_page );
			}
		} else {
			// if category selected.
			$posts_page = get_category_link( absint( $category ) );
		}

		global $post;

		?>

		<div class="recent-posts-layout-tile">
			<div class="container">
				<div class="title-wrap">
					<?php if ( $title ) :
						echo  $before_title . $title . $after_title;
					endif; ?>

					<?php if ( '' != $button_text ) : ?>
					<a href="<?php echo esc_url( $posts_page ); ?>" class="button"><?php echo esc_attr( $button_text ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="grid">
				<?php foreach ( $posts as $post ) : setup_postdata( $post ); ?>
					<?php
					$title_attribute = sprintf(
						__( 'Read now &#8212; %s', 'mono-assistant' ),
						the_title_attribute( array( 'echo' => false ) )
					);

					$post_class = 'grid__col';
					$post_class .= ' grid__col--1-of-' . $columns;

					?>

					<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>

						<?php if ( has_post_thumbnail() ) : ?>
						<style type="text/css">
						.recent-posts-layout-tile .post-<?php the_ID(); ?> { background-image: url(<?php echo esc_url( get_the_post_thumbnail_url() ); ?>); }
						</style>
						<?php endif; ?>

						<div class="background-overlay"></div>

						<div class="text-container">
							<?php if ( ! $hide_post_meta ) : ?>
							<div class="post-meta">
								<?php mono_posted_on(); ?>
							</div>
							<?php endif; ?>

							<header class="entry-header">
								<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( $title_attribute ); ?>" rel="bookmark">
									<?php the_title( '<h2 class="entry-title">', '</h2>' ); ?>
								</a>
							</header>

							<?php if ( ! $hide_post_excerpt ) : ?>
							<div class="entry-excerpt">
								<?php the_excerpt(); ?>
							</div>
							<?php endif; ?>
						</div>
					</article><!-- #post-## -->

				<?php endforeach;

				remove_all_filters( 'subtitle_view_supported' );

				wp_reset_postdata();

				?>
			</div>
		</div>

		<?php
	}
}
endif;

add_action( 'widgets_init', array( 'Mono_Recent_Posts', 'register' ) );
