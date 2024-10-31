<?php
// Manages the Settings screen where the user sets their App ID and Api Key.
class OPAdminSettings
{
	// Runs when in BE admin. Adds a filter for when an appid or key is tried. Adds an action to check for proper API creds and returns a notice when not.
	public static function init()
	{
		// Check to see if API creds are correct on cred update
		add_filter( 'pre_update_option_opAppID', array( 'OPAdminSettings', 'checkForValidAPPID'), 10, 2 );
		add_filter( 'pre_update_option_opAPIKey', array( 'OPAdminSettings', 'checkForValidAPIKey'), 10, 2 );

		// If nothing has been setup yet... trigger admin notices with the API Creds check
		$appid = get_option( 'opAppID' );
		if ( $appid === false )
		{
			add_action( 'admin_notices', array( 'OPCoreFunctions', 'checkAPICreds') );
		}
	}

	// Runs on plugin activation and flushes the rewrite rules so that WP picks up our custom 'ontrapage' post type
	public static function ontrapagesActivation()
	{
		flush_rewrite_rules();
	}


	// Runs on plugin activation and flushes the rewrite rules so that WP picks up our custom 'ontrapage' post type
	public static function ontrapagesDeactivation()
	{
		flush_rewrite_rules();
	}


	// Adds the settings menu for the ONTRApages custom post type in the WP backend admin area
	public static function adminSettings()
	{
		add_submenu_page( 'edit.php?post_type=ontrapage', 'ONTRApages Settings', 'Settings', 'manage_options', 'opsettings', array( 'OPAdminSettings', 'opAdminSettingsContent') );

		add_action( 'admin_init',  array( 'OPAdminSettings', 'registerOPSettings' ) );
		add_action( 'admin_notices', array( 'OPAdminSettings', 'adminNotices') );

		add_filter('wp_dropdown_pages', array( 'OPAdminSettings', 'addOPToPagesDropdowns' ), 10, 1);
	}


	// Registers the appid and key settings that get set in the admin area settings
	public static function registerOPSettings()
	{
		register_setting( 'op-admin-settings', 'opAppID' );
		register_setting( 'op-admin-settings', 'opAPIKey' );
	}


	// Adds scripts and css to the WP backend admin area. Nothing to return.
	public static function adminScripts()
	{
		wp_register_style( 'ontrapagesAdminStyles', plugins_url('_inc/css/op-admin-style.css', __FILE__));
	    wp_enqueue_style( 'ontrapagesAdminStyles' );

	    wp_register_script( 'ontrapagesAngular', plugins_url('_inc/js/angular.min.js', __FILE__) );
	    wp_enqueue_script( 'ontrapagesAngular' );

	    wp_register_script( 'ontrapagesApp', plugins_url('_inc/js/op-app.js', __FILE__));
	    wp_enqueue_script( 'ontrapagesApp' );

	    wp_register_script( 'ontrapagesController', plugins_url('_inc/js/op-controller.js', __FILE__));
	    wp_enqueue_script( 'ontrapagesController' );
	}


	// Fixes my (Will) stupidity in naming the app id / api key wrong in v1.1 :/ Nothing to return.
	public static function fixAPISettings()
	{
		$oldkey = get_option('opApiKey');
		$oldsecret = get_option('opAppSecret');
		$newAppId = get_option('opAppID');

		// If a user has manually re-entered their AppID and API key between versions when the plugin broke then prevent the check in the future. If not double check they need the fix and then apply it.
		if ( is_string($newAppId) )
		{
			add_option( 'opAPIFix', true );
		}
		else if ( $oldsecret !== null && $oldsecret != '' && $newAppId == '' )
		{
			delete_option( 'opApiKey' );
			delete_option( 'opAppSecret' );

			add_option( 'opAppID', $oldkey );
			add_option( 'opAPIKey', $oldsecret );
			add_option( 'opAPIFix', true );

			unregister_setting( 'op-admin-settings', 'opApiKey' );
			unregister_setting( 'op-admin-settings', 'opAppSecret' );
		}

		return;
	}


	// Check the Ontraport API to see if the creds are valid or not. If it connects successfully it sets an option in the db telling the app there are valid creds. If not then it checks the API source and tried the ONTRApages API. Returns the appid value that was entered.
	public static function checkForValidAPPID( $value, $old_value, $option=false )
	{
		$appid = $value;
		$key = get_option( 'opAPIKey' );
		$requestUrl = OPAPI . 'objects?objectID=0&range=1';

		$httpcode = OPCoreFunctions::apiRequest( $requestUrl, $appid, $key, true );

		if ( $httpcode != '403' && $httpcode != '401' && $httpcode != '500' )
		{
			update_option( 'opValidCreds', '1' );
		}
		return $value;
	}


	// Check the Ontraport API to see if the creds are valid or not. If it connects successfully it sets an option in the db telling the app there are valid creds. If not then it checks the API source and tried the ONTRApages API. Returns the key value that was entered.
	public static function checkForValidAPIKey( $value, $old_value, $option=false )
	{
		$appid = get_option( 'opAppID' );
		$key = $value;
		$requestUrl = OPAPI . 'objects?objectID=0&range=1';

		$httpcode = OPCoreFunctions::apiRequest( $requestUrl, $appid, $key, true );

		if ( $httpcode != '403' && $httpcode != '401' && $httpcode != '500' )
		{
			update_option( 'opValidCreds', '1' );
		}

		return $value;
	}

	// Check wp db for options that suggest valid creds and valid permalinks. Echos out an error message if not. Nothing to return.
	public static function adminNotices()
	{
		$validCreds = get_option( 'opValidCreds' );

		if ( $validCreds !== false && $validCreds == '0' )
		{
			$html = '<div id="message" class="error is-dismissible" style="display: block; color: #000000!important;">
		    			<p>ONTRApages Warning - It looks like your API credentials are incorrect. Your ONTRApages will not display properly until you fix this. Please <a href="edit.php?post_type=ontrapage&page=opsettings">click here</a> to remedy that & then try again.</p>
					</div>';

			echo $html;
		}

		if ( get_option('permalink_structure') !== '/%postname%/' )
		{
			$html = '<div id="message" class="error">
		    			<p>ONTRApages Warning - It appears that your site is not using the \'Post name\' permalink structure. Unfortunately the ONTRApages plugin may not work properly unless this is enabled. <a href="options-permalink.php">Click here to visit your permalink settings</a> and select \'Post name\' from the options.</a></p>
					</div>';

			echo $html;
		}
	}


	// Adds the settings to the ONTRApages settings page in the WP backend admin area if the user has permission to access them
	public static function opAdminSettingsContent()
	{
		if ( !current_user_can( 'manage_options' ) )
		{
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		self::echoOPSettings();
	}


	// Bleh... really don't want to have to do this like this but WP won't return the hidden inputs we need to process the form submittal. Their functions: settings_fields( 'op-admin-settings' ) & do_settings_sections( 'op-admin-settings' ) echo them out instead. Will add AJAX settings later so that we can do this in a cleaner way.
	public static function echoOPSettings()
	{ ?>
		<div class="op-admin-settings">
			<div class="op-as-title">
				<div class="op-as-title-items">
					<div class="op-as-logo"><img src="<?php echo plugins_url('_inc/images/oplogo.png',__FILE__); ?>" /></div>
				</div>
			</div>
			<div class="op-as-options op-as-small">
				<div class="op-as-options-title op-green-bg">Start</div>
				<div class="op-as-options-wrapper">
					<div class="op-as-options-description">Don't have an Ontraport account yet? No problem. Simply click the button below to get started.</div>
					<a href="https://ontraport.com" target="_blank">
						<button class="op-green-bg">GET AN ACCOUNT</button>
					</a>
				</div>
			</div>
			<div class="op-as-options op-as-small">
				<div class="op-as-options-title op-blue-bg">Connect</div>
				<div class="op-as-options-wrapper">
					<div class="op-as-options-description">Already have an Ontraport account? Great!<br />
						<ol>
							<li>Login to your Ontraport account.</li>
							<li>Click on your email address in the top right to open the admin drawer and click Administration.</li>
							<li>Select Ontraport API INSTRUCTIONS AND KEY MANAGER from the Integrations section.</li>
							<li>Create new API credentials (or use your existing PilotPress API credentials).</li>
							<li>Finally copy & paste them below.</li>
						</ol>
					</div>
					<form method="post" action="options.php">
						<?php settings_fields( 'op-admin-settings' ); ?>
						<?php do_settings_sections( 'op-admin-settings' ); ?>
						<div class="op-as-option">
							<label>App ID</label><br />
							<input name="opAppID" type="text" value="<?php echo esc_attr( get_option('opAppID') ); ?>" />
						</div>
						<div class="op-as-option">
							<label>API Key</label><br />
							<input name="opAPIKey" type="text" value="<?php echo esc_attr( get_option('opAPIKey') ) ?>" />
						</div>
						<div class="op-as-option op-as-submit">
							<input type="submit" name="submit" id="submit" value="SAVE">
						</div>
					</form>
				</div>
			</div>

			<div class="op-as-options op-as-small">
				<div class="op-as-options-title op-red-bg">Learn</div>
				<div class="op-as-options-wrapper">
					<div class="op-as-options-description" style="margin-top:10px;">Once you've created a Landing Page in your Ontraport account, publish it to Wordpress then return here and follow these simple steps. <a href="https://ontraport.com/support/landing-pages/display-your-ontraport-page-on-your-wordpress-site/" target="_blank">Learn more...</a>
						<ol>
							<li>Click <a href="post-new.php?post_type=ontrapage">Add New</a> in the ONTRApages menu item to the left.</li>
							<li>Enter a title for the page.</li>
							<li>Choose which Landing Page you would like to use</li>
							<li>Update your URL and Publish your page!</li>
						</ol>
					</div>
					<div class="op-as-options-important">
						IMPORTANT - This plugin may not work properly unless you have your Permalinks structure set to the 'Post name' option. Permalinks allow you to format the structure of the URL's of your site. <a href="options-permalink.php">Click here to visit your permalink settings</a> and select 'Post name' from the options.</a>
					</div>
				</div>
			</div>
		</div>
	<?php }


	// Add ONTRApages to the front page select option in Settings > Reading so that users can add an ONTRApage as their home page
	public static function addOPToPagesDropdowns( $select )
	{
		if ( strpos( $select, 'name="page_on_front"' ) === false &&
			strpos( $select, "name='page_on_front'" ) === false &&
			strpos( $select, "name='_customize-dropdown-pages-page_on_front'" ) === false &&
			strpos( $select, 'name="_customize-dropdown-pages-page_on_front"' ) === false )
		{
			return $select;
		}

		$ontrapages = get_posts( array( 'post_type' => 'ontrapage', 'numberposts' => -1 ) ); // -1 is a flag to get all, otherwise function defaults to 5

		if ( !$ontrapages )
		{
			return $select;
		}

		$frontPage = get_option("page_on_front",0);

		$opOptions = walk_page_dropdown_tree( $ontrapages, 0, array(
			'depth' => 0,
			'child_of' => 0,
			'selected' => $frontPage,
			'echo' => 0,
			'name' => 'page_on_front',
			'id' => '',
			'show_option_none' => '',
			'show_option_no_change' => '',
			'option_none_value' => ''
			)
		);

		return str_replace( '</select>', $opOptions . '</select>', $select );
	}

	//Hook so we can process things after the plugins are all loaded
	public static function pluginsLoaded()
	{
		//Remove the login page option in PilotPress drop down menu
		add_filter("pilotpress_get_routeable_pages", array("OPAdminSettings" , "filterPilotPressRouteablePages") ,10,1);
	}

	//Helper function to remove the (login page) option from the on error redirect that is generated from PilotPress
	public static function filterPilotPressRouteablePages($array)
	{
		global $post;

		if ($post->post_type == "ontrapage")
		{
			//remove (login page) from PilotPress drop down
			unset($array["-2"]);
		}
		return $array;
	}
}
