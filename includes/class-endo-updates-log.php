<?php 

/**
* Load the base class
*/
class Endo_Updates_Log {
	
	function __construct()	{
		
	}

	/**
	 * Kick it off
	 * 
	 */
	public function run() {

		self::setup_constants();

		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ) );

		add_action( 'upgrader_process_complete', array( $this, 'insert_log' ), 10, 2);

		add_filter('manage_edit-endo_updates_log_columns' , array( $this, 'add_admin_columns') );
		add_action('manage_endo_updates_log_posts_custom_column' , array( $this, 'display_admin_columns_data' ), 10, 2 );

	}

	public function load_scripts() {

		// wp_enqueue_script( 'bpopup', ENDO_UPDATES_PLUGIN_URL . 'js/bpopup.js' );

	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'ENDO_UPDATES_LOG_VERSION' ) ) {
			define( 'ENDO_UPDATES_LOG_VERSION', '1.0.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'ENDO_UPDATES_LOG_PLUGIN_DIR' ) ) {
			define( 'ENDO_UPDATES_LOG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'ENDO_UPDATES_LOG_PLUGIN_URL' ) ) {
			define( 'ENDO_UPDATES_LOG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File.
		if ( ! defined( 'ENDO_UPDATES_LOG_PLUGIN_FILE' ) ) {
			define( 'ENDO_UPDATES_LOG_PLUGIN_FILE', __FILE__ );
		}

	}

	/**
	 * Include required files.
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {
		global $this_plugins_options;

		require_once ENDO_UPDATES_LOG_PLUGIN_DIR . 'includes/admin/settings/register-settings.php';
		$this_plugins_options = this_plugin_get_settings();

	}

	/**
	 * Register a book post type.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 */
	public function register_post_types() {
		$labels = array(
			'name'               => 'Update Logs',
			'singular_name'      => 'Update Log',
			'menu_name'          => 'Update Logs',
			'name_admin_bar'     => 'Entry',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Entry',
			'new_item'           => 'New Entry',
			'edit_item'          => 'Edit Entry',
			'view_item'          => 'View Entry',
			'all_items'          => 'All Update Logs',
			'search_items'       => 'Search Entries',
			'parent_item_colon'  => 'Parent Update Logs:',
			'not_found'          => 'No entries found',
			'not_found_in_trash' => 'No Update Logs found in trash.'
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Description.', 'your-plugin-textdomain' ),
			'public'             => true,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_icon'			=> 'dashicons-book',
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);

		register_post_type( 'endo_updates_log', $args );
	}



	public function insert_log( $upgrader_obj, $data ) 
	{

		$log = array(
		  'post_title'    => time(),
		  'post_content'  => '',
		  'post_status'   => 'publish',
		  'post_author'   => get_current_user_id(),
		  'post_type'	=> 'endo_updates_log'
		);
		 
		// Insert the post into the database
		$log_id = wp_insert_post( $log );

		update_post_meta( $log_id, '_log_data', json_encode( $data ) );
		update_post_meta( $log_id, '_log_version', $upgrader_obj->skin->plugin_info['Version'] );

	}

	public function create_meta_box() {

		add_meta_box( 'endo_updates_log_details', 'Details', array( $this, 'display_log_details' ), 'endo_updates_log', 'normal', 'high' );

	}

	public function display_log_details( $post ) 
	{
		
		
		$data = json_decode( get_post_meta( $post->ID, '_log_data', true ) );

		?>
		<table class="form-table">
			<tbody>

				
				<tr valign="top">
					
					<td>

						<p><strong>Action:</strong> <?php echo $data->action; ?></p>
						<p><strong>Type:</strong> <?php echo $data->type; ?></p>
						<p><strong>Updated:</strong></p>
						
							<?php if ( $data->plugins ) { ?>
							<ul>
								<?php 
									foreach( $data->plugins as $plugin ) {
										$string = explode( '/', $plugin );
										echo '<li>' . ucwords( str_replace( '-', ' ', $string[0] ) ) . '</li>';
									}
								?>
							</ul>
							<?php } else {
								echo '<p>WordPress Core</p>';
							} ?>
			
					</td>
				</tr>
			</tbody>
		</table>

		<?php 
	}

	public function add_admin_columns( $columns ) 
	{
		$columns = array(
		    'cb' => '<input type="checkbox" />',
		    'date' => __( 'Date' ),
		    'item' => __( 'Item' ),
		    'version' => __( 'Version' ),
		    'type' => __( 'Type' ),
		    'author' => __( 'Author' )
		);

		return $columns;

	}

	public function display_admin_columns_data( $column, $post_id ) 
	{

		$data = json_decode( get_post_meta( $post_id, '_log_data', true ) );
		
        switch ( $column ) {
        
           case 'type' :
               echo $data->type;
               break;

           case 'item':
               if ( $data->plugins ) {
               		foreach( $data->plugins as $plugin ) {
               			$string = explode( '/', $plugin );
               			echo ucwords( str_replace( '-', ' ', $string[0] ) );
               		}
               } else {
               	echo 'WordPress Core';
               }
               break;
           case 'version' :
           	echo get_post_meta( $post_id, '_log_version', true ) ? get_post_meta( $post_id, '_log_version', true ) : 'not provided';
        }
	}

}