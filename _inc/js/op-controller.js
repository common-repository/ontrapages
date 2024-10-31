// Manages the select options for the ONTRApage Objects.
ontraPages.controller('OPMetaBoxController', function($scope)
{
	if ( typeof window.ontrapages !== 'undefined' )
	{
		$scope.pageId = window.ontrapages.pageId;
		$scope.pages = window.ontrapages.pages;

		if ( $scope.pageId === -1 )
		{
			$scope.selectedPage = 'Choose which ONTRApage you would like to use';
		}
		else
		{
			$scope.selectedPage = {};
			$scope.selectedPage.id = $scope.pageId;

			angular.forEach( $scope.pages, function(pageObj, index) 
			{
				if ( pageObj.id == $scope.pageId )
				{
					$scope.sPage = pageObj;
				}
			});
		}

		$scope.pageChanged = function()
		{
			angular.forEach( $scope.pages, function(pObj, index) 
			{
				if ( pObj.id == $scope.selectedPage )
				{
					$scope.sPage = pObj;
				}
			});
		}
	}

});


// Manages the options for the ONTRAform settings.
ontraPages.controller('ONTRAFormsController', function($scope, $timeout, $location, $anchorScroll)
{
	$scope.updateLightboxType = function(type)
	{
		$scope.scrollTo = function()
		{
			$location.hash('opMetabox');
			$anchorScroll();
		}

		$scope.lightboxType = type;
		$timeout($scope.scrollTo, 10);
	}

	$scope.updateFormID = function(id)
	{
		$scope.ontraformid = id;
	}

	$scope.updatePopPosition = function(type)
	{
		$scope.opPopPosition = type;
	}
});