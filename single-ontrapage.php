<?php 
// The template that is used on the FE to display the ONTRApage. Gets the ONTRApage object ID associated with the particular page visited, passes it off to ONTRApages::getONTRApageHTML() which reutnrs the HTML. Then it echo's it out. 
global $post;
global $wp_query;

if ( $post === null )
{
	$pageId = $wp_query->query_vars['page_id'];
	$opID = get_post_meta($pageId, 'ontrapage', true);
}
else
{
	$opID = get_post_meta($post->ID, 'ontrapage', true);
}


$html = ONTRApage::getONTRApageHTML( $opID );

if ( $html === 'auth-error' )
{
	echo 'There seems to be a problem with your ONTRApages API settings. Please update them and try again.';
}
else
{
	echo $html;
}