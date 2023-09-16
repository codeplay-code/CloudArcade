<?php
$warning_list = get_admin_warning();
if(!empty($warning_list)){
	echo('<div class="site-warning">');
	foreach ($warning_list as $val) {
		show_alert($val, 'warning');
	}
	echo('</div>');
}
if(isset($_GET['status'])){
	// Old method
	$type = 'success';
	$message = '';
	if($_GET['status'] == 'saved'){
		$message = 'Settings saved!';
	} elseif($_GET['status'] == 'error'){
		$type = 'danger';
		$message = 'Error!';
		if(isset($_GET['info'])){
			$message = $_GET['info'];
		}
	}
	if(isset($_SESSION['message'])&&($_SESSION['classmessage'])){
		show_alert($_SESSION['message'], $_SESSION['classmessage']);
		unset($_SESSION['message']);
	} else {
		show_alert($message, $type);
	}
}
if(isset($_SESSION['message'])){
	// [New] preferred method
	if(isset($_SESSION['message']['text'])){
		show_alert($_SESSION['message']['text'], $_SESSION['message']['type']);
	}
	unset($_SESSION['message']);
}
?>
<div class="section section-full">
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active" data-bs-toggle="tab" href="#general"><?php _e('General') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#advanced"><?php _e('Advanced') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#user"><?php _e('User') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#custom-path"><?php _e('Custom path') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#listings"><?php _e('Listings') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#other"><?php _e('Other') ?></a>
		</li>
	</ul>
	<div class="general-wrapper">
		<div class="tab-content">
			<div class="tab-pane tab-container active" id="general">
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="general">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
					<div class="mb-3 row">
						<label for="title" class="col-sm-2 col-form-label"><?php _e('Site title') ?>:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="data[site_title]" minlength="4" value="<?php echo esc_string(SITE_TITLE) ?>" required>
						</div>
					</div>
					<div class="mb-3 row">
						<label for="description" class="col-sm-2 col-form-label"><?php _e('Site description') ?>:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="data[site_description]" minlength="4" value="<?php echo esc_string(SITE_DESCRIPTION) ?>" required>
						</div>
					</div>
					<div class="mb-3 row">
						<label for="meta_description" class="col-sm-2 col-form-label"><?php _e('Meta description') ?>:</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="data[meta_description]" minlength="4" value="<?php echo esc_string(META_DESCRIPTION) ?>" required>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save changes') ?></button>
				</form>
				<br>
				<form id="form-updatelogo" action="request.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm('form-updatelogo')" >
					<div class="mb-3">
						<input type="hidden" name="action" value="updateLogo">
						<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
						<label for="logo" class="form-label"><?php _e('Site logo') ?>:</label><br>
						<img src="<?php echo DOMAIN . SITE_LOGO .'?v='.date('his') ?>" style="background-color: #aebfbc; padding: 10px"><br><br>
						<input type="file" class="form-control" name="logofile" accept=".png, .jpg, .jpeg, .gif"/>
						<div id="validation-message-form-updatelogo" class="text-danger"></div><br>
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Upload') ?></button>
						<br><br>
					</div>
				</form>
				<form id="form-updateloginlogo" action="request.php" method="post" enctype="multipart/form-data" onsubmit="return validateForm('form-updateloginlogo')">
					<div class="mb-3">
						<input type="hidden" name="action" value="updateLoginLogo">
						<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
						<label for="login-logo" class="form-label"><?php _e('Login logo') ?>:</label><br>
						<img src="<?php echo DOMAIN . 'images/login-logo.png?v='.date('his') ?>" style="background-color: #aebfbc; padding: 10px"><br><br>
						<input type="file" class="form-control" name="logofile" accept=".png"  />
						<div id="validation-message-form-updateloginlogo" class="text-danger"></div><br>
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Upload') ?></button>
						<br><br>
					</div>
				</form>
				<form id="form-updateicon" action="request.php" method="post" enctype="multipart/form-data">
					<div class="mb-3">
						<input type="hidden" name="action" value="updateIcon">
						<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
						<label for="icon" class="form-label"><?php _e('Site icon') ?> (.ico file format):</label><br>
						<img src="<?php echo DOMAIN . 'favicon.ico'.'?v='.date('his') ?>" style="background-color: #aebfbc; padding: 10px; width: 50px;"><br><br>
						<input type="file" class="form-control" name="iconfile" accept=".ico" required /><br>
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Upload') ?></button>
						<br><br>
					</div>
				</form>
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="general">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
					<div class="mb-3 row">
						<label for="code" class="col-sm-3 col-form-label"><?php _e('Site language') ?>:</label>
						<div class="col-sm-9">
							<?php

							$lang_list = ['en'];
							if(file_exists('../locales')){
								$files = scan_files('locales');
								foreach ($files as $file) {
									if(pathinfo($file, PATHINFO_EXTENSION) == 'json'){
										$lang_list[] = pathinfo($file, PATHINFO_FILENAME);
									}
								}
							}
							if(file_exists('../'.TEMPLATE_PATH.'/locales')){
								$files = scan_files(TEMPLATE_PATH.'/locales');
								foreach ($files as $file) {
									if(pathinfo($file, PATHINFO_EXTENSION) == 'json'){
										if(!in_array(pathinfo($file, PATHINFO_FILENAME), $lang_list)){
											$lang_list[] = pathinfo($file, PATHINFO_FILENAME);
										}
									}
								}
							}

							?>
							<select class="form-select" name="data[language]" required>
								<?php
								foreach ($lang_list as $value) {
									$selected = '';
									if($value == get_setting_value('language')){
										$selected = 'selected';
									}
									echo '<option value="'.$value.'" '.$selected.'>'.strtoupper($value).'</option>';
								}
								?>
							</select>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
				<div class="mb-3"></div>
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="updatePurchaseCode">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings">
					<div class="mb-3 row">
						<label for="code" class="col-sm-3 col-form-label"><span class="text-danger">*</span> <?php _e('Item purchase code') ?>:</label>
						<div class="col-sm-9">
							<input type="text" class="form-control" name="code" minlength="5" placeholder="101010-10aa-0101-01010-a1b010a01b10" required>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Update') ?></button>
				</form>
			</div>

			<div class="tab-pane tab-container fade" id="advanced">
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="advanced">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings#advanced">
					<?php

					$group = get_setting_group('advanced');
					foreach ($group as $item) {
						if($item['type'] == 'bool'){
							?>
							<div class="mb-3">
								<input id="<?php echo $item['name'] ?>" type="checkbox" name="data[<?php echo $item['name'] ?>]" value="1" <?php if ((int)$item['value']) { echo 'checked'; } ?>>
								<label for="<?php echo $item['name'] ?>"><?php _e($item['label']) ?></label>
								<?php if($item['tooltip'] != ''){ ?>
									<span class="tooltip-info" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo $item['tooltip'] ?>">
										<i class="fas fa-question"></i>
									</span>
								<?php } ?>
							</div>
							<?php
						}
					}

					?>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
				<div class="mb-3"></div>
				<form action="../sitemap.php" method="post" class="<?php if( !PRETTY_URL ) echo('disabled-list') ?>">
					<div class="mb-3">
						<label><?php _e('Generate sitemap') ?>:</label><br>
						<p>Exclude all page url. only work if Pretty URL enabled.</p>
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Generate sitemap') ?></button>
					</div>
				</form>
			</div>

			<div class="tab-pane tab-container fade" id="user">
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="user">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings#user">
					<?php

					$group = get_setting_group('user');
					foreach ($group as $item) {
						if($item['type'] == 'bool'){
							?>
							<div class="mb-3">
								<input id="<?php echo $item['name'] ?>" type="checkbox" name="data[<?php echo $item['name'] ?>]" value="1" <?php if ((int)$item['value']) { echo 'checked'; } ?>>
								<label for="<?php echo $item['name'] ?>"><?php _e($item['label']) ?></label>
								<?php if($item['tooltip'] != ''){ ?>
									<span class="tooltip-info" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo $item['tooltip'] ?>">
										<i class="fas fa-question"></i>
									</span>
								<?php } ?>
							</div>
							<?php
						}
					}

					?>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
			</div>

			<div class="tab-pane tab-container fade" id="custom-path">
				<p>Custom URL base for page or category name.</p>
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="set_custom_path">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings#custom-path">
					<?php

					$list = ['game','category','page','search','tag','login','register','user','post','full','splash'];
					foreach ($list as $name) {
						?>
						<div class="mb-3 row">
							<label for="<?php echo $name ?>" class="col-sm-2 col-form-label"><?php echo $name ?></label>
							<div class="col-sm-6 col-md-4">
								<input type="text" class="form-control" name="list[]" value="<?php echo (get_custom_path($name) != $name) ? get_custom_path($name) : '' ?>">
							</div>
						</div>
						<?php
					}

					?>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
			</div>

			<div class="tab-pane tab-container fade" id="listings">
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="listings">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings#listings">
					<?php

					$group = get_setting_group('listings');
					foreach ($group as $item) {
						if($item['type'] == 'number'){
							?>
							<div class="mb-3 row">
								<label class="col-sm-3 col-form-label"><?php _e($item['label']) ?></label>
								<div class="col-sm-2">
									<input type="number" class="form-control" name="data[<?php echo $item['name'] ?>]" value="<?php echo esc_int($item['value']) ?>">
								</div>
							</div>
							<?php
						}
					}

					?>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
			</div>

			<div class="tab-pane tab-container fade" id="other">
				<form action="request.php" method="post">
					<input type="hidden" name="action" value="saveSettings">
					<input type="hidden" name="category" value="other">
					<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=settings#other">
					<?php

					$group = get_setting_group('other');
					foreach ($group as $item) {
						if($item['type'] == 'bool'){
							?>
							<div class="mb-3">
								<input id="<?php echo $item['name'] ?>" type="checkbox" name="data[<?php echo $item['name'] ?>]" value="1" <?php if ((int)$item['value']) { echo 'checked'; } ?>>
								<label for="<?php echo $item['name'] ?>"><?php _e($item['label']) ?></label>
								<?php if($item['tooltip'] != ''){ ?>
									<span class="tooltip-info" data-bs-toggle="tooltip" data-bs-placement="right" title="<?php echo $item['tooltip'] ?>">
										<i class="fas fa-question"></i>
									</span>
								<?php } ?>
							</div>
							<?php
						}
					}

					?>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('Save') ?></button>
				</form>
			</div>
		</div>
	</div>
</div>
	<!-- script validation file -->
	<script>
		document.addEventListener('DOMContentLoaded', (event) => {
			let hash = window.location.hash;
			if (hash) {
				let tabEl = document.querySelector(`.nav-link[href="${hash}"]`)
				let tab = new bootstrap.Tab(tabEl)
				tab.show()
			}
		});
		function validateForm(formId) {
			var fileInput = document.getElementById(formId).elements.logofile;
			var validationMessage = document.getElementById('validation-message-' + formId);
			if (!fileInput.value) {
				validationMessage.innerHTML = 'Please select a file.';
				return false;
			}
			return true;
		}
	</script>
<!-- end script validation -->