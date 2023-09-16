<?php

function is_theme_has_thumbail($theme_name){

	$path = ABSPATH . 'content/themes/' . $theme_name;

	if(file_exists( $path . '/thumbnail.png' )){
		return true;
	}
}

if(!USER_ADMIN){
	die('P');
}

if(isset($_POST['action'])){
	if( ADMIN_DEMO ){
		echo 'Restricted for DEMO mode';
		return;
	}
	if($_POST['action'] == 'upload_theme_file'){
		if(check_purchase_code()){
			echo '<h4>'._t('Going to install theme file').'</h4><br>';
			if (!file_exists('tmp')) {
				mkdir('tmp', 0755, true);
			}
			if(file_exists('tmp/tmp_theme')){
				delete_files('tmp/tmp_theme/');
			}
			if (!file_exists('tmp/tmp_theme')) {
				mkdir('tmp/tmp_theme', 0755, true);
			}
			if (!file_exists('tmp/tmp_theme/files')) {
				mkdir('tmp/tmp_theme/files', 0755, true);
			}
			$enter_epc = false;
			$target_dir = "tmp/tmp_theme/";
			$extract_dir = "tmp/tmp_theme/files/";
			$target_file = $target_dir . strtolower(str_replace(' ', '-', basename($_FILES["theme_file"]["name"])));
			$theme_dir = str_replace('.zip', '', basename($_FILES["theme_file"]["name"]));
			$uploadOk = 1;
			$error = [];
			$warning = [];
			$json;
			$fileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
			if($fileType != 'zip'){
				$uploadOk = 0;
				$error[] = 'File format must be zip!';
			}
			if($uploadOk) {
				if (move_uploaded_file($_FILES["theme_file"]["tmp_name"], $target_file)) {
					$zip = new ZipArchive;
					$res = $zip->open($target_file);
					if ($res === TRUE) {
						$zip->extractTo($extract_dir);
						$zip->close();

						if(file_exists(ABSPATH . 'content/themes/' . $theme_dir)){
							$warning[] = 'Theme folder for this theme is already exist';
							$warning[] = 'Existing theme folder will be overriden';
						}
						if(!file_exists( $extract_dir . 'info.json' )){
							$error[] = 'Theme info (info.json) doesn\'t exist';
						} else {
							$json = json_decode(file_get_contents($extract_dir . 'info.json'), true);
						}
						if(!file_exists( $extract_dir . 'home.php' )){
							$error[] = 'home.php doesn\'t exist';
						}
						if(!file_exists( $extract_dir . 'page.php' )){
							$error[] = 'page.php doesn\'t exist';
						}
						if(!file_exists( $extract_dir . 'game.php' )){
							$error[] = 'game.php doesn\'t exist';
						}
						if(!file_exists( $extract_dir . 'archive.php' )){
							$error[] = 'archive.php doesn\'t exist';
						}
						if(!file_exists( $extract_dir . 'search.php' )){
							$error[] = 'search.php doesn\'t exist';
						}
						if(file_exists( $extract_dir . 'css/epc.css' )){
							$enter_epc = true;
						}
					} else {
						echo 'doh!';
					}
				}
			}
			if(count($error)){
				foreach ($error as $value) {
					show_alert($value, 'danger');
				}
			} else {
				if(count($warning)){
					foreach ($warning as $value) {
						show_alert($value, 'warning');
					}
				}
				echo '<br><b>Theme name</b>: '.$json['name'];
				echo '<br><b>Version</b>: '.$json['version'];
				echo '<br><b>Author</b>: '.$json['author'];
				echo '<br><b>Website</b>: <a href="'.$json['website'].'" target="_blank">'.$json['website'].'</a>';
				echo '<br><b>Description</b>: '.$json['description'];
				echo '<br><br>This theme is targeted for CloudArcade v'.$json['target_version'].' or newer.<br>';
				if(to_numeric_version(VERSION) < to_numeric_version($json['target_version'])){
					show_alert('You\'re using older version of CloudArcade, update your CMS to meet the requirement.', 'warning');
				} else {
					?>
					<br>
					<form id="form-upload-theme" action="dashboard.php?viewpage=themes" method="post" enctype="multipart/form-data">
						<input type="hidden" name="action" value="install_theme">
						<input type="hidden" name="file_name" value="<?php echo $theme_dir ?>">
						<input type="hidden" name="theme_name" value="<?php echo $json['name'] ?>">
						<?php if($enter_epc) { ?>
							<div class="mb-3">
								<label for="theme-license"><?php _e('Purchase code') ?>:</label>
								<input type="text" style="max-width: 600px;" class="form-control" id="theme-license" name="epc" placeholder="<?php _e('Enter purchase code') ?>">
							</div>
						<?php } ?>
						<input type="button" class="btn btn-primary btn-md" value="Install theme" onclick="this.form.submit()"/>
					</form>

				<?php }
			}
			delete_files($extract_dir);
		} else {
			show_alert('Item purchase code is required!', 'warning');
		}

	} else if($_POST['action'] == 'install_theme'){
		$continue = true;
		if(isset($_POST['epc'])){
			$continue = false;
			$curl = curl_request('https://api.cloudarcade.net/verify/verify.php?code='.get_setting_value('purchase_code').'&ref='.DOMAIN.'&v='.VERSION.'&action=check_theme_epc&validate&epc='.$_POST['epc'].'&theme_name='.str_replace(' ', '%20', $_POST['theme_name']));
			if($curl == 'valid'){
				$continue = true;
				update_option('epc_theme_'.$_POST['file_name'], $_POST['epc']);
			} else {
				show_alert('Theme purchase code not valid!', 'danger');
				show_alert('Contact seller for more info', 'warning');
			}
		}
		if($continue){
			echo '<h4>'._t('Installing theme').'</h4><br>';
			$target_file = 'tmp/tmp_theme/'.$_POST['file_name'].'.zip';
			if(file_exists($target_file)){
				$zip = new ZipArchive;
				$res = $zip->open($target_file);
				if ($res === TRUE) {
					$zip->extractTo('../content/themes/'.$_POST['file_name']);
					$zip->close();
					show_alert('Theme installed', 'success');
					delete_files('tmp/tmp_theme/');
					echo '<div id="theme-installed"></div>';
				} else {
					echo 'doh!';
				}
			} else {
				show_alert('Theme file is missing', 'danger');
			}
		}
	} else if($_POST['action'] == 'update'){
		echo '<h4>'._t('Update theme').'</h4>';
		//
		$url = 'https://api.cloudarcade.net/themes/fetch.php?action=info&code='. check_purchase_code();
		$url .= '&name='.$_POST['theme'];
		$url .= '&ref='.DOMAIN.'&theme-version='.$_POST['version'].'&v='.VERSION;
		$curl = curl_request($url);
		if($curl != ''){
			$json = json_decode($curl, true);
			echo '<br><b>Theme name</b>: '.$json['name'];
			echo '<br><b>Version</b>: '.$json['version'];
			echo '<br><b>Author</b>: '.$json['author'];
			echo '<br><b>Website</b>: '.$json['website'];
			echo '<br><b>Description</b>: '.$json['description'];
			if(isset($json['release_date'])){
				echo '<br><b>Release date</b>: '.$json['release_date'];
			}
			echo '<br><b>Changelog</b>: '.$json['changelog'];
			if(isset($json['html'])){
				echo $json['html'];
			}
			echo '<br><br>This theme is targeted for CloudArcade v'.$json['target_version'].' or newer.<br><br>';
			if(to_numeric_version(VERSION) < to_numeric_version($json['target_version'])){
				show_alert('You\'re using older version of CloudArcade, update your CMS to meet the requirement.', 'warning');
			} else {
				?>
				<br>
				<form action="dashboard.php?viewpage=themes" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="dl_theme">
					<input type="hidden" name="theme" value="<?php echo $_POST['theme'] ?>">
					<input type="hidden" name="version" value="<?php echo $_POST['version'] ?>">
					<input type="hidden" name="link" value="<?php echo $json['link'] ?>">
					<input type="button" class="btn btn-primary btn-md" value="Update theme" onclick="this.form.submit()"/>
				</form>

			<?php }
		}
	} else if($_POST['action'] == 'dl_theme'){
		$path = $_POST['link'];
		$target = '../t-update.zip';
		$affected_files = [];
		// Create a cURL resource
		$ch = curl_init($path);
		// Set cURL options for retrieving the remote file
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
		// Download the remote file and save it to the target file
		$remoteFile = curl_exec($ch);
		if($remoteFile !== false){
			$localFile = fopen($target, 'w');
			if($localFile){
				fwrite($localFile, $remoteFile);
				fclose($localFile);
				if(file_exists($target)){
					if (!file_exists(ABSPATH.'admin/backups')) {
						mkdir('backups/', 0755, true);
					}
					if(file_exists('../content/themes/'.$_POST['theme'].'/')){
						add_to_zip( '../content/themes/'.$_POST['theme'].'/', 'backups/'.$_SESSION['username'].'-'.$_POST['theme'].'-theme-backup-'.$_POST['version'].'-'.time().'.zip', [] );
					}
					$zip = new ZipArchive;
					$res = $zip->open($target);
					for( $i = 0; $i < $zip->numFiles; $i++ ){ 
						$stat = $zip->statIndex( $i );
						$name = basename( $stat['name'] );
						if(strpos($name, '.') !== false) {
							$affected_files[] = $name;
						}
					}
					if ($res === TRUE) {
						$zip->extractTo('../content/themes/'.$_POST['theme'].'/');
						$zip->close();
					} else {
						echo 'doh!';
					}
					unlink($target);
					show_alert('Theme updated!', 'success');
					show_alert('You can roll back to previous version if something happened.', 'info');
					echo '<div id="theme-updated"></div>';
					echo '<h4>'._t('Affected files').'</h4>';
					echo '<ol>';
					foreach ($affected_files as $key) {
						echo '<li>'.$key.'</li>';
					}
					echo '</ol>';
					echo '<a href="dashboard.php?viewpage=themes" class="btn btn-primary">'._t('Back to themes').'</a>';
				}
			} else {
				echo 'Could not create local file';
			}
		} else {
			echo 'Could not download remote file';
		}
		// Close the cURL resource
		curl_close($ch);
	} else if($_POST['action'] == 'duplicate'){
		$json = [];
		$json_path = ABSPATH . 'content/themes/' . $_POST['theme'] . '/info.json';
		if(file_exists( $json_path )){
			$json = json_decode(file_get_contents( $json_path ), true);
		}
		echo '<h4>'._t('Duplicate theme').'</h4>';
		echo '<p>Duplicated themes (Or custom themes) can\'t receive any updates and safe from overwritten update files.</p>';
		?>
		<div class="mb-4"></div>
		<form method="post">
			<input type="hidden" name="action" value="start_duplicate">
			<input type="hidden" name="target" value="<?php echo $_POST['theme'] ?>">
			<div class="mb-3">
				<label><?php _e('Theme Name') ?> (<?php _e('Must be unique') ?>):</label>
				<input type="text" style="max-width: 400px;" class="form-control" name="theme-name" placeholder="<?php _e('Latin characters only') ?>" value="<?php echo $json['name'] ?>" required>
			</div>
			<button type="submit" class="btn btn-primary btn-md"><?php _e('Duplicate') ?></button>
		</form>
		<?php
	} else if($_POST['action'] == 'start_duplicate'){
		$theme_name = htmlspecialchars($_POST['theme-name']);
		if($theme_name != $_POST['theme-name']){
			show_alert('Error! Theme name contain special characters!', 'danger');
		} else {
			$theme_dir = strtolower(str_replace(' ', '-', $theme_name));
			$dirs = scan_folder('content/themes/');
			$exist = false;
			foreach ($dirs as $dir) {
				$json_path = ABSPATH . 'content/themes/' . $dir . '/info.json';
				if(file_exists( $json_path )){
					if($dir == $theme_dir){
						$exist = true;
					}
				}
			}
			if($exist){
				show_alert('Theme folder with this name already exist!', 'warning');
			} else {
				$base = '../content/themes/'.$theme_dir;
				if(!file_exists($base)){
					mkdir($base, 0755, true);
				}
				function recursive_copy($src,$dst) {
					$dir = opendir($src);
					@mkdir($dst);
					while(( $file = readdir($dir)) ) {
						if (( $file != '.' ) && ( $file != '..' )) {
							if ( is_dir($src . '/' . $file) ) {
								recursive_copy($src .'/'. $file, $dst .'/'. $file);
							}
							else {
								copy($src .'/'. $file,$dst .'/'. $file);
							}
						}
					}
					closedir($dir);
				}
				recursive_copy('../content/themes/'.$_POST['target'], $base);
				//
				$json = [];
				$json_path = ABSPATH . 'content/themes/' . $theme_dir . '/info.json';
				if(file_exists( $json_path )){
					$json = json_decode(file_get_contents( $json_path ), true);
				}
				$json['name'] = $theme_name;
				$json['release_date'] = date('d/m/Y');
				file_put_contents($json_path, json_encode($json));
				show_alert('Theme successfully duplicated!', 'success');
			}
		}
	} else if($_POST['action'] == 'delete'){
		if(THEME_NAME == $_POST['theme']){
			show_alert('Active theme can\'t be deleted!', 'warning');
		} else {
			echo '<h4>'._t('Are you sure want to delete %a theme?', $_POST['theme-name']).'</h4>';
			?>
			<p>This action can't be undone</p>
			<div class="mb-4"></div>
			<form method="post">
				<input type="hidden" name="action" value="yes-delete">
				<input type="hidden" name="theme" value="<?php echo $_POST['theme'] ?>">
				<button type="submit" class="btn btn-danger btn-md"><?php _e('Delete') ?></button>
			</form>
			<?php
		}
	} else if($_POST['action'] == 'yes-delete'){
		if(file_exists('../content/themes/'.$_POST['theme'])){
			delete_files('../content/themes/'.$_POST['theme']);
		}
		show_alert('Theme files removed!', 'success');
		echo '<a href="dashboard.php?viewpage=themes" class="btn btn-primary">'._t('Back to themes').'</a>';
	} else if($_POST['action'] == 'begin-install-with-code'){
		$curl = curl_request('https://api.cloudarcade.net/themes/install.php?code='.get_setting_value('purchase_code').'&ref='.DOMAIN.'&v='.VERSION.'&action=install&validate&email='.$_POST['email'].'&pcode='.$_POST['pcode']);
		if(is_valid_json($curl)){
			$json = json_decode($curl, true);
			if($json['status'] == 'valid'){
				$status = 'warning';
				$message = 'Null';
				$continue = true;
				if(file_exists('../content/themes/'.$json['name'])){
					show_alert('Failed to install!', 'warning');
					$status = 'warning';
					$message = 'Theme folder '.$json['name'].' already exist!';
					$continue = false;
				} else {
					mkdir('../content/themes/'.$json['name'], 0755, true);
				}
				if($continue){
					$target = '../the-file.zip';
					$_ch = curl_init();
					curl_setopt($_ch, CURLOPT_URL, $json['link']);
					curl_setopt($_ch, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($_ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($_ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($_ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
					$remoteFile = curl_exec($_ch);
					if($remoteFile !== false){
						$localFile = fopen($target, 'w');
						if($localFile){
							fwrite($localFile, $remoteFile);
							fclose($localFile);
							if(file_exists($target)){
								$zip = new ZipArchive;
								$res = $zip->open($target);
								if ($res === TRUE) {
									$zip->extractTo('../content/themes/'.$json['name']);
									$zip->close();
									$status = 'success';
									$message = 'Theme installed!';
								} else {
									echo 'doh!';
								}
								unlink($target);
							}
						}
					}
					curl_close($_ch);
				}
				show_alert($message, $status);
				echo '<div id="theme-updated"></div>';
				echo '<a href="dashboard.php?viewpage=themes" class="btn btn-primary">'._t('Back to themes').'</a>';
			} else {
				show_alert($json['message'], 'danger');
			}
		} else {
			show_alert('Server error!', 'danger');
		}
	}
} else if(isset($_GET['action'])){
	if( ADMIN_DEMO ){
		echo 'Restricted for DEMO mode';
		return;
	}
	$action = $_GET['action'];
	if($action == 'details'){
		if(isset($_GET['theme'])){
			$json_path = ABSPATH . 'content/themes/' . $_GET['theme'] . '/info.json';
			if(file_exists( $json_path )){
				$json = json_decode(file_get_contents( $json_path ), true);
				echo '<br><b>Theme name</b>: '.$json['name'];
				echo '<br><b>Version</b>: '.$json['version'];
				echo '<br><b>Author</b>: '.$json['author'];
				echo '<br><b>Website</b>: <a href="'.$json['website'].'" target="_blank">'.$json['website'].'</a>';
				if(isset($json['documentation'])){
					echo '<br><b>Documentation</b>: <a href="'.$json['documentation'].'" target="_blank">'.$json['documentation'].'</a>';
				}
				echo '<br><b>Description</b>: '.$json['description'];
				if(isset($json['release_date'])){
					echo '<br><b>Release date</b>: '.$json['release_date'];
				}
				if(check_purchase_code()){
					?>
					<div class="mb-4"></div>
					<form method="post">
						<input type="hidden" name="action" value="duplicate">
						<input type="hidden" name="theme" value="<?php echo $_GET['theme'] ?>">
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Duplicate') ?></button>
					</form>
					<div class="mb-3"></div>
					<form method="post">
						<input type="hidden" name="action" value="delete">
						<input type="hidden" name="theme" value="<?php echo $_GET['theme'] ?>">
						<input type="hidden" name="theme-name" value="<?php echo $json['name'] ?>">
						<button type="submit" class="btn btn-danger btn-md"><?php _e('Delete') ?></button>
					</form>
					<?php
				}
			}
		}
	} else if($action == 'install-with-code'){
		?>
		<div class="bs-callout bs-callout-info">
			If you've already purchased a theme, you can submit your purchase code here to install the theme.
		</div>
		<div class="row">
			<div class="col-md-4">
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="begin-install-with-code">
					<div class="mb-3">
						<label class="form-label"><?php _e('The email you are using for the purchase') ?></label>
						<input type="email" class="form-control" name="email" required>
					</div>
					<div class="mb-3">
						<label class="form-label"><?php _e('Your theme purchase code') ?></label>
						<input type="text" class="form-control" name="pcode" required>
					</div>
					<input type="submit" class="btn btn-primary" value="<?php _e('Install') ?>">
				</form>
			</div>
		</div>
		<?php
	} else if($action == 'upload-theme'){
		?>
		<div class="bs-callout bs-callout-warning">
			Make sure you're uploading a theme file from a source you trust; otherwise, your theme file may contain malware or a backdoor that can harm your site.
		</div>
		<div class="row">
			<div class="col-md-4">
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="upload_theme_file">
					<div class="mb-3">
						<label class="form-label">Theme zip file</label>
						<input type="file" name="theme_file" class="form-control" accept=".zip">
					</div>
					<input type="submit" class="btn btn-primary" value="<?php _e('Upload theme') ?>">
				</form>
			</div>
		</div>
		<?php
	} else if($action == 'activate-theme'){
		if(isset($_POST['theme_dir'])){
			$json_path = '../content/themes/' . $_POST['theme_dir'] . '/info.json';
			if(file_exists($json_path)){
				$theme = json_decode(file_get_contents( $json_path ), true);
				if(to_numeric_version($theme['target_version']) > to_numeric_version(VERSION)){
					show_alert('Failed to activate!', 'warning');
					echo '<div class="bs-callout bs-callout-warning">'._t('This theme require CloudArcade v%a or newer.', $theme['target_version']).'</div>';
					echo '<a href="dashboard.php?viewpage=themes" class="btn btn-primary">'._t('Back to themes').'</a>';
				} else {
					// Theme activated
					update_setting('theme_name', $_POST['theme_dir']);
					show_alert('Theme activated!', 'success');
					echo '<a href="dashboard.php?viewpage=themes" class="btn btn-primary">'._t('Back to themes').'</a>';
				}
			}
		}
	}
} else {
	$dirs = scan_folder('content/themes/');
	$update_availabe = get_pref('updates');
	if(is_null($update_availabe)){
		$update_availabe = [];
	} else {
		$update_availabe = json_decode($update_availabe, true);
		if(!isset($update_availabe['themes'])){
			$update_availabe['themes'] = [];
		}
	}
	foreach ($dirs as $dir) {
		$json_path = ABSPATH . 'content/themes/' . $dir . '/info.json';
		if(file_exists( $json_path )){
			$theme = json_decode(file_get_contents( $json_path ), true);
			$disabled = '';
			$btn_label = _t('Activate');
			$thumb;
			if( THEME_NAME == $dir){
				$disabled = _t('disabled');
				$btn_label = _t('Activated');
			}
			if(is_theme_has_thumbail($dir)){
				$thumb = DOMAIN . 'content/themes/' . $dir . '/thumbnail.png'; 
			} else {
				$thumb = DOMAIN . 'images/theme-no-thumb.png'; 
			} ?>

			<div class="theme">
				<a href="dashboard.php?viewpage=themes&theme=<?php echo $dir ?>&action=details">
					<div class="theme-thumbnail">
						<img src="<?php echo $thumb ?>">
						<div class="theme-overlay">
							<i class="fas fa-info-circle"></i>
						</div>
					</div>
				</a>
				<?php if(isset($update_availabe['themes'][$dir])){ ?>
					<div class="theme-update-wrapper">
						<div class="theme-update-info">
							<?php _e('Update available!') ?>
							<div class="float-right">
								<form action="dashboard.php?viewpage=themes" method="post" enctype="multipart/form-data">
									<input type="hidden" name="action" value="update">
									<input type="hidden" name="theme" value="<?php echo $dir ?>">
									<input type="hidden" name="version" value="<?php echo $theme['version'] ?>">
									<input type="button" class="text-primary" value="<?php _e('Update') ?>" onclick="this.form.submit()"/>
								</form>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="theme-id-container">
					<div class="theme-name"> <?php echo $theme['name'] ?> </div>
					<div class="theme-action">
						<form action="dashboard.php?viewpage=themes&action=activate-theme" method="post" enctype="multipart/form-data">
							<input type="hidden" name="theme_dir" value="<?php echo $dir ?>">
							<input type="submit" class="btn-theme btn btn-primary btn-sm" value="<?php echo $btn_label ?>" <?php echo $disabled ?>>
						</form>
					</div>
					<div class="theme-info">
						<div class="theme-author"><?php _e('Author') ?>: <a href="<?php echo $theme['website'] ?>" target="_blank"><?php echo $theme['author'] ?></a></div>
						<div class="theme-version">v<?php echo $theme['version'] ?></div>
					</div>
				</div>
			</div>
			<?php
		}
	}

	?>

	<div class="theme theme-add" id="add-theme">
		<i class="fa fa-plus-circle theme-add-icon"></i>
	</div>

<?php } ?>

<!-- Modal -->
<div class="modal fade" id="modal-add-theme" tabindex="-1" role="dialog" aria-labelledby="add-theme-modal-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="add-theme-label"><?php _e('Add new theme') ?></h5>
				<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<a href="dashboard.php?viewpage=themes&action=install-with-code" class="btn btn-primary"><?php _e('Install with purchase code') ?></a>
				</div>
				<div class="mb-3">
					<a href="dashboard.php?viewpage=themes&action=upload-theme" class="btn btn-primary"><?php _e('Upload theme') ?></a>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		if($('#theme-updated').length){
			check_theme_update();
		}
		if($('#theme-installed').length){
			check_theme_update();
		}
		$('#add-theme').on('click', function(){
			$('#modal-add-theme').modal('show');
		});
	});
</script>