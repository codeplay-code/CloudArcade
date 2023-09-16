<?php
	if(isset($_GET['status'])){
		$type = 'success';
		$message = '';
		if($_GET['status'] == 'updated'){
			$message = 'CloudArcade successfully updated to version '.VERSION.'!';
		} elseif($_GET['status'] == 'error'){
			$type = 'warning';
			$message = 'Error: '.esc_string($_GET['info']);
		}
		show_alert($message, $type);
	}
?>
<div class="check-update"></div>
<div class="section">
<?php

if(!check_purchase_code() && !ADMIN_DEMO){
	echo('<div class="bs-callout bs-callout-warning"><p>Please provide your <b>Item Purchase code</b>. You can submit or update your Purchase code on site settings.</p><p><a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank">Where to get Envato purchase code?</a></p></div>');
} else {

if(!ADMIN_DEMO){
	if(function_exists('check_writeable')){
		if(!check_writeable()){
			$msg = 'CloudArcade don\'t have permissions to modify files, any settings can\'t be saved and can\'t do backup and update. Change all folders and files CHMOD to 777 to fix this.';
			show_alert($msg, 'warning');
		}
	}
	$pre = '';
	$beta = '';
	if(isset($_GET['beta'])){
		$beta = '&test';
		$pre = 'super';
	}
	$ch = curl_init('https://api.cloudarcade.net/verify/verify.php?action=next_update&ref='.DOMAIN.'&v='.VERSION.$beta);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$curl = curl_exec($ch);
	$data = json_decode($curl, true);
	curl_close($ch);
	if(isset($data['log'])){
		$v_latest = esc_int($data['version']);
		$v_current = esc_int(VERSION);
		if($v_current < $v_latest){
			echo('<div class="bs-callout bs-callout-info">CloudArcade version '.$data['version'].' is available.</div>');
			if(isset($data['info'])){
				echo($data['info']);
			}
			echo('<p>Changelog:</p>');
			echo('<ul>');
			foreach ($data['log'] as $key) {
				echo('<li>'.$key.'</li>');
			}
			echo('</ul>');
			if(isset($data['html'])){
				echo($data['html']);
			}
			//
			?>
			<div class="bs-callout bs-callout-info">Read more info about the update here <a href="https://cloudarcade.net/changelog/" target="_blank">https://cloudarcade.net/changelog/</a></div>
			<hr>
			<div class="progress mb-4 d-none">
				<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
			</div>
			<form id="form-update" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<input type="hidden" name="action" value="updater">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=update">
					<input type="hidden" name="code" minlength="5" value="<?php echo $pre.check_purchase_code() ?>" required/>
					<button type="submit" class="btn btn-primary btn-md" id="btn-update"><?php _e('Update') ?></button>
				</div>
			</form>

			<div id="update-error" class="d-none">
				<div class="bs-callout bs-callout-danger" id="u-error"></div>
				<?php _e('Server response') ?>:
				<div class="bs-callout bs-callout-warning" id="u-response"></div>
			</div>

			<?php
		} else {
			echo('<div class="bs-callout bs-callout-info">'._t('Congratulation! You are up to date.').'</div>');
		}
	}
} else {
	echo('<div class="bs-callout bs-callout-info">'._t('Congratulation! You are up to date.').'</div>');
}

?>

<hr>
<?php if(!ADMIN_DEMO){ ?>
<h4>Got an issues after updating?</h4>
<p>You can go back to previous version using <b>Backup Restore</b> plugin. Each update attept, system will create a backup file (Games and thumbnail files are not backed up).</p>
<p>Have an unknown issues? you can contact me through <a href="https://codecanyon.net/user/redfoc" target="_blank">codecanyon profile</a> page.</p>
<h4>How updater works?</h4>
<p>Updater will override specific file and folder that have an update. Updater can also modify database table.</p><p>Custom themes, connect.php, config.php will not be overridden.</p>
<?php } } ?>
</div>