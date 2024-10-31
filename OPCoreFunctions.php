<?php
// Manages the API requests and any other core functionality that gets used accross multiple classes.
class OPCoreFunctions
{
	// Performs an API request to api.ontrapages.com or api.ontraport.com/1/. Returns the API response.
	public static function apiRequest( $requestUrl, $appid, $key, $httpCodeOnly=false )
	{
		$response = self::getResponse( $requestUrl, array("Api-Appid" => $appid, "Api-Key" => $key) );
		if ( $httpCodeOnly !== false )
		{
			return wp_remote_retrieve_response_code( $response );
		}
		return wp_remote_retrieve_body( $response );
	}


	// Get's the contents of the URL. Returns the HTML.
	public static function getURLContent( $url , $extra_get_vars = "")
	{
		$response = self::getResponse( $url . $extra_get_vars, array("X-Ontrapages-Plugin: $url"));
		$content = wp_remote_retrieve_body( $response );
		if (!$content)
		{
			// WP errored out trying to receive the HTML, lets grab a copy from the last known successful time
			$content = get_transient("ontrapages_".$url);

			if (!$content)
			{
				// no cached content either. Try one more time
				$response = self::getResponse( $url . $extra_get_vars, array("X-Ontrapages-Plugin: no_cache_attempt_2") );
				$content = wp_remote_retrieve_body( $response );
			}
		}

		if ($content && !$extra_get_vars)
		{
			// got some content lets cache it for a day so we can use in the case something goes wrong fetching it from the server
			set_transient("ontrapages_".$url, $content, 86400);
		}

		return $content;
	}

	public static function getResponse( $url , $headers = array("X-Ontrapages-Plugin: 1") )
	{
		return wp_remote_get( $url, array(
				'blocking' => true,
				'headers' => $headers,
				'sslverify' => false,
				'timeout' => 60
			)
		);
	}


	/**
	 * Check db options to see if there are valid api creds. 
	 * 
	 * @param $returnType You can pass 'echo' or 'code' as the return type. If echo then it will echo the response message. If code it will just return the response code. If nothing then it will return the entire response.
	 * @param $emptyObject If there is an empty object check it to see if it's an auth error. If so let the user know.
	 * 
	 * @return Echos a warning message if not and returns an error code.
	 * 
	 * Error Code 0 - All good in the hood
	 * Error Code 1 - API Creds are wrong
	 * Error Code 2 - Missing API Creds
	 * Error Code 3 - No ONTRApages available
	 */
	public static function checkAPICreds( $returnType=false, $emptyObject=false )
	{
		$appid = get_option( 'opAppID' );
		$validCreds = get_option( 'opValidCreds' );
		
		if ( $appid === false )
		{
			$code = 2;
		}
		else if ( $validCreds !== false && $validCreds == '0' )
		{
			$code = 1;
		}
		else if ( $validCreds !== false && $validCreds == '1' && $emptyObject !== false )
		{
			if ( $emptyObject == 'auth-error' )
			{
				$code = 1;
			}
			else
			{
				$code = 3;
			}
		}
		else
		{
			$code = 0;
		}

		$response = self::buildResponse( $code );

		if ( $returnType === 'echo' || $returnType === '' )
		{
			echo $response['message'];
		}
		else if ( $returnType === 'code' )
		{
			return $response['code'];
		}
		else
		{
			return $response;
		}
	}


	/**
	 * Takes the code that it gets passed and builds a response with the code and message
	 * @param  integer $code A response code
	 * 
	 * @return array A complete response with a code and message
	 */
	public static function buildResponse( $code )
	{
		switch( $code )
		{
			case 0:
				$message = 'Success';
			break;

			case 1:
				$message = '<div class="op-error-message">It looks like your API credentials are incorrect. Your ONTRApages will not display properly until you fix this. Please <a href="edit.php?post_type=ontrapage&page=opsettings">click here</a> to remedy that & then try again.</div>';
			break;

			case 2:
				$message = '<div id="message" class="error is-dismissible">
					<p>ONTRApages Warning - It looks like you don\'t have any API credentials setup just yet. <a href="edit.php?post_type=ontrapage&page=opsettings">Click here</a> to remedy that & then try again.</p>
					</div>';
			break;

			case 3:
				$message = '<div class="op-error-message">Unfortunately it appears that you don\'t have any ONTRApages available. Create one by logging into your <a href="https://ontrapages.com" target="_blank">ONTRApages account</a> and then return when it has been saved!</div>';
			break;

			default:
				$message = '';
			break;
		}

		$response = array( 
			'code' => $code,
			'message' => $message 
			);

		return $response;
	}
}
