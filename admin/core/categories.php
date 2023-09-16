<?php
if(isset($_POST['action'])){
	if($_POST['action'] == 'reset-priority'){
		$cats = Category::getList()['results'];
		foreach ($cats as $cat) {
			$cat->priority = 0;
			$cat->update();
		}
		$_GET['status'] = 'reset';
	}
}
	if(isset($_GET['status'])){
		$class = 'success';
		$message = '';
		if($_GET['status'] == 'added'){
			$message = 'New category added!';
		} elseif($_GET['status'] == 'exist'){
			$class = 'warning';
			$message = 'Category already exist!';
		} elseif($_GET['status'] == 'deleted'){
			$class = 'warning';
			$message = 'Category deleted!';
		} elseif($_GET['status'] == 'updated'){
			$message = 'Category updated!';
			if(isset($_GET['info'])){
				$message = $message.' '.$_GET['info'];
			}
		} elseif($_GET['status'] == 'reset'){
			$message = 'Category priority set to 0!';
			if(isset($_GET['info'])){
				$message = $message.' '.$_GET['info'];
			}
		}
		show_alert($message, $class);
	}
?>
<div class="row">
	<div class="col-lg-8">
		<div class="section">
			<ul class="category-list">
				<?php
				$results = array();

				$data = Category::getList();
				$categories = $data['results'];

				if($data['totalRows'] > 0){
					foreach ($categories as $cat) {
						echo '<li class="category-item d-flex align-items-center">';
						if($cat->priority<0){
							echo '<span style="opacity: 0.3;">'.esc_string($cat->name).'</span>';
						}
						else{
							echo esc_string($cat->name);
						}
						$count = Category::getCategoryCount($cat->id);
						if($count > 0){
							echo '<span class="badge badge-primary badge-pill">';
							echo esc_int($count);
							echo '</span>';
						}
						echo '<div style="margin-left: auto;">';
						echo '<span class="actions"><a class="editcategory" href="#" id="'.esc_int($cat->id).'"><i class="fa fa-pencil-alt circle" aria-hidden="true"></i></a><a class="remove-category text-danger" href="#" id="'.esc_int($cat->id).'"><i class="fa fa-trash circle" aria-hidden="true"></i></a></span>';
						echo '</div></li>';
					}
				} else {
					_e('No categories found!');
				}

				?>
			</ul>
			<?php
			if(count($categories) > 0){
				?>
				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="reset-priority">
					<div class="mb-3">
						<button type="submit" class="btn btn-primary btn-md"><?php _e('Reset Priority') ?></button>
					</div>
				</form>
				<?php
			}
			?>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="section">
			<form id="form-newcategory" action="request.php" method="post">
				<input type="hidden" name="action" value="newCategory">
				<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=categories">
				<div class="mb-3">
					<label class="form-label" for="category"><?php _e('Add new category') ?>:</label>
					<input type="text" class="form-control" name="name" placeholder="Name" value="" minlength="2" maxlength="15" required>
				</div>
				<div class="mb-3">
					<label class="form-label" for="description"><?php _e('Description') ?>:</label>
					<textarea type="text" class="form-control" name="description" rows="3" placeholder="(Optional) Category description"></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="meta_description"><?php _e('Meta Description') ?>:</label>
					<textarea class="form-control" name="meta_description" rows="3" placeholder="(Optional) Category meta description"></textarea>
				</div>
				<?php
					if(CUSTOM_SLUG){ ?>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Category Slug') ?>:</label>
						<input type="text" class="form-control" name="slug" placeholder="adventure-game" value="" minlength="3" maxlength="15" required>
					</div>
					<?php }
				?>
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Add') ?></button>
			</form>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="edit-category" tabindex="-1" role="dialog" aria-labelledby="edit-category-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
	<div class="modal-content">
	  <div class="modal-header">
		<h5 class="modal-title" id="edit-category-label"><?php _e('Edit category') ?></h5>
		<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
	  </div>
	  <div class="modal-body">
		<form id="form-editcategory" action="request.php" method="post">
			<input type="hidden" name="action" value="editCategory">
			<input type="hidden" name="redirect" value="<?php echo DOMAIN ?>admin/dashboard.php?viewpage=categories">
			<input type="hidden" id="edit-id" name="id" value=""/>
			<div class="mb-3">
				<label class="form-label" for="title"><?php _e('Category Name') ?>:</label>
				<?php show_alert('Change category name will update all related games category string.', 'warning') ?>
				<input type="text" class="form-control" id="edit-name" name="name" placeholder="Name of the game" required minlength="2" maxlength="255" value=""/>
			</div>
			<div class="mb-3">
				<label class="form-label" for="slug"><?php _e('Category Slug') ?>:</label>
				<input type="text" class="form-control" id="edit-slug" name="slug" placeholder="online-games" required minlength="2" maxlength="255" value=""/>
			</div>
			<div class="mb-3">
				<label class="form-label" for="description"><?php _e('Description') ?>:</label>
				<textarea class="form-control" name="description" id="edit-description" rows="3" placeholder="(Optional) Category description" minlength="3" maxlength="100000"></textarea>
			</div>
			<div class="mb-3">
				<label class="form-label" for="meta_description"><?php _e('Meta Description') ?>:</label>
				<textarea class="form-control" name="meta_description" id="edit-meta_description" rows="3" placeholder="(Optional) Category meta description" minlength="3" maxlength="100000"></textarea>
			</div>
			<div class="mb-3">
				<label class="form-label" for="edit-priority"><?php _e('Priority') ?>:</label>
				<input type="number" class="form-control" id="edit-priority" name="priority" value="0" />
			</div>
			<div class="mb-3">
				<label class="form-label" for="cat-id"><?php _e('ID') ?>:</label>
				<input type="text" class="form-control" id="cat-id" name="cat-id" value="" disabled />
			</div>
			<div class="mb-3">
			    <input id="edit-hide" class="edit-hide" name="hide" type="checkbox" >
			    <label class="form-label" for="edit-hide"><?php _e('Hide') ?></label><br>
			</div>
			<input type="submit" class="btn btn-primary" value="<?php _e('Save changes') ?>" />
			<input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="<?php _e('Close') ?>" />
		</form>
	  </div>
	</div>
  </div>
</div>