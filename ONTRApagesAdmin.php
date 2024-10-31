<?php
// Manages all functionality of the BE admin functions with regards to setting the options for each ONTRApage.
class ONTRApagesAdmin
{
	
	// Initialize ONTRApages WP BE Admin settings
	public static function init()
	{
		if ( is_admin() )
		{
			self::initHooks();
		}
	}


	// Initializes WP BE Admin hooks
	private static function initHooks()
	{
		add_action( 'save_post', array( 'ONTRApagesAdmin', 'updateOPMetaboxData' ), 1, 2 );
	}
	

	// Adds the Metabox with our settings to the ONTRApage custom post type
	public static function addOPMetabox()
	{
		add_meta_box('opMetabox', 'ONTRApages', array ( 'ONTRApagesAdmin', 'getOPMetabox'), 'ontrapage', 'normal', 'high');
	}


	// Generates the content for the ONTRApages Object dropdown select option. No return. Just echo's it out directly since that's how WP requires it.
	public static function getOPMetabox()
	{
		global $post;
		
		$html = '<div ng-app="ontraPages" class="op-metabox-opselect">
					<div ng-controller="OPMetaBoxController" class="opm-opselect-items">';

		$html .=  ONTRApagesAdmin::getOPTemplates();

		$html .= '	</div>
				</div>';

		echo $html;
	}


	// Gets all available ONTRApage template objects and then adds the little stat boxes below the dropdown. Returns the data to be used in getOPMetabox.
	protected static function getOPTemplates()
	{
		// Check API Creds first then proceed if ok
		$errorCode = OPCoreFunctions::checkAPICreds( 'code' );

		// Get ONTRApage Objects
		$ONTRApages = OPObjects::getOPObjects( 'pages' );

		if ( $ONTRApages !== 'auth-error' && $errorCode === 0 && is_array( $ONTRApages ) )
		{
			$post_id = get_the_ID();
			$opID = get_post_meta( $post_id, 'ontrapage', true);

			if ( $opID === '' || $opID === null )
			{
				$opID = -1;
			}

			$html = '<input type="hidden" name="ontrapagenonce" value="' . wp_create_nonce('ontrapagenonce') . '" />
					<select name="ontrapage" ng-model="selectedPage" title="Choose which Landing Page you would like to use" ng-change="pageChanged()" ng-options="page.id as page.name for page in pages track by page.id">
						<option value="" selected>Choose which Landing Page you would like to use</option>
					</select>
					<div class="op-ontrapage-details op-hidden" ng-show="sPage != null && sPage.visits_0 != 0">
						<div class="op-odetail" title="Split tests allow you to create multiple versions of a webpage with small differences that you can test against each other to see which performs the best! Learn more at ontrapages.com." ng-show="sPage.visits_0 != 0 || sPage.a_convert != 0">
							<div class="op-odetails-lp-stat-title" ng-show="sPage.visits_1 == 0 && sPage.visits_2 == 0 && sPage.visits_3 == 0">Page Stats</div>
							<div class="op-odetails-lp-stat-title" ng-show="sPage.visits_1 != 0 || sPage.visits_2 != 0 || sPage.visits_3 != 0">Split Test A</div>
							<div class="op-odetails-lp-views">{{sPage.visits_0}} Views</div>
							<div class="op-odetails-lp-conversion">{{sPage.a_convert}}% Convert</div>
						</div>
						<div class="op-odetail" title="Split tests allow you to create multiple versions of a webpage with small differences that you can test against each other to see which performs the best! Learn more at ontrapages.com." ng-show="sPage.visits_1 != 0 || sPage.b_convert != 0">
							<div class="op-odetails-lp-stat-title">Split Test B</div>
							<div class="op-odetails-lp-views">{{sPage.visits_1}} Views</div>
							<div class="op-odetails-lp-conversion">{{sPage.b_convert}}% Convert</div>
						</div>
						<div class="op-odetail" title="Split tests allow you to create multiple versions of a webpage with small differences that you can test against each other to see which performs the best! Learn more at ontrapages.com." ng-show="sPage.visits_2 != 0 || sPage.c_convert != 0">
							<div class="op-odetails-lp-stat-title">Split Test C</div>
							<div class="op-odetails-lp-views">{{sPage.visits_2}} Views</div>
							<div class="op-odetails-lp-conversion">{{sPage.c_convert}}% Convert</div>
						</div>
						<div class="op-odetail" title="Split tests allow you to create multiple versions of a webpage with small differences that you can test against each other to see which performs the best! Learn more at ontrapages.com." ng-show="sPage.visits_3 != 0 || sPage.d_convert != 0">
							<div class="op-odetails-lp-stat-title">Split Test D</div>
							<div class="op-odetails-lp-views">{{sPage.visits_3}} Views</div>
							<div class="op-odetails-lp-conversion">{{sPage.d_convert}}% Convert</div>
						</div>
					</div>
					<script type="text/javascript">
						window.ontrapages = {};
						window.ontrapages.pageId = ' . $opID . ';
						window.ontrapages.pages = ' . json_encode($ONTRApages) . ';
					</script>';

			return $html;

		}
		else if ( $ONTRApages == 'timeout-error')
		{
			echo "There was an error retrieving the pages. Please refresh this page";
		}
		else if ( $ONTRApages === 'auth-error' && $errorCode === 0 )
		{
			OPCoreFunctions::checkAPICreds();
		}

		return;
	}


	// Saves or updates the selected ONTRApage object ID and associates it with a given page.
	public static function updateOPMetaboxData($post_id, $post)
	{
		// verify this came from the our screen and with proper authorization,
		// because save_post can be triggered at other times
		if ( isset( $_POST['ontrapagenonce'] ) )
		{
			if ( !wp_verify_nonce( $_POST['ontrapagenonce'], 'ontrapagenonce' ) ) 
			{
				return $post->ID;
			}

			$key = 'ontrapage';
			$value = sanitize_key($_POST["ontrapage"]);

			if (!is_numeric($value))
			{
				return;
			}

			// Is the user allowed to edit the post or page?
			if ( !current_user_can( 'edit_post', $post->ID ) )
			{
				return $post->ID;
			}		
			
			// Don't store custom data twice
			if( $post->post_type == 'revision' )
			{
				return;
			}

			if(get_post_meta($post->ID, $key, FALSE)) 
			{ 
				update_post_meta($post->ID, $key, $value);
			} 
			else 
			{ 
				add_post_meta($post->ID, $key, $value);
			}

			if (!$value)
			{
				delete_post_meta($post->ID, $key);
			}
		}
	}

}