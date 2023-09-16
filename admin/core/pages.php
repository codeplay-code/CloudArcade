<?php
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        show_alert(isset($_GET['info']) ? $_GET['info'] : 'Page successfully add!', 'success');
    } elseif ($_GET['status'] == 'deleted') {
        show_alert(isset($_GET['info']) ? $_GET['info'] : 'Page removed!', 'danger');
    } elseif ($_GET['status'] == 'update') {
        show_alert(isset($_GET['info']) ? $_GET['info'] : 'Page successfully updated!', 'success');
    }
}
?>

<div class="section section-full">
	<ul class="nav nav-tabs custom-tab" role="tablist">
		<li class="nav-item" role="presentation">
			<a class="nav-link active" data-bs-toggle="tab" href="#pagelist"><?php _e('Pages') ?></a>
		</li>
		<li class="nav-item" role="presentation">
			<a class="nav-link" data-bs-toggle="tab" href="#addpage"><?php _e('Add page') ?></a>
		</li>
	</ul>
	<!-- Tab panes -->
	<div class="tab-content">
		<div class="tab-pane tab-container active" id="pagelist">
			<table class="table custom-table">
				<thead>
				<tr>
					<th>#</th>
					<th><?php _e('ID') ?></th>
					<th><?php _e('Title') ?></th>
					<th><?php _e('Created') ?></th>
					<th><?php _e('Slug') ?></th>
					<th><?php _e('URL') ?></th>
					<th><?php _e('Action') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$results = array();
				$data = Page::getList();
				$results['pages'] = $data['results'];
				$results['totalRows'] = $data['totalRows'];
				$index = 0;
				foreach ( $results['pages'] as $page ) {
				$index++;
				?>
				<tr>
					<th scope="row"><?php echo esc_int($index); ?></th>
					<td>
						<?php echo esc_string($page->id)?>
					</td>
					<td>
						<?php echo esc_string($page->title)?>
					</td>
					<td>
						<?php echo date('j M Y', $page->createdDate) ?>
					</td>
					<td>
						<?php echo esc_string($page->slug)?>
					</td>
					<td><a href="<?php echo get_permalink('page', $page->slug) ?>" target="_blank"><?php _e('Visit') ?></a></td>
					<td><span class="actions"><a class="editpage" href="#" id="<?php echo esc_int($page->id)?>"><i class="fa fa-pencil-alt circle" aria-hidden="true"></i></a><a class="deletepage" href="#" id="<?php echo esc_int($page->id)?>"><i class="fa fa-trash circle" aria-hidden="true"></i></a></span></td>
				</tr>
				<?php }
				?>
			</tbody>
			</table>
			<div class="general-wrapper">
				<p><?php _e('%a pages in total.', esc_int($results['totalRows'])) ?></p>
			</div>
		</div>
		<div class="tab-pane tab-container fade" id="addpage">
			<div class="general-wrapper">
				<form id="form-newpage" method="post">
					<div class="mb-3">
						<label class="form-label" for="title"><?php _e('Page Title') ?>:</label>
						<input type="text" class="form-control" id="newpagetitle" name="title" placeholder="Name of the page" required autofocus maxlength="255" value=""/>
					</div>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Page Slug') ?>:</label>
						<input type="text" class="form-control" id="newpageslug" name="slug" placeholder="Page url ex: this-is-sample-page" required autofocus maxlength="255" value=""/>
					</div>
					<div class="mb-3">
						<label class="form-label" for="content"><?php _e('Content') ?>:</label>
						<textarea class="form-control" name="content" rows="12" placeholder="The HTML content of the page" required maxlength="100000"></textarea>
					</div>
					<div class="mb-3">
						<label class="form-label" for="title"><?php _e('Created Date') ?>:</label>
						<input type="date" class="form-control" name="createdDate" placeholder="YYYY-MM-DD" required maxlength="10" value="<?php echo date( "Y-m-d" ) ?>" />
					</div>
					<input type="submit" class="btn btn-primary"  name="saveChanges" value="<?php _e('Publish') ?>" />
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="edit-page" tabindex="-1" role="dialog" aria-labelledby="edit-page-modal-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
	<div class="modal-content">
		<div class="modal-header">
		<h5 class="modal-title" id="edit-page-label"><?php _e('Edit page') ?></h5>
		<button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body">
		<form id="form-editpage">
			<input type="hidden" id="edit-id" name="id" value=""/>
			<input type="hidden" id="edit-createdDate" name="createdDate" value=""/>
			<div class="mb-3">
				<label class="form-label" for="title"><?php _e('Page Title') ?>:</label>
				<input type="text" class="form-control" id="edit-title" name="title" placeholder="Name of the page" required minlength="3" maxlength="255" value=""/>
			</div>
			<div class="mb-3">
				<label class="form-label" for="slug"><?php _e('Page Slug') ?>:</label>
				<input type="text" class="form-control" id="edit-slug" name="slug" placeholder="Page url ex: this-is-sample-page" required minlength="3" maxlength="255" value=""/>
			</div>
			<div class="mb-3">
				<label class="form-label" for="content"><?php _e('Content') ?>:</label>
				<textarea class="form-control" name="content" id="edit-content" rows="12" placeholder="The HTML content of the page" required minlength="3" maxlength="100000"></textarea>
			</div>
			<input type="submit" class="btn btn-primary" value="<?php _e('Save changes') ?>" />
			<input type="button" class="btn btn-secondary" data-bs-dismiss="modal" value="<?php _e('Close') ?>" />
		</form>
		</div>
	</div>
	</div>
</div>