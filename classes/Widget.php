<?php

$widget_factory;

$registered_sidebars = array();
 
class Widget {
	public $name;
	public $id_base;
	public $description = '';

	public function __construct() {
		// actual widget processes
	}
 
	public function widget( $instance, $args  ) {
		// outputs the content of the widget
	}
 
	public function form( $instance ) {
		echo '<p class="no-options-widget">' . __( 'There are no options for this widget.' ) . '</p>';
		return 'noform';
	}
 
	public function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}

class Widget_Factory {
	public $widgets = array();

	public function register( $widget ) {
		if ( $widget instanceof Widget ) {
			$this->widgets[ spl_object_hash( $widget ) ] = $widget;
		} else {
			$this->widgets[ $widget ] = new $widget();
		}
	}

	public function unregister( $widget ) {
		if ( $widget instanceof WP_Widget ) {
			unset( $this->widgets[ spl_object_hash( $widget ) ] );
		} else {
			unset( $this->widgets[ $widget ] );
		}
	}
}

$widget_factory = new Widget_Factory();

function register_widget($widget){
	global $widget_factory;
	$widget_factory->register($widget);
}

function the_widget( $widget, $instance = array(), $args = array() ){
	global $widget_factory;
	if ( ! isset( $widget_factory->widgets[ $widget ] ) ) {
		return;
	}

	$widget_obj = $widget_factory->widgets[ $widget ];
	if ( ! ( $widget_obj instanceof Widget ) ) {
		return;
	}

	$widget_obj->widget( $instance, $args );
}

function get_widget( $widget, $instance = array(), $args = array() ){
	global $widget_factory;
	if ( ! isset( $widget_factory->widgets[ $widget ] ) ) {
		return;
	}

	$widget_obj = $widget_factory->widgets[ $widget ];
	if ( ! ( $widget_obj instanceof Widget ) ) {
		return;
	}

	return $widget_obj;
}

function widget_exists($widget){
	global $widget_factory;
	if(isset($widget_factory->widgets[ $widget ])){
		return true;
	} else {
		return false;
	}
}

require( ABSPATH . 'includes/widgets.php' );
 
?>