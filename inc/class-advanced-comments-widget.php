<?php

/**
 * Advanced_Comments_Widget Class
 *
 * Adds a Recent Comments widget with extended functionality
 *
 * @package Advanced_Comments_Widget
 *
 * @since 1.0
 *
 */

// No direct access
if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


class Advanced_Comments_Widget
{

	/**
	 * Full file path to plugin file
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $file = '';


	/**
	 * URL to plugin
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $url = '';


	/**
	 * Filesystem directory path to plugin
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $path = '';


	/**
	 * Base name for plugin
	 *
	 * e.g. "advanced-term-fields/advanced-term-fields.php"
	 *
	 * @since 0.1.0
	 *
	 * @var string
	 */
	protected $basename = '';
	
	
	public function __construct( $file ){
		$this->file	    = $file;
		$this->url	    = plugin_dir_url( $this->file );
		$this->path	    = plugin_dir_path( $this->file );
		$this->basename = plugin_basename( $this->file );			
	}
	
	public function init()
	{
		$this->load_widget();
		$this->load_admin_scripts();
	}
	
	
	public function load_widget()
	{
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
	}
	
	
	public function register_widget()
	{
		register_widget( 'Widget_Advanced_Comments' );
	}
	
	
	/**
	 * Loads js/css admin scripts
	 *
	 * @access public
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function load_admin_scripts()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'admin_head', array( $this, 'enqueue_admin_styles' ) );
	}
	
	
	/**
	 * Loads js admin scripts
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts( $hook ){
	
		if( 'widgets.php' !== $hook ) {
			return;
		}
		
		wp_enqueue_script( 'acw-scripts', $this->url . 'js/admin.js', array( 'jquery' ), '', true );
	
	}


	/**
	 * Prints out css styles in admin head
	 *
	 * Note: Only loads on edit-tags.php
	 *
	 * @access public
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function enqueue_admin_styles(){
		?>
		<style type="text/css">
			.acw-avatar { display: block; border: 1px solid #ddd; margin: 5px 0; text-align: center; }
			.acw-avatar .dashicons {  font-size: inherit; height: 100%; width: 100%; }
		</style>
		<?php
	}

}