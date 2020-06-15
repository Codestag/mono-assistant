<?php
/**
 * Widget Base Class.
 *
 * @package Mono
 */

class Stag_Widget extends WP_Widget {

	public $widget_cssclass;
	public $widget_description;
	public $widget_id;
	public $widget_name;
	public $settings;
	public $control_ops;

	/**
	 * Constructor
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => $this->widget_cssclass,
			'description' => $this->widget_description,
		);

		parent::__construct( $this->widget_id, $this->widget_name, $widget_ops, $this->control_ops );

		add_action( 'save_post', array( $this, 'delete_widget_cache' ) );
		add_action( 'deleted_post', array( $this, 'delete_widget_cache' ) );
		add_action( 'switch_theme', array( $this, 'delete_widget_cache' ) );
	}

	/**
	 * Get cached widget function.
	 *
	 * @return boolean
	 */
	function get_cached_widget( $args ) {
		global $post;

		if ( isset( $post->ID ) ) {
			$args[ 'widget_id' ] = $args[ 'widget_id' ] . '-' . $post->ID;
		}

		$cache = wp_cache_get( $this->widget_id, 'widget' );

		if ( ! is_array( $cache ) )
			$cache = array();

		if ( isset( $cache[ $args[ 'widget_id' ] ] ) ) {
			echo $cache[ $args[ 'widget_id' ] ];
			return true;
		}

		return false;
	}

	/**
	 * Cache the widget.
	 *
	 * @return void
	 */
	public function cache_widget( $args, $content ) {
		if ( ! isset( $args[ 'widget_id' ] ) ) {
			$args[ 'widget_id' ] = rand(0, 100);
		}

		$cache[ $args[ 'widget_id' ] ] = $content;

		wp_cache_set( $this->widget_id, $cache, 'widget' );
	}

	/**
	 * Flush the cache.
	 *
	 * @return void
	 */
	public function delete_widget_cache() {
		wp_cache_delete( $this->widget_id, 'widget' );
	}

	/**
	 * Update function.
	 *
	 * @see WP_Widget->update
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		if ( ! $this->settings )
			return $instance;

		foreach ( $this->settings as $key => $setting ) {
			switch ( $setting[ 'type' ] ) {
				case 'textarea' :
					if ( current_user_can( 'unfiltered_html' ) )
						$instance[ $key ] = $new_instance[ $key ];
					else
						$instance[ $key ] = wp_kses_data( $new_instance[ $key ] );
				break;
				case 'multicheck' :
					$instance[ $key ] = maybe_serialize( $new_instance[ $key ] );
				break;
				case 'number' :
					$instance[ $key ] = absint( $new_instance[ $key ] );
				break;
				case 'text' :
				case 'checkbox' :
				case 'select' :
				case 'number' :
				case 'colorpicker' :
					$instance[ $key ] = sanitize_text_field( $new_instance[ $key ] );
				break;
				default :
					$instance[ $key ] = apply_filters( 'stag_widget_update_type_' . $setting[ 'type' ], $new_instance[ $key ], $key, $setting );
				break;
			}
		}

		$this->delete_widget_cache();

		return $instance;
	}

	/**
	 * Form function.
	 *
	 * @see WP_Widget->form
	 * @access public
	 * @param array $instance
	 * @return void
	 */
	function form( $instance ) {

		if ( ! $this->settings )
			return;

		foreach ( $this->settings as $key => $setting ) {
			$value = isset( $instance[ $key ] ) ? $instance[ $key ] : $setting[ 'std' ];

			$input_key = $this->get_field_id( $key );

			echo '<p>';

			switch ( $setting[ 'type' ] ) {
				case 'description' :
					?>
					<small class="stag-widget-description" style="color:#777;"><?php echo esc_html( $setting['std'] ); ?></small>
					<?php
				break;

				case 'image':
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="hidden" value="<?php echo esc_attr( $value ); ?>" />

					<style type="text/css">
					.image-container img { max-width:100%;margin:0 0 10px 0; }
					.image-container .placeholder { border:1px dashed #ddd;text-align:center;padding:15px;margin-bottom:10px; }

					<?php if ( '' != $value ) : ?>
					.image-container-<?php echo esc_attr( $input_key ); ?> .placeholder { display: none; }
					<?php endif; ?>

					</style>

					<div class="image-container image-container-<?php echo esc_attr( $input_key ); ?>">
						<div class="placeholder">
							<div class="inner">
								<span>
									<?php esc_html_e( 'No image selected', 'mono' ); ?>
								</span>
							</div>
						</div>

						<?php if ( '' != $value ) : ?>
							<img src="<?php echo esc_url( $value ) ?>" alt="media">
						<?php endif; ?>
					</div>

					<button class="button upload-button" id="image-selector-<?php echo esc_attr( $input_key ); ?>" data-text="<?php esc_attr_e( 'Choose Image', 'mono' ); ?>" data-title="<?php esc_attr_e( 'Select Image', 'mono' ); ?>" data-id="<?php echo esc_attr( $input_key ); ?>"><?php esc_html_e( 'Select Image', 'mono' ); ?></button>

					<?php if ( '' != $value ) : ?>
					<button type="button" class="button remove-button" id="remove-image-<?php echo esc_attr( $input_key ); ?>" data-id="<?php echo esc_attr( $input_key ); ?>"><?php esc_html_e( 'Remove', 'mono' ); ?></button>
					<?php endif; ?>

					<?php wp_enqueue_media(); ?>
					<script type="text/javascript">

					var file_frame;

					jQuery(document).ready(function($){

						jQuery( document ).on( "click", "#image-selector-<?php echo esc_js( $input_key ); ?>", function() {
							event.preventDefault();

							var $this = jQuery(this);

							// Create the media frame.
							file_frame = wp.media({
								button: {
									text: jQuery(this).data('text')
								},
								states: [
									new wp.media.controller.Library({
										title:     jQuery(this).data('title'),
										library:   wp.media.query({ type: 'image' }),
										multiple:  false,
										date:      false
									})
								]
							});

							// When an image is selected, run a callback.
							file_frame.on( 'select', function() {
								// We set multiple to false so only get one image from the uploader
								attachment = file_frame.state().get('selection').first().toJSON();

								$('#'+$this.data('id')).val(attachment.url);
								$this.prev('.image-container').html('<img src="'+ attachment.url +'" alt />');
							});


							// Finally, open the modal
							file_frame.open();
						});

						jQuery( document ).on( "click", "#remove-image-<?php echo esc_js( $input_key ); ?>", function() {
							event.preventDefault();

							var $this = jQuery(this);

							$this.prev().prev('.image-container').find('img').remove();
							$this.prev().prev('.image-container').find('.placeholder').show();
							$('#'+$this.data('id')).val('');
							$this.remove();
						});

					});

					</script>
					<?php
					break;

				case 'text' :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="<?php echo esc_attr( $value ); ?>" <?php if ( isset( $setting['datalist'] ) && is_array( $setting['datalist'] ) ) { echo 'list="list-' . $key . '"'; }; ?> />

					<?php
					if ( isset( $setting['datalist'] ) && is_array( $setting['datalist'] ) ) :
						echo '<datalist id="list-'. $key .'">';
						foreach( $setting['datalist'] as $key => $value ) {
							echo '<option value="' . esc_attr( $value ) . '">';
						}
						echo '</datalist>';
					endif;
					?>

					<?php
				break;

				case 'checkbox' :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>">
						<input type="checkbox" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="text" value="1" <?php checked( 1, esc_attr( $value ) ); ?>/>
						<?php echo esc_html( $setting[ 'label' ] ); ?>
					</label>
					<?php
				break;

				case 'multicheck' :
					$value = maybe_unserialize( $value );

					if ( ! is_array( $value ) )
						$value = array();
					?>
					<p><?php echo esc_attr( $setting[ 'label' ] ); ?></p>
					<p>
						<?php foreach ( $setting[ 'options' ] as $id => $label ) : ?>
						<label for="<?php echo sanitize_title( $label ); ?>-<?php echo esc_attr( $id ); ?>">
							<input type="checkbox" id="<?php echo sanitize_title( $label ); ?>-<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>[]" value="<?php echo esc_attr( $id ); ?>" <?php if ( in_array( $id, $value ) ) : ?>checked="checked"<?php endif; ?>/>
							<?php echo esc_attr( $label ); ?><br />
						</label>
						<?php endforeach; ?>
					</p>
					<?php
				break;

				case 'select' :
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
						<?php foreach ( $setting[ 'options' ] as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo esc_attr( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
				break;

				case 'page':
					$exclude_ids = implode( ',', array( get_option( 'page_for_posts' ), get_option( 'page_on_front' ) ) );
					$pages       = get_pages( 'sort_order=ASC&sort_column=post_title&post_status=publish&exclude='. $exclude_ids );
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
						<?php foreach ( $pages as $page ) : ?>
							<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $page->ID, $value ); ?>><?php echo esc_attr( $page->post_title ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
				break;

				case 'categories':
					$args = array( 'hide_empty' => 0 );

					if ( isset( $setting['taxonomy'] ) ) $args['taxonomy'] = $setting['taxonomy'];

					$categories = get_categories( $args );
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>">
						<option value="0"><?php esc_html_e( 'All', 'mono' ); ?></option>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( $cat->term_id, $value ); ?>><?php echo esc_attr( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php
				break;

				case 'number' :
					if ( ! isset( $setting['step'] ) ) $setting['step'] = '1';
					if ( ! isset( $setting['min'] ) ) $setting['min'] = '1';
					if ( ! isset( $setting['max'] ) ) $setting['max'] = '100';
					?>
					<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="number" step="<?php echo esc_attr( $setting[ 'step' ] ); ?>" min="<?php echo esc_attr( $setting[ 'min' ] ); ?>" max="<?php echo esc_attr( $setting[ 'max' ] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
					<?php
				break;

				case 'textarea' :
					?>
					<label for="<?php echo esc_html( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
					<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" rows="<?php echo esc_attr( $setting[ 'rows' ] ); ?>"><?php echo esc_html( $value ); ?></textarea>
					<?php
				break;

				case 'colorpicker' :
						wp_enqueue_script( 'wp-color-picker' );
						wp_enqueue_style( 'wp-color-picker' );
					?>
						<p style="margin-bottom:0;">
							<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>"><?php echo esc_html( $setting[ 'label' ] ); ?></label>
						</p>
						<input type="text" class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" data-default-color="<?php echo esc_attr( $setting['std'] ); ?>" value="<?php echo esc_attr( $value ); ?>" />
						<script>
							jQuery(document).ready(function($){
								$( 'input[name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>"]' ).wpColorPicker();
							});
						</script>
					<?php
				break;

				default :
					do_action( 'stag_widget_type_' . $setting[ 'type' ], $this, $key, $setting, $instance );
				break;
			}


			if ( isset( $setting[ 'description' ] ) && '' != $setting[ 'description' ] ) {
				echo '<small style="display:block;margin-top:7px;">' . $setting[ 'description' ] . '</small>';
			}

			echo '</p>';
		}
	}
}
