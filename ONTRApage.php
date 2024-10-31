<?php
// Manages the FE aspect of displaying ONTRApages when visiting a WP url.
class ONTRApage
{
    //a year under max.. in case someone's on an old version of WP/PHP
    const MAX_TIMESTAMP = 2115926722;

    const ONE_DAY = 86400;

    // Initialize ONTRApages WP FE settings
    public static function init()
    {
        self::initHooks();
    }


    // Initializes WP FE hooks
    private static function initHooks()
    {
        add_action( 'template_redirect', array ( 'ONTRApage', 'addOPContainerTemplate'), 10 );

        // Does some trickery to remove the 'o' slug that is required by WP for a custom post type. I do NOT want to be doing it like this, but WP doesn't provide a method by which to do this any other way. This is however a widely accepted workaround.
        add_action( 'wp_loaded', array( 'ONTRApage', 'addONTRApagesPostType' ) );
        add_filter( 'post_type_link', array ( 'ONTRApage', 'removeSlug'), 10, 3 );
        add_action( 'pre_get_posts', array ( 'ONTRApage', 'interceptRequestTrickery') );
    }

    // Adds the ontrapage custom post type and sets up all menu items for it etc.
    public static function addONTRApagesPostType()
    {
        $menuIcon = plugins_url('_inc/images/opgicon.png', __FILE__);

        $labels = array(
            'name' => 'ONTRApages',
            'singular_name' => 'ONTRApage',
            'add_new' => 'Add New',
            'add_new_item' => 'Add a new ONTRApage',
            'edit_item' => 'Edit your ONTRApage',
            'new_item' => 'New ONTRApage',
            'all_items' => 'All ONTRApages',
            'view_item' => 'View this ONTRApage',
            'search_items' => 'Search ONTRApages',
            'not_found' =>  'No ONTRApages found',
            'not_found_in_trash' => 'No ONTRApages found in the Trash', 
            'parent_item_colon' => '',
            'menu_name' => 'ONTRApages'
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'menu_icon' => $menuIcon,
            'publicly_queryable' => true,
            'show_ui' => true, 
            'show_in_menu' => true, 
            'query_var' => true,
            'rewrite' => array( 'slug' => 'o' ),
            'capability_type' => 'page',
            'hierarchical' => false,
            'menu_position' => 20,
            'supports' => array(
                'title'
                )
        ); 

        // Register the new ontrapage post type
        register_post_type( 'ontrapage', $args );

        // Adds the custom meta box to each ONTRApage
        add_action( 'add_meta_boxes', array ( 'ONTRApagesAdmin', 'addOPMetabox') );
    }

    // Sets up the FE Container Template if it detects an ontrapage post type and tells WP to use our custom template on that particular page. Hands the template path over to opThemeRedirect.
    public static function addOPContainerTemplate( $template )
    {
        global $post;
        global $wp_query;
        $plugindir = dirname( __FILE__ );
            
        $pageId = $wp_query->query_vars['page_id'];
        $postType = get_post_type( $pageId );

        if ( (is_object($post) && $post->post_type == 'ontrapage') || ( $post === null && $postType === 'ontrapage' ) )
        {
            $templatefilename = 'single-ontrapage.php';
            if ( file_exists(TEMPLATEPATH . '/' . $templatefilename) ) 
            {
                $return_template = TEMPLATEPATH . '/' . $templatefilename;
            } 
            else 
            {
                $return_template = $plugindir . '/' . $templatefilename;
            }

            self::opThemeRedirect($return_template);
        }
    }


    // Redirects WP to use our own FE template when it detects an ontrapage post type.
    public static function opThemeRedirect($url)
    {
        global $post, $wp_query;

        if (is_search())
        {
            return;
        }
        $pageId = $wp_query->query_vars['page_id'];
        $postType = get_post_type( $pageId );

        if ( have_posts() || ( $post === null && $postType === 'ontrapage' ) ) 
        {
            include($url);
            die();
        }
        else
        {
            $wp_query->is_404 = true;
        }
        
    }


    // Finds the id of the ONTRApage object desired to be displayed on the given page and calls home to get the ONTRApage's URL. Then it provides the URL to OPCoreFunctions::getURLContent() which uses cURL to get the HTML of that particular URL. Returns the HTML for the ONTRApage.
    protected static function getONTRApageHTML($opID)
    {
        $appid = get_option('opAppID');
        $key = get_option('opAPIKey');

        $request = OPAPI . "landingPage/getHostedURL?id=$opID&get_split=1";

        if (isset($_COOKIE["contact_id"]) && is_numeric($_COOKIE["contact_id"]))
        {
            $request .= "&object_id=" . sanitize_key($_COOKIE["contact_id"]);
        }

        $response = OPCoreFunctions::apiRequest($request, $appid, $key);

        if (isset($response) && $response === 'Your App ID and API Key do not authenticate.')
        {
            return 'auth-error';
        }
        else
        {
            $lpObject = json_decode($response);
            $url = $lpObject->data;
            
            if (!$url)
            {
                $url = get_transient("ontrapages_url_" . $opID);
            }
            else
            {
                set_transient("ontrapages_url_" . $opID, $url, self::ONE_DAY);
            }
            
            //parse out lpsplt_id and the split_num (indices 0 and 1)
            $lpsplt = explode('=', parse_url($url, PHP_URL_QUERY));
            $split_num = $lpsplt[1];
            $lpsplt = $lpsplt[0];

            //want to save clean URL, no appended split testing info
            $trim_length = strlen($url) - strlen(parse_url($url, PHP_URL_QUERY)) - 1;
            $url = substr($url, 0, $trim_length);

            $query_string = "?" . $lpsplt . "=";
            if (isset($_COOKIE[$lpsplt]) && is_numeric($_COOKIE[$lpsplt]))
            {
                //need to insert cookied split test
                $query_string .= sanitize_key($_COOKIE[$lpsplt]);
            }
            else if ($lpsplt)
            {
                //first visit
                setcookie($lpsplt, $split_num, self::MAX_TIMESTAMP);
                $query_string .= $split_num . "&fv=1";
            }

            //need to pass query string back to getURLContent so get vars can be merged in
            $extra_get_vars = "";
            if ($_GET)
            {
                $extra_get_vars .= "&" . $_SERVER['QUERY_STRING'];
            }

            $url .= $query_string;

            // For v1 templates fix the relative URL
            $html = OPCoreFunctions::getURLContent($url, $extra_get_vars);
            $html = str_replace('"/opt_assets/', 'http://optassets.ontraport.com/opt_assets/', $html);

            return do_shortcode($html);
        }
    }


    // Manages the removal of the 'o' slug that is required when settings up the custom post type. Returns a new post link.
    public static function removeSlug( $url, $post ) 
    {
        if ( 'ontrapage' != get_post_type( $post ) ) 
        {
            return $url;
        }

        $post_link = str_replace( '/o/', '/', $url );

        return $post_link;
    }


    /*
    * @brief Intercepts the request and resets the custom post type
    *
    * @param Object $query Wp Query Array
    */
    public static function interceptRequestTrickery($query)
    {
        if (!$query->is_main_query())
        {
            return;
        }

        //This allows for an ONTRAPAGE used on the Front page to not forward to the permalinked version
        if (empty($query->query_vars['post_type']) && 0 != $query->query_vars['page_id'])
        {
            $query->set('post_type', array('post', 'ontrapage', 'page'));
        }

        if (2 != count($query->query) || !isset($query->query['page']))
        {
            return;
        }

        if (!empty($query->query['name']))
        {
            $query->set('post_type', array('post', 'ontrapage', 'page'));
        }
    }
}