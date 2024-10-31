<?php
// Manages the objects that get used in the BE settings of each ONTRApages or ONTRAform.
class OPObjects
{

	// Basic function that starts the process of getting and formatting the ONTRApage or ONTRAform objects. Returns the formatted objects ready to use.
	public static function getOPObjects( $type )
	{
		if ( get_option('opValidCreds') != 1 )
		{
			return 'auth-error';
		}

		$opObjects = OPObjects::createObject( $type );

		return $opObjects;
	}


	// Gets a single object. Used for ONTRAforms. Returns the object.
	public static function getOPObject( $type, $extraVars, $id )
	{
		$opObject = OPObjects::requestOPObjects( $type, $extraVars, $id );

		return $opObject;
	}


	// Works together with the requestObjects function to compile a complete object. It makes one call to requestObjects and if there are 50 object items then it makes more calls till it collects all the items. Returns a complete and formatted object.
	private static function createObject( $type )
	{
		$number = 0;
		$newOpObjects = array();
		$opObjects = array();

		$objectSet = OPObjects::requestOPObjects( $type );
		
		if ($objectSet == "timeout-error")
		{
			return "timeout-error";
		}

		if (!is_string($objectSet))
		{
			return "error in response";
		}

		$objectSet = json_decode( $objectSet, true);
		$objectSet = $objectSet["data"];

		//If the number of records is 50, let's see if there are more
		if ( count( $objectSet ) === 50 )
		{
			while ( $number > -1 )
			{
				if ( $number !== 0 )
				{
					$extraVars = '&start=' . $number;
					$objectSet = json_decode( OPObjects::requestOPObjects( $type, $extraVars ), true );
					$objectSet = $objectSet["data"];
				}

				foreach ($objectSet as $setData)
				{
					array_push($newOpObjects, $setData);
				}

				if ( count($objectSet) === 50 )
				{
					$number = $number + 50;
				}
				else
				{
					$number = -1;
				}
			}
		}
		else
		{
			$newOpObjects = $objectSet;
		}

		return $newOpObjects;
	}


	// Calls home to get all the ONTRApage objects using either the ontrapages or ontraport api. Then it works with modifyOntrapagesObjects to format the object correctly depending upon the API used. Returns a formatted object.
	private static function requestOPObjects( $type, $extraVars=false, $formId=false )
	{
		$appid = get_option( 'opAppID' );
		$key = get_option( 'opAPIKey' );

		switch ( $type )
		{
			case 'form':
				$request = OPAPI . 'object?objectID=122&id=' . $formId;
			break;

			case 'forms':
				$request = OPAPI . 'objects?objectID=122&performAll=true&sort=formname&sortDir=asc&condition=%5B%7B%22field%22%3A%7B%22field%22%3A%22type%22%7D%2C%22op%22%3A%22%3D%22%2C%22value%22%3A%7B%22value%22%3A11%7D%7D%5D&searchNotes=true&listFields=form_id%2Cformname%2Cfillouts' . $extraVars;
			break;

			case 'pages':

				// "`design_type` = '4' AND (`domain` != '' OR `resource` != '')"
				// Available wordpress pages are pages that have been published in app before, which means `resource` will be set even after unhosting blanks out `domain`
				// Edge case: If page is flagged as abusive on first publish, `resource` will be empty, but `domain` will be set 
					// `domain` != '' OR covers this case
                $condition = array(
					array("field" =>
                        array("field" => "design_type"),
                        "op" => "IN",
                        "value" => array("list" => array(array("value" => 3,), array("value" => 4)))
                    ),
					"AND",
					array(
					  array("field"=>
						array( "field"=>"domain"),
						"op"=> "!=",
						"value"=>
						array( "value"=>"")
					  ),
					  "OR",
					  array("field"=>
						array("field"=>"resource"),
						"op"=> "!=",
						"value"=>
						array("value"=> "")
					  )
					)
				  );
				
                $condition = json_encode($condition);
                $condition = urlencode($condition);
                $request = OPAPI . 'objects?objectID=20&performAll=true&sort=name&sortDir=asc&condition=' . $condition . '&searchNotes=true&listFields=id%2Cname%2Cdomain%2Cvisits_0%2Cvisits_1%2Cvisits_2%2Cvisits_3%2Ca_convert%2Cb_convert%2Cc_convert%2Cd_convert' . $extraVars;
                $opObjects = OPCoreFunctions::apiRequest( $request, $appid, $key );
                break;

			default:
				return;
		}

		$opObjects = OPCoreFunctions::apiRequest( $request, $appid, $key );

		if (!$opObjects)
		{
			return "timeout-error";
		}

		$opObjects = self::modifyOntrapagesObjects( $opObjects );

		if ( isset($opObjects) &&
			( $opObjects === 'Your App ID and API Key do not authenticate.' || $opObjects === 'Not Authorized' ) )
		{
			return 'auth-error';
		}
		else
		{
			return $opObjects;
		}
	}


	// Modify objects from the ONTRApages API to make the data from either API match each other and additionally does the math for the conversion stat. Returns a properly formatted object.
	protected static function modifyOntrapagesObjects( $opObjects )
	{
		$opObjects = json_decode( $opObjects );
		$ONTRApageObjects = $opObjects->data;

		if ( is_array($ONTRApageObjects) )
		{
			$modifiedObject = array();

			foreach ( $ONTRApageObjects as $opObject => $obj )
			{
				if ( get_option('opApiSource') === 'ontrapages' )
				{
					if ( $obj->a_sent === null )
						$obj->a_sent = 0;
					if ( $obj->b_sent === null )
						$obj->b_sent = 0;
					if ( $obj->c_sent === null )
						$obj->c_sent = 0;
					if ( $obj->d_sent === null )
						$obj->d_sent = 0;

					$obj->visits_0 = $obj->a_sent;
					$obj->visits_1 = $obj->b_sent;
					$obj->visits_2 = $obj->c_sent;
					$obj->visits_3 = $obj->d_sent;

					unset($obj->a_sent);
					unset($obj->b_sent);
					unset($obj->c_sent);
					unset($obj->d_sent);
					unset($obj->resource);

					if ( !isset($obj->a_convert) )
						$obj->a_convert = '0';
					if ( !isset($obj->b_convert) )
						$obj->b_convert = '0';
					if ( !isset($obj->c_convert) )
						$obj->c_convert = '0';
					if ( !isset($obj->d_convert) )
						$obj->d_convert = '0';
				}

				if ( $obj->a_convert != 0 && $obj->visits_0 != 0 )
					$obj->a_convert = round( ( $obj->a_convert / $obj->visits_0 ) * 100, 2);
				if ( $obj->b_convert != 0 && $obj->visits_1 != 0 )
					$obj->b_convert = round( ( $obj->b_convert / $obj->visits_1 ) * 100, 2);
				if ( $obj->c_convert != 0 && $obj->visits_2 != 0 )
					$obj->c_convert = round( ( $obj->c_convert / $obj->visits_2 ) * 100, 2);
				if ( $obj->d_convert != 0 && $obj->visits_3 != 0 )
					$obj->d_convert = round( ( $obj->d_convert / $obj->visits_3 ) * 100, 2);

				array_push( $modifiedObject, $obj );
			}

			$opObjects = array(
				'code' => 0,
				'data' => $modifiedObject );
		}

		$opObjects = json_encode( $opObjects );

		return $opObjects;
	}

}
