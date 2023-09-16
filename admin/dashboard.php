<?php
session_start();

$action = isset( $_POST['action'] ) ? $_POST['action'] : "";

require "../config.php";
require "../init.php";
require "admin-functions.php";

if ( !$login_user ) {
	header('Location: '. get_permalink('login'));
	return;
}

if(!USER_ADMIN){
	exit('Access forbidden!');
}

load_language('admin');

require( "../includes/plugin.php" );

if(count($plugin_list) > 0){
	// If plugin exist
}

$update_availabe = get_pref('updates');
if(is_null($update_availabe)){
	$update_availabe = [];
} else {
	$update_availabe = json_decode($update_availabe, true);
}

$pages = array (
	array(_t('Dashboard'), 'dashboard', 'home'),
	array(_t('Game list'), 'gamelist', 'gamepad'),
	array(_t('Add game'), 'addgame', 'plus-circle'),
	array(_t('Categories'), 'categories', 'th-large'),
	array(_t('Collections'), 'collections', 'th-list'),
	array(_t('Pages'), 'pages', 'book'),
	array(_t('Themes'), 'themes', 'palette'),
	array(_t('Plugins'), 'plugin', 'plug'),
	array(_t('Layout'), 'layout', 'columns'),
	array(_t('Settings'), 'settings', 'cog'),
	array(_t('Updater'), 'update', 'sync-alt'),
);

$page_name = 'Dashboard';
$page_slug = 'dashboard';
$page_icon;
if(isset($_GET['viewpage'])){
	$page_slug = htmlspecialchars($_GET['viewpage']);
}

if(!check_purchase_code()){
	if($page_slug != 'settings'){
		header('Location: dashboard.php?viewpage=settings', true);
		exit();
	}
	for ($i=count($pages)-1; $i >= 0 ; $i--) { 
		if($pages[$i][1] != 'settings'){
			array_splice($pages, $i, 1);
		}
	}
}

foreach ($pages as $item) {
	$active = '';
	if($item[1] == $page_slug){
		$page_name = _t(esc_string($item[0]));
		$page_slug = esc_string($item[1]);
		$page_icon = $item[2];
		$active = 'active';
	}
}

?>

<!DOCTYPE html>
<html lang="en" class="theme-light">
<head>
	<meta charset="utf-8">
	<title>Admin | <?php echo $page_name . ' - ' . SITE_TITLE ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<link rel="stylesheet" type="text/css" href="../vendor/bootstrap5/css/bootstrap.min.css" />
	<!-- Font Awesome icons (free version)-->
	<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" crossorigin="anonymous" defer>
	<link rel="stylesheet" type="text/css" href="style/admin.css?v=<?php echo VERSION ?>">
	<?php
		if ($page_slug == 'layout'){
			echo '<link rel="stylesheet" type="text/css" href="style/jquery.nestable.css?v='.VERSION.'">';
			echo '<link rel="stylesheet" type="text/css" href="style/menus.css?v='.VERSION.'">';
		}
	?>
	<script type="text/javascript" src="../js/jquery-3.6.2.min.js"></script>
	<script type="text/javascript" src="../js/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../vendor/bootstrap5/js/bootstrap.bundle.min.js"></script>
	<script type="text/javascript" src="../js/chart/utils.js"></script>
	<script type="text/javascript" src="../js/chart/Chart.min.js"></script>
</head>
<body>
<div class="main-header">
	<nav class="navbar navbar-expand-lg navbar-light top-nav" id="mainNav">
		<div class="container-fluid">
			<button class="sidebar-toggler" type="button" onclick="openSidebar()">
				<span class="navbar-toggler-icon"></span>
			</button>
			<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#quickLinks" aria-controls="quickLinks" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>
			<div class="collapse navbar-collapse justify-content-end" id="quickLinks">
				<div class="quicklinks">
					<a href="<?php echo DOMAIN ?>admin.php?action=logout" class="btn btn-sm btn-danger">
						<i class="fas fa-sign-out-alt"></i>
					</a>
					<a href="<?php echo DOMAIN ?>" target="_blank" class="btn btn-sm btn-primary">
						<i class="fas fa-external-link-alt"></i> <?php _e('VISIT SITE') ?>
					</a>
				</div>
			</div>
		</div>
	</nav>
</div>
<div class="admin-container">
	<div class="sidebar" id="sidebar">
		<a class="navbar-brand" href="#">
			<img src="../images/logo-horizontal.png" class="logo" alt="Logo">
		</a>
		<div class="admin-menu">
			<ul id="menu-list">
				<?php
				$i = 0;
				foreach ($pages as $item) {
					$active = '';
					if($item[1] == $page_slug){
						//$page_name = _t(esc_string($item[0]));
						//$page_slug = esc_string($item[1]);
						$active = 'active';
					}
					if($item[1] == 'plugin'){
						//Dropdown
						echo '<li class="'.$active.'">';

						?>
						<div class="li-list dropdown-btn">
							<i class="fa fa-<?php echo $item[2] ?>" aria-hidden="true"></i>
							<?php echo esc_string($item[0]); ?>
							<i class="fa fa-caret-down"></i>

						</div>
						<?php

						echo '</li>';

						?>
						
						<div class="dropdown-container <?php echo $active ?>">
							<a href="?viewpage=<?php echo $item[1] ?>">
								<?php
									$selected_plugin = '';
									$active_child = '';
									if(isset($_GET['name'])){
										$selected_plugin = $_GET['name'];
									} else {
										$active_child = 'active';
									}
								?>
								<div class="dropdown-list <?php echo $active_child ?>">
									<?php _e('Manage Plugins') ?>
								</div>
							</a>
							<?php
								foreach ($plugin_list as $plugin) {
									if(substr($plugin['dir_name'], 0, 1) != '_'){
										$active_child = '';
										if($selected_plugin == $plugin['dir_name']){
											$active_child = 'active';
										} ?>
										<a href="?viewpage=<?php echo $item[1] ?>&name=<?php echo $plugin['dir_name'] ?>">
											<div class="dropdown-list <?php echo $active_child ?>">
												<?php _e($plugin['name']) ?>
											</div>
										</a>
										<?php
									}
								}
							?>
						</div>

					<?php
					} else {
						//Regular menu
						echo '<li class="'.$active.'">';
						echo '<a href="?viewpage='.$item[1].'">';
						echo '<div class="li-list" name="dashboard"><i class="fa fa-'.$item[2].'" aria-hidden="true"></i>';
						echo esc_string($item[0]);
						if($item[1] == 'themes' || $item[1] == 'update'){
							if(isset($update_availabe[$item[1]])){
								echo '<i class="fa fa-exclamation-circle has-update-icon -u-'.$item[1].'"></i>';
							}
						}
						echo '</div></a>';
						echo '</li>';
					}
					$i++;
				}
				?>
			</ul>

			<div class="form-check custom-switch" style="margin-left: 20px;">
			  <input type="checkbox" class="form-check-input" id="darkSwitch" onclick="toggleTheme()">
			  <label class="form-check-label" for="darkSwitch"><?php _e('Dark Mode') ?></label>
			</div>
			<div class="cms justify-content-center" style="display: flex;">
				<a href="http://cloudarcade.net" target="_blank" style="margin-right: 10px">Cloud Arcade</a> v<?php echo VERSION ?>
			</div>
		</div>
	</div>
	<div class="content" id="content">
		<?php if( ADMIN_DEMO ){
			show_alert('(Admin Demo) All actions are not saved.', 'warning');
		} ?>

		<h3 class="page-title"><i class="fa fa-<?php echo $page_icon ?>"></i> <?php echo esc_string($page_name); ?></h3>

		<?php include 'core/'.$page_slug.'.php'; ?>

	</div>
	<span id="cms-version" style="display: none;"><?php echo VERSION ?></span>
</div>
<script type="text/javascript" src="../js/script.js?v=<?php echo VERSION ?>"></script>
<?php
	if ($page_slug == 'dashboard'){
		echo '<script type="text/javascript" src="../js/chart/stats.js?v='.VERSION.'"></script>';
	} elseif ($page_slug == 'layout'){
		echo '<script type="text/javascript" src="../js/jquery.nestable.js"></script>';
		echo '<script type="text/javascript" src="../js/menu.js?v='.VERSION.'"></script>';
	}
?>
</body>
</html>