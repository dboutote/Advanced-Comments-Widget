<?php
/**
 * Tests
 */
 
function _acw_update_instance( $instance, $new_instance, $old_instance ){
	_debug($instance);
	_debug($new_instance);
	_debug($old_instance);
	$instance['exclude_pings']  = isset( $new_instance['exclude_pings'] ) ? (bool) $new_instance['exclude_pings'] : 0;
	return $instance;
}
#add_filter('acw_widget_update_instance', '_acw_update_instance');


function _acw_allowed_post_types( $post_types ) {
	$post_types['cpt_code'] = 'Code';
	
	return $post_types;
}

#add_filter( 'acw_widget_allowed_post_types', '_acw_allowed_post_types' );



function _acw_widget_comment_query_args( $query_args, $instance ) {
	$query_args['order'] = 'ASC';
	
	_debug($instance);
	
	return $query_args;
}

#add_filter( 'acw_widget_comment_query_args', '_acw_widget_comment_query_args', 0, 2 );


function _acw_comment_class( $comment_classes, $comment, $instance ) {

	$comment_classes[] = 'testing';
	
	return $comment_classes;
}

#add_filter( 'acw_comment_class', '_acw_comment_class', 0, 3 );


function _comments_clauses( $clauses, $query ){
_debug( $clauses );
	global $wpdb;
	$clauses[ 'fields' ] = "max({$wpdb->comments}.comment_ID)";
	$clauses['groupby'] = "{$wpdb->comments}.comment_post_ID";
	#$clauses['orderby'] = "{$wpdb->comments}.comment_ID DESC";
	return $clauses;
}
#add_filter( 'comments_clauses', '_comments_clauses', 0, 2 );


function _acw_form_defaults( $form_defaults ){
	unset( $form_defaults['title'] );
	return $form_defaults;
}
#add_filter( 'acw_form_defaults', '_acw_form_defaults' );