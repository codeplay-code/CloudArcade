<?php

require( '../../config.php' );
require( '../../init.php' );
require( '../admin-functions.php' );

if(isset($_POST['action'])){
	$action = $_POST['action'];
	if($action == 'upload_image'){
		// Fix post / get issue on page.php Gallery plugin
		$_GET['action'] = $action;
	}
	$super_user = false;
	if( $login_user && USER_ADMIN && !ADMIN_DEMO ){
		$super_user = true;
	}

	if($action == 'save_widgets_position'){
		$data = $_POST['data'];
		if( $super_user ){
			update_option('widgets', json_encode($data));
			echo 'ok';
		}
	} elseif($action == 'update_widget'){
		$data = $_POST['data'];
		if( $super_user ){
			$widget_data = get_pref('widgets') ?: "[]";
			$stored_widgets = json_decode($widget_data, true);
			
			foreach ($stored_widgets as $key => $item) {
				if($key == $_POST['parent']){
					$stored_widgets[$key][(int)$_POST['index']] = $data;
					break;
				}
			}

			update_option('widgets', json_encode($stored_widgets));
			echo 'ok';
		}
	} elseif($action == 'delete_widget'){
		if( $super_user ){
			$widget_data = get_pref('widgets') ?: "[]";
			$stored_widgets = json_decode($widget_data, true);
			
			foreach ($stored_widgets as $key => $item) {
				if($key == $_POST['parent']){
					unset($stored_widgets[$key][(int)$_POST['index']]);
					if(count($stored_widgets[$key])){
						$stored_widgets[$key] = array_values($stored_widgets[$key]);
					}
					break;
				}
			}

			update_option('widgets', json_encode($stored_widgets));
			echo 'ok';
		}
	} elseif($action == 'check_theme_updates'){
		if( $super_user ){
			function set_cd(){
				$conn = open_connection();
				$st = $conn->prepare( 'UPDATE settings SET value = "" WHERE name = "purchase_code"' );
				$st->execute();
			}
			$themes = [];
			$dirs = scan_folder('content/themes/');
			foreach ($dirs as $dir) {
				$json_path = ABSPATH . 'content/themes/' . $dir . '/info.json';
				if(file_exists( $json_path )){
					$theme = json_decode(file_get_contents( $json_path ), true);
					$themes[$dir] = array(
						'name' => $theme['name'],
						'version' => $theme['version']
					);
				}
			}
			$update_availabe = get_pref('updates');
			if(is_null($update_availabe)){
				$update_availabe = [];
			} else {
				$update_availabe = json_decode($update_availabe, true);
			}
			$url = 'https://api.cloudarcade.net/themes/fetch.php?action=check&code='. check_purchase_code();
			$url .= '&data='.urlencode(json_encode($themes));
			$url .= '&ref='.DOMAIN.'&v='.VERSION;
			$curl = curl_request($url);
			if($curl != ''){
				if($curl == 'bl'){
					set_cd();
				} else if($curl == 'invalid'){
					set_cd();
				} else {
					$update_list = json_decode($curl, true);
					if(count($update_list)){
						if(!isset($update_availabe['themes'])){
							$update_availabe['themes'] = [];
						}
						if(json_encode($update_list) != json_encode($update_availabe['themes'])){
							$update_availabe['themes'] = $update_list;
							update_option('updates', json_encode($update_availabe));
						}
					}
				}
				echo 'ok';
			} else {
				if($curl == 'bl'){
					set_cd();
				} else {
					if(!is_null($update_availabe) && count($update_availabe)){
						if(isset($update_availabe['themes'])){
							unset($update_availabe['themes']);
							update_option('updates', json_encode($update_availabe));
						}
					}
				}	
				echo 'ok';
			}
		}
	} elseif($action == 'update_alert'){
		if( $super_user ){
			$update_availabe = get_pref('updates');
			
			if(is_null($update_availabe)){
				$update_availabe = [];
			} else {
				$update_availabe = json_decode($update_availabe, true);
			}
			
			$update_availabe[$_POST['type']] = true;

			update_option('updates', json_encode($update_availabe));
			echo 'ok';
		}
	} elseif($action == 'unset_update_alert'){
		if( $super_user ){
			$update_availabe = get_pref('updates');
			
			if(is_null($update_availabe)){
				$update_availabe = [];
			} else {
				$update_availabe = json_decode($update_availabe, true);
			}

			if(isset($update_availabe[$_POST['type']])){
				unset($update_availabe[$_POST['type']]);
				update_option('updates', json_encode($update_availabe));
			}
			echo 'ok';
		}
	} elseif($action == 'get_plugin_list'){
		//Used for plugin updates
		if( $super_user ){
			require_once('../../includes/plugin.php');
			if(count($plugin_list)){
				$list = [];
				foreach($plugin_list as $plugin){
					if($plugin['author'] == 'RedFoc' || $plugin['author'] == 'CloudArcade'){
						array_push($list, array(
							'dir_name' => $plugin['dir_name'],
							'version' => $plugin['version']
						));
					}
				}
				$result = array(
					'plugins' => json_encode($list),
					'code' => check_purchase_code(),
					'version' => VERSION,
					'domain' => DOMAIN
				);
				echo json_encode($result);
			}
		}
	} elseif($action == 'get_plugin_repo_list'){
		//Used for plugin updates
		if( $super_user ){
			require_once('../../includes/plugin.php');
			if(true){
				$list = [];
				foreach($plugin_list as $plugin){
					if($plugin['author'] == 'RedFoc' || $plugin['author'] == 'CloudArcade'){
						array_push($list, array(
							'dir_name' => $plugin['dir_name'],
							'version' => $plugin['version']
						));
					}
				}
				$result = array(
					'plugins' => json_encode($list),
					'code' => check_purchase_code(),
					'version' => VERSION,
					'domain' => DOMAIN
				);
				$url = 'https://api.cloudarcade.net/plugin-repo/fetch2.php?ref='.DOMAIN.'&code='.check_purchase_code().'&v='.VERSION;
				$curl = curl_request($url);
				if($curl != ''){
					$json = json_decode($curl, true);
					if(isset($json['status']) && $json['status'] == 'failed'){
						show_alert($json['info'], 'danger', false);
						exit();
					}
					if(!$json){
						echo $curl;
						exit();
					}
					try {
						$filtered_plugin = []; // Plugin list that aren't installed
						foreach ($json as $plugin) {
							if(!is_plugin_exist($plugin['dir_name'])){
								$filtered_plugin[] = $plugin;
							}
						}
						?>
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th>#</th>
										<th>Plugin</th>
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
									<?php
									$index = 0;
									foreach ($filtered_plugin as $_plugin) {
										$index++;
										if($_plugin){ ?>
											<tr>
												<th scope="row"><?php echo $index ?></th>
												<td>
													<strong class="plugin-repo-name"><?php echo $_plugin['name'] ?></strong>
													<p><?php echo $_plugin['description'] ?></p>
													Version: <?php echo $_plugin['version'] ?><br>
													Last update: <?php echo $_plugin['last_update'] ?><br>
													Require CA version: <?php echo $_plugin['require_version'] ?><br>
													Tested CA version: <?php echo $_plugin['tested_version'] ?><br>
													Author: <a href="<?php echo $_plugin['website'] ?>" target="_blank"><?php echo $_plugin['author'] ?><br>
												</td>
												<td>
													<a href="#" class="add-plugin-repo" data-reqversion="<?php echo $_plugin['require_version'] ?>" data-url="<?php echo $_plugin['url'] ?>">
														<i aria-hidden="true" class="fa fa-plus circle"></i>
													</a>
												</td>
											</tr>
										<?php }
									}
									?>
								</tbody>
							</table>
						</div>
					<?php } catch (Throwable $e){
						show_alert('An error occured while parsing plugin data', 'danger', false);
					}
				}
			}
		}
	} elseif($action == 'update_plugin'){
		if( $super_user ){
			$target = ABSPATH.'content/plugins/tmp_plugin.zip';
			file_put_contents($target, file_get_contents($_POST['path'].'.zip'));
			if(file_exists($target)){
				$zip = new ZipArchive;
				$res = $zip->open($target);
				if ($res === TRUE) {
					$zip->extractTo(ABSPATH.'content/plugins/');
					$zip->close();
					$status = 'success';
					$info = 'Plugin installed!';
				} else {
				  echo 'doh!';
				}
				unlink($target);
				echo 'ok';
			} else {
				echo 'not found';
			}
		}
	} elseif($action == 'get_quote'){
		$url = 'https://api.cloudarcade.net/get_quote.php?ref='.DOMAIN.'&code='.check_purchase_code().'&v='.VERSION;
		$curl = curl_request($url);
		echo $curl;
	} elseif($action == 'delete_image'){
		if( $super_user && isset($_POST['name']) ){
			if(file_exists('../../files/images/'.$_POST['name'])){
				unlink('../../files/images/'.$_POST['name']);
				if(!file_exists('../../files/images/'.$_POST['name'])){
					echo 'ok';
				} else {
					echo 'Failed to delete';
				}
			} else {
				echo 'File not exist';
			}
		}
	}
}
if(isset($_GET['action'])){

	$action = $_GET['action'];

	$super_user = false;
	if( $login_user && USER_ADMIN && !ADMIN_DEMO ){
		$super_user = true;
	}
	if($action == 'upload_image'){
		if( $super_user ){
			$target_dir = '../../files/images/';
			// Ensure directories exist
			if (!file_exists('../../files')) {
				mkdir('../../files', 0755, true);
			}
			if (!file_exists($target_dir)) {
				mkdir($target_dir, 0755, true);
			}
			if(file_exists($target_dir)){
				// Prepare array to hold uploaded files
				$files_to_upload = [];
				if(isset($_FILES['file-0'])){
					$files_to_upload[] = $_FILES['file-0'];
				}
				if(isset($_FILES['files']) && is_array($_FILES['files']['name'])){
					for($i=0; $i < count($_FILES['files']['name']); $i++) {
						$files_to_upload[] = [
							'name' => $_FILES['files']['name'][$i],
							'type' => $_FILES['files']['type'][$i],
							'tmp_name' => $_FILES['files']['tmp_name'][$i],
							'error' => $_FILES['files']['error'][$i],
							'size' => $_FILES['files']['size'][$i],
						];
					}
				}
				$results = [];
				foreach($files_to_upload as $file_to_upload){
					$file_to_upload['name'] = strtolower($file_to_upload['name']);
					$file_to_upload['name'] = check_file_name_exist($target_dir, $file_to_upload['name']);

					$uploaded_url = '/files/images/' . $file_to_upload["name"];
					$target_file = $target_dir . $file_to_upload["name"];
					$ok = false;

					// Validate file type
					$validTypes = ['image/png', 'image/jpg', 'image/jpeg', 'image/gif'];
					if (in_array($file_to_upload['type'], $validTypes)) {
						$ok = true;
					}
					if($ok){
						if (move_uploaded_file($file_to_upload["tmp_name"], $target_file)) {
							$results[] = [
								'url' => $uploaded_url,
								'name' => $file_to_upload['name'],
								'size' => $file_to_upload['size'],
							];
						} else {
							echo '{"errorMessage": "'._t('Upload failed!').'"}';
							exit();
						}
					} else {
						echo '{"errorMessage": "'._t('Image mime type not valid!').'"}';
						exit();
					}
				}
				echo json_encode(['result' => $results]);
			} else {
				echo '{"errorMessage": "'._t('Target dir not exist!').'"}';
			}
		}
	}
}

// Check if file name exists and return a new file name if it does
function check_file_name_exist($dir, $fileName) {
	$path = $dir . $fileName;
	if (file_exists($path)) {
		$info = pathinfo($fileName);
		$name = $info['filename'] . '-copy';
		$ext  = $info['extension'];
		return $name . "." . $ext;
	}
	return $fileName;
}

?>