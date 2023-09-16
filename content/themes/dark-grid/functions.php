<?php

function list_categories(){
	$categories = fetch_all_categories();
	echo '<ul class="links list-categories">';
	foreach ($categories as $item) {
		echo '<a href="'. get_permalink('category', $item->slug) .'"><li>'. esc_string($item->name) .'</li></a>';
	}
	echo '</ul>';
}
function list_games($type, $amount, $count = false){
	echo '<div class="row">';
	$data = fetch_games_by_type($type, $amount, 0, $count);
	$games = $data['results'];
	foreach ( $games as $game ) { ?>
	<div class="col-4 list-tile">
		<a href="<?php echo get_permalink('game', $game->slug) ?>">
			<div class="list-game">
				<div class="list-thumbnail"><img src="<?php echo get_small_thumb($game) ?>" class="small-thumb" alt="<?php echo esc_string($game->title) ?>"></div>
			</div>
		</a>
	</div>
	<?php }
	echo '</div>';
}
function list_games_by_category($cat, $amount){
	// Deprecated, not used anymore
	echo '<div class="grid-layout grid-wrapper">';
	$data = get_game_list_category($cat, $amount);
	$games = $data['results'];
	foreach ( $games as $game ) { ?>
		<?php include  TEMPLATE_PATH . "/includes/grid.php" ?>
	<?php }
	echo '</div>';
}
function list_games_by_categories($cat, $amount){
	// Deprecated, not used anymore
	echo '<div class="grid-layout grid-wrapper">';
	$data = get_game_list_categories($cat, $amount);
	$games = $data['results'];
	foreach ( $games as $game ) { ?>
		<?php include  TEMPLATE_PATH . "/includes/grid.php" ?>
	<?php }
	echo '</div>';
}

function show_user_profile_header(){

	global $login_user;

	if($login_user){
	?>
	<div class="user-avatar">
		<img src="<?php echo get_user_avatar() ?>">
	</div>
	<ul class="user-links hidden">
		<li>
			<strong>
				<?php echo $login_user->username ?>
			</strong>
			<div class="label-xp"><?php echo $login_user->xp ?>xp</div>
		</li>
		<hr>
		<a href="<?php echo get_permalink('user', $login_user->username) ?>">
			<li><?php _e('My Profile') ?></li>
		</a>
		<a href="<?php echo get_permalink('user', $login_user->username, array('edit' => 'edit')) ?>">
			<li><?php _e('Edit Profile') ?></li>
		</a>
		<hr>
		<a href="<?php echo DOMAIN ?>admin.php?action=logout">
			<li class="text-danger"><?php _e('Log Out') ?></li>
		</a>
	</ul>
	<?php
	}
}

register_sidebar(array(
	'name' => 'Head',
	'id' => 'head',
	'description' => 'HTML element before &#x3C;/head&#x3E;',
));

register_sidebar(array(
	'name' => 'Sidebar 1',
	'id' => 'sidebar-1',
	'description' => 'Right sidebar',
));

register_sidebar(array(
	'name' => 'Footer 1',
	'id' => 'footer-1',
	'description' => 'Footer 1',
));

register_sidebar(array(
	'name' => 'Footer 2',
	'id' => 'footer-2',
	'description' => 'Footer 2',
));

register_sidebar(array(
	'name' => 'Footer 3',
	'id' => 'footer-3',
	'description' => 'Footer 3',
));

register_sidebar(array(
	'name' => 'Top Content',
	'id' => 'top-content',
	'description' => 'Above main content element. Recommended for Ad banner placement.',
));

register_sidebar(array(
	'name' => 'Bottom Content',
	'id' => 'bottom-content',
	'description' => 'Under main content element. Recommended for Ad banner placement.',
));

register_sidebar(array(
	'name' => 'Homepage Bottom',
	'id' => 'homepage-bottom',
	'description' => 'Bottom content on homepage. Can be used to show site description or explaining about your site.',
));

register_sidebar(array(
	'name' => 'Footer Copyright',
	'id' => 'footer-copyright',
	'description' => 'Copyright section.',
));

class widget_game_list extends Widget {
	function __construct() {
 		$this->name = 'Game List';
 		$this->id_base = 'game-list';
 		$this->description = 'Show game list ( Grid ). Is recommedned to put this on sidebar.';
	}
	public function widget( $instance, $args = array() ){
		$label = isset($instance['label']) ? $instance['label'] : '';
		$class = isset($instance['class']) ? $instance['class'] : 'widget';
		$type = isset($instance['type']) ? $instance['type'] : 'new';
		$amount = isset($instance['amount']) ? $instance['amount'] : 9;

		echo '<div class="'.$class.'">';

		if($label != ''){
			$icon = 'fa-plus';
			if($type != 'new'){
				$icon = 'fa-gamepad';
			}
			echo '<h4 class="widget-title"><i class="fa '.$icon.'" aria-hidden="true"></i>'.$label.'</h4>';
		}

		list_games($type, (int)$amount);
		echo '</div>';
	}

	public function form( $instance = array() ){

		if(!isset( $instance['label'] )){
			$instance['label'] = '';
		}
		if(!isset( $instance['type'] )){
			$instance['type'] = 'new';
		}
		if(!isset( $instance['amount'] )){
			$instance['amount'] = 9;
		}
		if(!isset( $instance['class'] )){
			$instance['class'] = 'widget';
		}
		?>
		<div class="form-group">
			<label><?php _e('Widget label/title (optional)') ?>:</label>
			<input type="text" class="form-control" name="label" placeholder="NEW GAMES" value="<?php echo $instance['label'] ?>">
		</div>
		<div class="form-group">
			<label><?php _e('Sort game list by') ?>:</label>
			<select class="form-control" name="type">
				<?php

				$opts = array(
					'new' => 'New',
					'popular' => 'Popular',
					'random' => 'Random',
					'likes' => 'Likes',
					'trending' => 'Trending'
				);

				foreach ($opts as $key => $value) {
					$selected = '';
					if($key == $instance['type']){
						$selected = 'selected';
					}
					echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
				}
				?>
			</select>
		</div>
		<div class="form-group">
			<label><?php _e('Amount') ?>:</label>
			<input type="number" class="form-control" name="amount" placeholder="9" min="1" value="<?php echo $instance['amount'] ?>">
		</div>
		<div class="form-group">
			<label><?php _e('Div class (Optional)') ?>:</label>
			<input type="text" class="form-control" name="class" placeholder="widget" value="<?php echo $instance['class'] ?>">
		</div>
		<?php
	}
}

register_widget( 'widget_game_list' );

if(file_exists(ABSPATH . TEMPLATE_PATH . '/includes/custom.php')){
	include(ABSPATH . TEMPLATE_PATH . '/includes/custom.php');
}

?>