<?php

//include '../' .TEMPLATE_PATH . '/layout.php';

if(isset($_POST['menu_data'])){
	if(USER_ADMIN && !ADMIN_DEMO){
		$array_menu = json_decode($_POST['menu_data'], true);
		$sql = "TRUNCATE TABLE menus";
		$st = $conn->prepare($sql);
		$st->execute();
		update_menu($array_menu);
		show_alert('Menu saved!', 'success');
	}
}

function update_menu($menu,$parent = 0)
{
	global $conn;
	if (!empty($menu)) {
		foreach ($menu as $value) {
			$label = $value['label'];
			$name = 'top_nav';
			$url = (empty($value['url'])) ? '#' : $value['url'];
			$sql = "INSERT INTO menus (label, url, parent_id, name) VALUES (:label, :url, :parent, :name)";
			$st = $conn->prepare($sql);
			$st->bindValue(':label', $label, PDO::PARAM_STR);
			$st->bindValue(':url', $url, PDO::PARAM_STR);
			$st->bindValue(':name', $name, PDO::PARAM_STR);
			$st->bindValue(':parent', $parent, PDO::PARAM_INT);
			$st->execute();
			$id = $conn->lastInsertId();
			if (array_key_exists('children', $value))
				update_menu($value['children'],$id);
		}
	}
}

function render_menu_item($id, $label, $url)
{
	return '<li class="dd-item dd3-item" data-id="' . $id . '" data-label="' . $label . '" data-url="' . $url . '">' .
		'<div class="dd-handle dd3-handle" > Drag</div>' .
		'<div class="dd3-content"><span>' . $label . '</span>' .
		'<div class="item-edit"><i class="fa fa-pencil-alt" aria-hidden="true"></i></div>' .
		'</div>' .
		'<div class="item-settings d-none">' .
		'<div class="mb-3">' .
		'<label>Name</label><input type="text" class="form-control" name="navigation_label" value="' . $label . '">' .
		'</div>' .
		'<div class="mb-3">' .
		'<label>URL</label><input type="text" class="form-control" name="navigation_url" value="' . $url . '">' .
		'</div>' .
		'<p><a class="item-delete" href="javascript:;">Remove</a> | ' .
		'<a class="item-close" href="javascript:;">Close</a></p>' .
		'</div>';

}

function menu_tree($parent_id = 0)
{
	global $conn;
	$items = '';
	$sql = "SELECT * FROM menus WHERE parent_id = :parent_id ORDER BY id ASC";
	$st = $conn->prepare($sql);
	$st->bindValue(":parent_id", $parent_id, PDO::PARAM_INT);
	$st->execute();
	$result = $st->fetchAll();
	if (count($result)) {
		$items .= '<ol class="dd-list">';
		foreach ($result as $row) {
			$items .= render_menu_item($row['id'], $row['label'], $row['url']);
			$items .= menu_tree($row['id']);
			$items .= '</li>';
		}
		$items .= '</ol>';
	}
	return $items;
}

?>
<?php
	if(isset($_GET['status'])){
		$type = 'success';
		$message = '';
		if($_GET['status'] == 'saved'){
			$message = 'Layout saved!';
		}
		show_alert($message, $type);
	}
?>
<div class="row">
	<div class="col-lg-8">
		<div class="section section-full">
			<ul class="nav nav-tabs custom-tab" role="tablist">
				<?php
				foreach($tab_list as $tab => $label){
					$active = '';
					if($tab == $slug){
						$active = 'active';
					}
					?>
					<li class="nav-item" role="presentation">
						<a class="nav-link <?php echo $active ?>" href="dashboard.php?viewpage=layout&slug=<?php echo $tab ?>"><?php _e($label) ?></a>
					</li>
					<?php
				}
				?>
			</ul>
			<div class="general-wrapper">
				<div class="mb-4"></div>
				<form id="add-item">
					<div class="form-row">
						<div class="mb-3 col-md-6">
							<input type="text" name="name" class="form-control" placeholder="<?php _e('Name') ?>" required>
						</div>
						<div class="mb-3 col-md-6">
							<input type="text" name="url" class="form-control" placeholder="<?php _e('URL') ?>" required>
						</div>
					</div>
					<button type="submit" class="btn btn-primary btn-md"><?php _e('ADD MENU') ?></button>
				</form>
				<hr />
				<div class="dd" id="nestable">
					<?php
						$html_menu = menu_tree();
						echo (empty($html_menu)) ? '<ol class="dd-list"></ol>' : $html_menu;
					?>
				</div>
				<hr />
				<form action="dashboard.php?viewpage=layout" method="post">
					<input type="hidden" id="nestable-output" name="menu_data">
					<button type="submit" class="btn btn-primary btn-md"><?php _e('SAVE MENU') ?></button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="section">
			<p><?php _e('Add menu items') ?></p>
			<div class="accordion" id="accordion-container">
				<div class="card">
					<div class="card-header" id="acc-head1">
						<a href="#" class="btn btn-header-link collapsed" data-bs-toggle="collapse" data-bs-target="#acc1"
						aria-expanded="true" aria-controls="acc1"><?php _e('Pages') ?></a>
					</div>
					<div id="acc1" class="collapse" aria-labelledby="acc-head1" data-bs-parent="#accordion-container">
						<div class="card-body">
							<form id="form-page-menu">
								<?php

								$data = Page::getList();
								$pages = $data['results'];

								if($pages){
									echo '<div class="ml-3">';
									foreach ($pages as $page) {
										echo '<div class="form-check">';
										echo '<input class="form-check-input" type="checkbox" name="'.$page->title.'" value="'.$page->slug.'" id="item-'.$page->slug.'" data-url="/'.SUB_FOLDER.str_replace( DOMAIN, '', get_permalink('page', $page->slug)).'">';
										echo '<label class="form-check-label" for="item-'.$page->slug.'">';
										echo $page->title;
										echo '</label></div>';
									}
									echo '</div><br>';
									echo '<input type="submit" class="btn btn-info btn-md" value="'. _t('ADD TO MENU') .'">';
								} else {
									_e('Empty');
								}
								?>
							</form>
						</div>
					</div>
				</div>
				<div class="card">
					<div class="card-header" id="acc-head2">
						<a href="#" class="btn btn-header-link collapsed" data-bs-toggle="collapse" data-bs-target="#acc2"
						aria-expanded="true" aria-controls="acc2"><?php _e('Categories') ?></a>
					</div>
					<div id="acc2" class="collapse" aria-labelledby="acc-head2" data-bs-parent="#accordion-container">
						<div class="card-body">
							<form id="form-category-menu">
								<?php

								$data = Category::getList();
								$categories = $data['results'];

								if($categories){
									echo '<div class="ml-3">';
									foreach ($categories as $category) {
										echo '<div class="form-check">';
										echo '<input class="form-check-input" name="'.$category->name.'" type="checkbox" value="'.$category->slug.'" id="item-'.$category->slug.'" data-url="/'.SUB_FOLDER.str_replace( DOMAIN, '', get_permalink('category', $category->slug)).'">';
										echo '<label class="form-check-label" for="item-'.$category->slug.'">';
										echo $category->name;
										echo '</label></div>';
									}
									echo '</div><br>';
									echo '<input type="submit" class="btn btn-info btn-md" value="'. _t('ADD TO MENU') .'">';
								} else {
									_e('Empty');
								}
								?>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>