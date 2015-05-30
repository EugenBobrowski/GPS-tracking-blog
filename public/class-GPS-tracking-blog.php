<?php
/**
 * Plugin Name.
 *
 * @package   GPSTrackingBlog
 * @author    Your Name <email@example.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2014 Your Name or Company Name
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-GPS-tracking-blog-admin.php`
 *
 * @TODO: Rename this class to a proper name for your plugin.
 *
 * @package GPSTrackingBlog
 * @author  Your Name <email@example.com>
 */
class GPSTrackingBlog {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 * @TODO - Rename "plugin-name" to the name of your plugin
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'GPS-tracking-blog';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Define custom functionality.
		 * Refer To http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( '@TODO', array( $this, 'action_method_name' ) );
		add_filter( 'the_content', array( $this, 'add_tracking_map_to_content' ) );

		add_filter( 'init', array( $this, 'register_post_type' ) );
        add_shortcode('addgpstrack', array( $this, 'add_gps_track' ));

        add_action( 'wp_ajax_gps_blog_ajax', array( $this, 'gps_public_ajax_controller' ));
        add_action( 'wp_ajax_nopriv_gps_blog_ajax', array( $this, 'gps_public_ajax_controller' ));

	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
		// @TODO: Define activation functionality here
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        wp_enqueue_script(
            'google-maps',
            'http://maps.google.com/maps/api/js?sensor=false',
            array(),
            '5'
        );

        wp_enqueue_script(
            'gmap3',
            plugins_url( 'assets/js/gmap3.min.js', __FILE__ ),
            array('jquery', 'google-maps'),
            self::VERSION,
            true
        );
        wp_enqueue_script(
            'jquery-canvas-js',
            plugins_url( 'assets/js/jquery.canvasjs.min.js', __FILE__ ),
            array('jquery'),
            self::VERSION,
            true
        );
        wp_enqueue_script(
            'gmap-public',
            plugins_url( 'assets/js/public.js', __FILE__ ),
            array('jquery', 'google-maps', 'gmap3', 'jquery-canvas-js'),
            self::VERSION,
            true
        );
        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'gmap-public', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234 ) );
		//wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
	}

	/**
	 * NOTE:  Actions are points in the execution of a page or process
	 *        lifecycle that WordPress fires.
	 *
	 *        Actions:    http://codex.wordpress.org/Plugin_API#Actions
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Action_Reference
	 *
	 * @since    1.0.0
	 */
	public function action_method_name() {
		// @TODO: Define your action hook callback here
	}

	/**
	 * NOTE:  Filters are points of execution in which WordPress modifies data
	 *        before saving it or sending it to the browser.
	 *
	 *        Filters: http://codex.wordpress.org/Plugin_API#Filters
	 *        Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
	 *
	 * @since    1.0.0
	 */
	public function add_tracking_map_to_content($content) {

        global $post;

        $track = get_post_meta($post->ID, 'track_data', true);

        if (!empty($track) && strpos($content, '[notracks]') === false) {
            $result = '
            <div id="postMap"
            class="gmap3" style="width: 100%; height: 300px" data-track=\''.$track.'\'></div>';
            return $result.$content;
        } else {
            return str_replace(array('[notracks]'), '', $content);
        }

	}

    /**
     *
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __( 'GPS Trips' , 'atf' ),
            'singular_name'      => __( 'Trip' , 'atf' ),
            'add_new'            => __( 'Add New' , 'atf' ),
            'add_new_item'       => __( 'Add New Trip item' , 'atf' ),
            'edit_item'          => __( 'Edit Trip item' , 'atf' ),
            'new_item'           => __( 'New Trip item' , 'atf' ),
            'all_items'          => __( 'All Trip items' , 'atf' ),
            'view_item'          => __( 'View Trip item' , 'atf' ),
            'search_items'       => __( 'Search Trips item' , 'atf' ),
            'not_found'          => __( 'No products found' , 'atf' ),
            'not_found_in_trash' => __( 'No products found in the Trash' , 'atf' ),
            'parent_item_colon'  => '',
            'menu_name'          => 'Trips'
        );
        $args = array(
            'labels'        => $labels,
            'description'   => 'Holds our products and product specific data',
            'public'        => true,
            'supports'      => array( 'title', 'editor', 'thumbnail', 'tags', 'sticky', 'excerpt', 'comments', 'author' ),
            'has_archive'   => true,
            //'menu_icon'     => plugin_dir_url(__FILE__) . 'Trip-20px.png',
            'taxonomies'    => array('post_tag'),
            'publicly_queryable' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'trip'),
        );
        register_post_type( 'track' , $args );
    }
    public function add_gps_track($atts) {


        $result  = '<form id="submitTrackForm" method="POST" action="javascript:void(null);">';
        $result .= '<input type="hidden" id="gpsChickenhut" name="gpstrack[gpsChickenhut]" value="'.wp_create_nonce( 'add-form-xxx' ).'">';
        $result .= '<div class="form-group">
            <label for="gpsTrackTitle">Track title</label>
            <input type="text" name="gpstrack[title]" class="form-control" id="gpsTrackTitle" placeholder="Track title">
          </div>
          <div class="form-group">
            <label for="gpsTrackDescription">Description</label>
            <textarea class="form-control" rows="3" id="gpsTrackDescription" placeholder="Type here your story"></textarea>
          </div>
          <div class="form-group file-to-map">
          <label for="gpsTrackFile">
            <span class="glyphicon glyphicon-floppy-open"></span> Upload track file <span id="labelFileName"></span>

            <input type="file" id="gpsTrackFile">
          </label>
            <input type="hidden" id="gpsTrackContent" name="gpstrack[trackdata]">
            <div id="formMap" class="gmap3" style="width: 100%; height: 300px"></div>
          </div>
          <div class="form-group">
              <div class="btn-group" data-toggle="buttons">
                  <label class="btn btn-primary active">
                    <input type="checkbox" autocomplete="off" checked> <i class="fa fa-subway"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-wheelchair"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-car"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-bicycle"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-ship"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-bus"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-truck"></i>
                  </label>
                  <label class="btn btn-primary">
                    <input type="checkbox" autocomplete="off"> <i class="fa fa-motorcycle"></i>
                  </label>
              </div>
          </div>


          <button type="submit" class="btn btn-primary btn-lg" id="submitTrack" data-loading-text="Sending...">Submit</button>
        </form>';


        return $result;
    }
    public function gps_public_ajax_controller () {
        check_ajax_referer( 'add-form-xxx', 'chickenhut' );

        if ($_POST['subaction'] == 'updateMap') {

            $filexp = explode('.',$_POST['fileName']);
            $ext = array_pop($filexp);
            if ($ext  == 'txt') {
                require_once 'class-track-render.php';
                $track = new TrackRender;
                $track->srting = $_POST['track'];
                echo $track->txt();
            } elseif  ($ext  == 'gpx') {
                require_once 'class-track-render.php';
                $track = new TrackRender;
                $track->srting = $_POST['track'];
                echo $track->gpx();
            }
        }
        elseif ($_POST['subaction'] == 'submit') {



            $track_id = wp_insert_post( array(
                'post_content'   => $_POST['description'], // The full text of the post.
                'post_title'     => $_POST['title'], // The title of your post.
                'post_status'    => 'publish',
                'post_type'     => 'track'
            ) );
            if( is_wp_error( $track_id ) ) {
                echo $track_id->get_error_message();
            } else {
                add_post_meta($track_id, 'track_data', $_POST['track']);

                add_post_meta($track_id, 'track_data_time_full', $_POST['track_data_simple']['time_full']);
                add_post_meta($track_id, 'track_data_time_start', $_POST['track_data_simple']['time_start']);
                add_post_meta($track_id, 'track_data_time_stop', $_POST['track_data_simple']['time_stop']);
                add_post_meta($track_id, 'track_data_distance', $_POST['track_data_simple']['distance']);
                echo get_permalink($track_id);

            }
        }
        elseif ($_POST['subaction'] == 'delete') {
            if (
            !empty($_POST['postid'])
            && current_user_can('delete_post', $_POST['postid'])
            ) {
                wp_delete_post($_POST['postid']);

                $user = wp_get_current_user();
                // The Query
                $tracks = new WP_Query( array(
                    'post_type' => 'track',
                    'author' => $user->ID,
                    'offset' => 9,
                    'posts_per_page' => 1,
                ) );
                if ( $tracks->have_posts() ) {

                    while ( $tracks->have_posts() ) {
                        $tracks->the_post();
                        echo '<tr>';
                        echo '<td>' . get_the_ID()

                            . '</td>';
                        echo '<td class="track-name"><a href="'. get_permalink() .'">' . get_the_title() . '</a>'
                            . '</td>';
                        echo '<td>'
                            . ' <a href="#"><i class="fa fa-pencil"></i></a> '
                            . '</td>';

                        //Distance
                        $distance = get_post_meta($tracks->post->ID, 'track_data_distance', true);
                        echo (!empty($distance)) ? '<td>' . round($distance/1000, 3) . ' km' . '</td>' : '<td></td>';

                        //Duration
                        $seconds = get_post_meta($tracks->post->ID, 'track_data_time_full', true);
                        $days = floor($seconds / (3600*24));
                        $hours = floor(($seconds  - ($days*3600*24)) / 3600);
                        $mins = floor(($seconds - ($hours*3600)) / 60);
                        $secs = floor($seconds % 60);
                        echo (!empty($seconds)) ? '<td>' .((!empty($days)) ? $days.'d ' : ''). $hours.':'.$mins.':'.$secs . '</td>' : '<td></td>';

                        //Speed
                        $start = get_post_meta($tracks->post->ID, 'track_data_time_start', true);
                        echo (!empty($seconds) && !empty($distance)) ? '<td>' . round(($distance * 3600) / ($seconds * 1000), 2) . ' km/h' . '</td>' : '<td></td>';

                        //Date
                        $start = get_post_meta($tracks->post->ID, 'track_data_time_start', true);
                        echo (!empty($start)) ? '<td>' . date('D d M Y', $start) . '</td>' : '<td></td>';

                        echo '<td>'
                            . ' <a href="#" class="delete-track" data-postid="' . get_the_ID() . '"><i class="fa fa-trash-o"></i></a> '
                            . '</td>';

                        echo '</tr>';
                    }
                } else {
                    // no posts found
                }
                /* Restore original Post Data */
                wp_reset_postdata();

            }

        }
        elseif ($_POST['subaction'] == 'update') {
            if ( !empty($_POST['postid']) && current_user_can('edit_post', $_POST['postid']) ) {
                wp_update_post( array(
                    'ID'             => $_POST['postid'],
                    'post_content'   => $_POST['description'], // The full text of the post.
                    'post_title'     => $_POST['title'], // The title of your post.
                ) );
                echo 'post updated';
            } else {
                echo 'permision denide';
            }
        }





        wp_die(); // this is required to terminate immediately and return a proper response
    }


}
