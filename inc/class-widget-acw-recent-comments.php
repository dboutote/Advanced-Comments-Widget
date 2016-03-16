<?php

/**
 * Widget_ACW_Recent_Comments Class
 *
 * Adds a Recent Comments widget with extended functionality
 *
 * @package ACW_Recent_Comments
 * @subpackage Widget_ACW_Recent_Comments
 *
 * @since 1.0
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


/**
 * Core class used to implement a Recent Comments widget.
 *
 * @since 1.0
 *
 * @see WP_Widget
 */
class Widget_ACW_Recent_Comments extends WP_Widget {


	/**
	 * Sets up a new Comments widget instance.
	 *
	 * @access public
	 *
	 * @since 1.0
	 */
	public function __construct()
	{
		$widget_options = array(
			'classname' => 'widget_acw_recent_comments',
			'description' => __( 'A recent comments widget with extended features.' )
			);

		$control_options = array();

		parent::__construct(
			'acw-recent-comments',           // $this->id_base
			__('Advanced Recent Comments'),  // $this->name
			$widget_options,                 // $this->widget_options
			$control_options                 // $this->control_options
		);

		$this->alt_option_name = 'widget_acw_recent_comments';
	}


	/**
	 * Outputs the content for the current Recent Comments widget instance.
	 *
	 * Applies 'widget_title' filter on $title to allow extension by plugins.
	 * Applies 'acw_widget_comment_query_args' filter on $query_args to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $instance Settings for the current Recent Comments widget instance.
	 */
	public function widget( $args, $instance )
	{
		if ( ! isset( $args['widget_id'] ) ){
			$args['widget_id'] = $this->id;
		}

		$instance['widget_id'] = $this->id;
		$instance['widget_number'] = $this->number;

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 5 ;
		if ( ! $number ) { $number = 5; };

		// post type
		if( 'all' === $instance['post_type'] ) {
			$post_types = $this->get_post_types();
			unset( $post_types['all'] );
			$_post_types = array_keys( $post_types );
		} else {
			$_post_types = $instance['post_type'];
		}

		// query
		$query_args = array(
			'number'      => $number,
			'status'      => 'approve',
			'post_status' => 'publish',
			'post_type'   => $_post_types,
		);

		if( ! empty( $instance['exclude_pings'] ) && $instance['exclude_pings'] ) {
			$query_args['type__not_in'] = 'pings';
		}

		// let devs filter the query
		$query_args = apply_filters( "acw_widget_comment_query_args", $query_args, $instance );

		$comments = get_comments( $query_args );
		
		// output
		$output = '';

		$output .= $args['before_widget'];

		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		$output .= '<div class="advanced-comments-widget acw-recent-comments acw-comments-wrap">';

		if ( is_array( $comments ) && $comments ) {

			// Prime cache for associated posts. (Prime post term cache if we need it for permalinks.)
			$post_ids = array_unique( wp_list_pluck( $comments, 'comment_post_ID' ) );
			_prime_post_caches( $post_ids, strpos( get_option( 'permalink_structure' ), '%category%' ), false );

			$output .= $this->start_list( $instance, $comments );

			ob_start();

			if( 'html5' === $instance['comment_format'] ) {
				$this->build_html5_comments( $comments, $instance );
			} else {
				$this->build_xhtml_comments( $comments, $instance );
			}

			$output .= ob_get_clean();

			$output .= $this->end_list( $instance, $comments );
		}

		$output .= '</div><!-- /.acw-comments-wrap -->';
		$output .= $this->colophon();
		$output .= $args['after_widget'];

		echo $output;
	}


	/**
	 * Handles updating settings for the current widget instance.
	 *
	 * Applies 'acw_widget_update_instance' filter on $instance to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance )
	{
		$instance                   = $old_instance;
		$instance['title']          = sanitize_text_field( $new_instance['title'] );
		$instance['post_type']      = sanitize_text_field( $new_instance['post_type'] );
		$instance['show_thumbs']    = isset( $new_instance['show_thumbs'] ) ? 1 : 0 ;
		$instance['thumb_size']     = absint( $new_instance['thumb_size'] );
		$instance['show_excerpt']   = isset( $new_instance['show_excerpt'] ) ? 1 : 0 ;
		$instance['excerpt_length'] = absint( $new_instance['excerpt_length'] );
		$instance['exclude_pings']  = isset( $new_instance['exclude_pings'] ) ? 1 : 0 ;
		$instance['number']         = absint( $new_instance['number'] );
		$instance['list_style']     = ( '' !== $new_instance['list_style'] ) ? sanitize_key( $new_instance['list_style'] ) : 'ul ';
		$instance['comment_format'] = ( '' !== $new_instance['comment_format'] ) ? sanitize_key( $new_instance['comment_format'] ) : 'xhtml ';

		$instance = apply_filters('acw_widget_update_instance', $instance, $new_instance, $old_instance );

		return $instance;
	}


	/**
	 * Outputs the settings form for the Recent Comments widget.
	 *
	 * Applies 'acw_form_fields' filter on form fields to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $instance Current settings.
	 */
	public function form( $instance )
	{
		$_comment_format = current_theme_supports( 'html5', 'comment-list' ) ? 'html5' : 'xhtml';
		$_list_style = ( 'html5' == $_comment_format ) ? 'div' : 'ul' ;

		$form_fields = array(
			'title'          => __('Recent Comments'),
			'post_type'      => 'post',
			'show_thumbs'    => 1,
			'thumb_size'     => 55,
			'number'         => 5,
			'show_excerpt'   => 1,
			'excerpt_length' => 50,
			'exclude_pings'  => 1,
			'comment_format' => $_comment_format,
			'list_style'     => $_list_style,
		);

		$form_fields = apply_filters( 'acw_form_fields', $form_fields );
		$instance = wp_parse_args( (array) $instance, $form_fields );
		?>

		<?php if( isset( $form_fields['title'] ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['post_type'] ) ) : ?>
			<?php $post_types = $this->get_post_types(); ?>
			<p>
				<label for="<?php echo $this->get_field_id('post_type'); ?>">
					<?php _e( 'Post Type:' ); ?>
				</label>
				<select name="<?php echo $this->get_field_name('post_type'); ?>" id="<?php echo $this->get_field_id('post_type'); ?>" class="widefat">
					<?php foreach( $post_types as $query_var => $label  ) { ?>
						<option value="<?php echo esc_attr( $query_var ); ?>" <?php selected( $instance['post_type'] , $query_var ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['show_thumbs'] ) ) : ?>
			<h3>Avatars</h3>
			<p>
				<input class="checkbox" type="checkbox" id="<?php echo $this->get_field_id( 'show_thumbs' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbs' ); ?>" <?php checked( $instance['show_thumbs'], 1 ); ?>/>
				<label for="<?php echo $this->get_field_id( 'show_thumbs' ); ?>">
					<?php _e( 'Display Avatars' ); ?>
				</label>
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['thumb_size'] ) ) : ?>
			<?php $thumb_size = absint( $instance['thumb_size'] ); ?>
			<p class="acw-thumb-size-wrap">
				<label for="<?php echo $this->get_field_id( 'thumb_size' ); ?>">
					<?php _e( 'Avatar/Thumbnail Size' ); ?>
				</label>
				<span class="acw-avatar" style="font-size: <?php echo $thumb_size; ?>px; height:<?php echo $thumb_size; ?>px; width:<?php echo $thumb_size;?>px">
					<i class="acw-icon dashicons dashicons-admin-users"></i>
				</span>
				<input class="widefat acw-thumb-size" id="<?php echo $this->get_field_id( 'thumb_size' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>" type="number" value="<?php echo absint( $instance['thumb_size'] ); ?>" />
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['show_excerpt'] ) ) : ?>
			<h3>Comment Content</h3>
			<p>
				<input id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" type="checkbox" <?php checked( $instance['show_excerpt'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'show_excerpt' ); ?>">
					<?php _e( 'Display Comment Excerpt' ); ?>
				</label>
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['excerpt_length'] ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>">
					<?php _e( 'Excerpt Length' ); ?>
				</label>
				<input class="widefat acw-excerpt-length" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" step="1" min="0" value="<?php echo absint( $instance['excerpt_length'] ); ?>" />
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['exclude_pings'] ) ) : ?>
			<p>
				<input id="<?php echo $this->get_field_id( 'exclude_pings' ); ?>" name="<?php echo $this->get_field_name( 'exclude_pings' ); ?>" type="checkbox" <?php checked( $instance['exclude_pings'], 1 ); ?> />
				<label for="<?php echo $this->get_field_id( 'exclude_pings' ); ?>">
					<?php _e( 'Exclude pingbacks and trackbacks' ); ?>
				</label>
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['number'] ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>">
					<?php _e( 'Number of comments to show:' ); ?>
				</label>
				<input class="widefat acw-number" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo absint( $instance['number'] ); ?>" size="3" />
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['list_style'] ) ) : ?>
			<p>
				<label for="<?php echo $this->get_field_id('list_style'); ?>">
					<?php _e( 'Comment List Format:' ); ?>
				</label>
				<select name="<?php echo $this->get_field_name('list_style'); ?>" id="<?php echo $this->get_field_id('list_style'); ?>" class="widefat">
					<option value="ul" <?php selected( $instance['list_style'] , 'ul' ); ?>><?php _e( 'Unordered List (ul)'); ?></option>
					<option value="ol" <?php selected( $instance['list_style'] , 'ol' ); ?>><?php _e( 'Ordered List (ol)'); ?></option>
					<option value="div" <?php selected( $instance['list_style'] , 'div' ); ?>><?php _e( 'Div (div)'); ?></option>
				</select>
			</p>
		<?php endif; ?>

		<?php if( isset( $form_fields['comment_format'] ) ) : ?>
			<p>
				<?php _e( 'Comment Format:' ); ?><br />
				<label>
					<input class="radio" id="<?php echo $this->get_field_id( 'comment_format' ); ?>" name="<?php echo $this->get_field_name( 'comment_format' ); ?>" type="radio" value="html5" <?php checked( $instance['comment_format'], 'html5'); ?>/>
					HTML5 &nbsp;
				</label>
				<label>
					<input class="radio" id="<?php echo $this->get_field_id( 'comment_format' ); ?>" name="<?php echo $this->get_field_name( 'comment_format' ); ?>" type="radio" value="xhtml" <?php checked( $instance['comment_format'], 'xhtml'); ?>/>
					XHTML
				</label>
			</p>
		<?php endif; ?>
	<?php
	}


	/**
	 * Retrieves public post types
	 *
	 * Only returns post types that have comments enabled.
	 * Applies 'acw_widget_allowed_post_types' filter on post types to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return array $_ptypes Filtered array of post types.
	 */
	public function get_post_types()
	{
		$post_type_args = apply_filters( 'acw_widget_post_type_args', array( 'public' => true) );
		$post_types = get_post_types( $post_type_args, 'objects' );

		$_ptypes = array();
		$_ptypes['all'] = __('All');

		foreach( $post_types as $post_type ){
			if ( post_type_supports( $post_type->name, 'comments' ) ) {
				$query_var = ( ! $post_type->query_var ) ? $post_type->name : $post_type->query_var ;
				$label = $post_type->labels->singular_name;
				$_ptypes[ $query_var ] = $label;
			}
		}

		$_ptypes = apply_filters( 'acw_allowed_post_types', $_ptypes );
		$_ptypes = ( ! is_array( $_ptypes ) ) ? (array) $_ptypes : $_ptypes ;

		// Clean the values (since it can be filtered by other plugins)
		$_ptypes = array_map('esc_html', $_ptypes);

		/**
		 * Flip to clean the keys (used as <option> values in <select> field on form)
		 * Note: Keys *should* be post-type names e.g., "post", "page", "event", etc.
		 */
		$_ptypes = array_flip( $_ptypes );
		$_ptypes = array_map('sanitize_key', $_ptypes);

		// Flip back
		$_ptypes = array_flip( $_ptypes );

		asort( $_ptypes );

		return $_ptypes;

	}


	/**
	 * Generate avatar markup
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param object $comment  Comment to display.
	 * @param array $instance Widget instance.
	 *
	 * @return string $avatar_string HTML of commenter avatar.
	 */
	public function get_comment_author_avatar( $comment, $instance )
	{
		$avatar_string = get_avatar( $comment, $instance['thumb_size'] );
		$comment_author_url = get_comment_author_url( $comment );
		if ( '' !== $comment_author_url ) {
			$avatar_string = sprintf(
				'<a href="%1$s" class="author-link url" rel="external nofollow">%2$s</a>',
				esc_url($comment_author_url),
				$avatar_string
			);
		};
		return $avatar_string;
	}


	/**
	 * Generate comment classes
	 *
	 * Applies 'acw_comment_class' filter on comment classes to allow extension by plugins.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param object $comment  Comment to display.
	 * @param array $instance Widget instance.
	 *
	 * @return string $class_str Filtered comment classes.
	 */
	public function get_acw_comment_class( $comment, $instance )
	{
		$type = ( empty( $comment->comment_type ) ) ? 'comment' : $comment->comment_type;

		$classes = array();
		$classes[] = 'comment';
		$classes[] = 'acw-comment';
		$classes[] = 'type-' . $type;
		$classes[] = 'acw-type-' . $type;
		$classes[] = 'recentcomments';

		if ( $comment->comment_parent > 0 ) {
			$classes[] = 'child-comment';
			$classes[] = 'parent-' . $comment->comment_parent;
		}

		$classes = apply_filters( 'acw_comment_class', $classes, $comment, $instance );
		$classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
		$classes = array_map('sanitize_html_class', $classes);

		$class_str = implode(' ', $classes);

		return $class_str;
	}


	/**
	 * Generates unique comment id based on widget instance
	 *
	 * Applies 'acw_comment_id' filter on comment ID to allow extension by plugins.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param object $comment Comment to display.
	 * @param array $instance Widget instance.
	 *
	 * @return string $acw_comment_id Filtered comment ID.
	 */
	public function get_acw_comment_id( $comment, $instance )
	{
		$acw_comment_id = $instance['widget_id'] . '-comment-' . $comment->comment_ID;

		return apply_filters( 'acw_comment_id', $acw_comment_id, $comment, $instance );
	}


	/**
	 * Retrieves comment content
	 *
	 * Applies 'acw_get_comment_content' filter on comment content to allow extension by plugins.
	 * Applies 'comment_text' filter on comment content to allow extension by plugins.
	 * Note: 'comment_text' is Core WordPress filter
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param object $comment  Comment to display.
	 * @param array $instance Widget instance.
	 *
	 * @return string $comment_content Filtered comment content.
	 */
	public function get_comment_content( $comment, $instance )
	{
		$comment_content = apply_filters('acw_comment_content', $comment->comment_content, $comment, $instance );

		return apply_filters( 'comment_text', $comment_content, $comment, $instance );
	}


	/**
	 * Builds HTML5 comment list
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $comments The comments returned from get_comments()
	 * @param array $instance Widget instance
	 *
	 * @return string $html5_comments HTML for comment list.
	 */
	public function build_html5_comments( $comments, $instance )
	{
		$html5_comments = '';

		foreach ( (array) $comments as $comment ) {
			$html5_comments .= $this->html5_comment( $comment, $instance );
		}

		return $html5_comments;
	}


	/**
	 * Builds XHTML comment list
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $comments The comments returned from get_comments()
	 * @param array $instance Widget instance
	 *
	 * @return string $xhtml_comments HTML for comment list.
	 */
	public function build_xhtml_comments( $comments, $instance )
	{
		$xhtml_comments = '';

		foreach ( (array) $comments as $comment ) {
			$xhtml_comments .= $this->xhtml_comment( $comment, $instance );
		}

		return $xhtml_comments;
	}


	/**
	 * Opens the comment list for the current Recent Comments widget instance.
	 *
	 * Applies 'acw_start_list' filter on $output to allow extension by plugins.
	 * Applies 'acw_comment_list_class' filter on list classes to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $instance Settings for the current Recent Comments widget instance.
	 * @param array $comments Comments to display.
	 *
	 * @return string $output Opening tag element for the comment list.
	 */
	public function start_list( $instance, $comments )
	{
        switch ( $instance['list_style'] ) {
            case 'div':
                $tag = 'div';
				break;
            case 'ol':
                $tag = 'ol';
                break;
            case 'ul':
            default:
                $tag = 'ul';
                break;
        }

		$classes = array();
		$classes[] = 'acw-comments-list';
		$classes = apply_filters( 'acw_comment_list_class', $classes, $instance, $comments );
		$classes = ( ! is_array( $classes ) ) ? (array) $classes : $classes ;
		$classes = array_map('sanitize_html_class', $classes);
		$class_str = implode(' ', $classes);

		$output = sprintf( '<%1$s class="%2$s">', $tag, $class_str );

		return apply_filters( 'acw_start_list', $output, $instance, $comments );
	}


	/**
	 * Closes the comment list for the current Recent Comments widget instance.
	 *
	 * Applies 'acw_end_list' filter on $output to allow extension by plugins.
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param array $instance Settings for the current Recent Comments widget instance.
	 * @param array $comments Comments to display.
	 *
	 * @return string $output Closing tag element for the comment list.
	 */
	public function end_list( $instance, $comments )
	{
        switch ( $instance['list_style'] ) {
            case 'div':
                $output = "</div>\n";
				break;
            case 'ol':
                $output = "</ol>\n";
                break;
            case 'ul':
            default:
                $output = "</ul>\n";
                break;
        }

		return apply_filters( 'acw_end_list', $output, $instance, $comments );
	}


	/**
	 * Outputs a single comment in the HTML5 format.
	 *
	 * @uses Advanced_Comments_Widget::get_acw_comment_id()
	 * @uses Advanced_Comments_Widget::get_acw_comment_class()
	 * @uses Advanced_Comments_Widget::get_comment_content()
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param object $comment WP_Comment Object
	 * @param object $instance Widget instance
	 */
	public function html5_comment( $comment, $instance )
	{
		$acw_comment_id = $this->get_acw_comment_id( $comment, $instance );
		$acw_comment_class = $this->get_acw_comment_class( $comment, $instance );
		$comment_content = $this->get_comment_content( $comment, $instance  );
		$tag = ( 'div' === $instance['list_style'] ) ? 'div' : 'li';
		?>

		<<?php echo $tag; ?> id="comment-<?php echo sanitize_html_class( $acw_comment_id ); ?>" class="<?php echo $acw_comment_class; ?>" >

			<article id="div-comment-<?php echo sanitize_html_class( $acw_comment_id ); ?>" class="comment-body acw-comment-body">

				<footer class="comment-meta acw-comment-meta">

					<?php if ( $instance['show_thumbs'] ) : ?>
						<span class="comment-avatar acw-comment-avatar">
							<?php echo $this->get_comment_author_avatar( $comment, $instance ); ?>
						</span>
					<?php endif; ?>
					
					<span class="comment-header">
						<?php
						printf(
							_x( '%1$s <span class="on">on</span> %2$s', 'widgets' ),
							'<span class="comment-author acw-comment-author">' . get_comment_author_link( $comment ) . '</span>',
							'<span class="comment-link acw-comment-link"><a class="comment-link acw-comment-link" href="' . esc_url( get_comment_link( $comment ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a></span>'
						);
						?>
					</span>

				</footer>

				<?php if ( $instance['show_excerpt'] ) : ?>
					<div class="comment-content acw-comment-content">
						<?php echo wp_html_excerpt( $comment_content, absint( $instance['excerpt_length'] ), '&hellip;' ); ?>
					</div>
				<?php endif; ?>

			</article>

		</<?php echo $tag; ?>>
		<?php
	}


	/**
	 * Outputs a single comment in the XHTML format.
	 *
	 * @uses Advanced_Comments_Widget::get_acw_comment_id()
	 * @uses Advanced_Comments_Widget::get_acw_comment_class()
	 * @uses Advanced_Comments_Widget::get_comment_content()
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @param object $comment WP_Comment Object
	 * @param object $instance Widget instance
	 */
	public function xhtml_comment( $comment, $instance )
	{
		$acw_comment_id = $this->get_acw_comment_id( $comment, $instance );
		$acw_comment_class = $this->get_acw_comment_class( $comment, $instance );
		$comment_content = $this->get_comment_content( $comment, $instance  );
		$tag = ( 'div' === $instance['list_style'] ) ? 'div' : 'li';
		?>

		<<?php echo $tag; ?> id="comment-<?php echo sanitize_html_class( $acw_comment_id ); ?>" class="<?php echo $acw_comment_class; ?>" >

			<div id="div-comment-<?php echo sanitize_html_class( $acw_comment_id ); ?>" class="comment-body acw-comment-body">
				<div class="comment-meta acw-comment-meta">

					<?php if ( $instance['show_thumbs'] ) : ?>
						<span class="comment-avatar acw-comment-avatar">
							<?php echo $this->get_comment_author_avatar( $comment, $instance ); ?>
						</span>
					<?php endif; ?>

					<span class="comment-header acw-comment-header">
						<?php
						printf(
							_x( '%1$s <span class="on">on</span> %2$s', 'widgets' ),
							'<span class="comment-author acw-comment-author">' . get_comment_author_link( $comment ) . '</span>',
							'<span class="comment-link acw-comment-link"><a class="comment-link acw-comment-link" href="' . esc_url( get_comment_link( $comment ) ) . '">' . get_the_title( $comment->comment_post_ID ) . '</a></span>'
						);
						?>
					</span>

				</div>

				<?php if ( $instance['show_excerpt'] ) : ?>
					<div class="comment-content acw-comment-content">
						<?php echo wp_html_excerpt( $comment_content, absint( $instance['excerpt_length'] ), '&hellip;' ); ?>
					</div>
				<?php endif; ?>

			</div>

		</<?php echo $tag; ?>>
		<?php
	}


	/**
	 * Outputs plugin attribution
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return string Plugin attribution.
	 */
	public function colophon()
	{
		return '<!-- Advanced Comments Widget by darrinb http://darrinb.com/plugins/advanced-comments-widget -->';
	}

}