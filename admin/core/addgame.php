<div class="section section-full">

<?php

if(!check_purchase_code() && !ADMIN_DEMO){
	echo('<div class="bs-callout bs-callout-warning"><p>Please provide your <b>Item Purchase code</b>. You can submit or update your Purchase code on site settings.</p><p>To be able to add a game, you need to provide your Item Purchase code. <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code" target="_blank">Where to get Envato purchase code?</a></p></div>');
} else {

?>
<input type="hidden" name="p_code" value="<?php echo (ADMIN_DEMO ? 'holy-moly' : check_purchase_code()) ?>" id="p_code" />
<ul class="nav nav-tabs custom-tab" role="tablist">
	<li class="nav-item" role="presentation">
		<a class="nav-link <?php echo (!isset($_GET['slug'])) ? 'active' : ''; ?>" data-bs-toggle="tab" href="#addgame"><?php _e('Upload game') ?></a>
	</li>
	<li class="nav-item" role="presentation">
		<a class="nav-link" data-bs-toggle="tab" href="#fetch"><?php _e('Fetch games') ?></a>
	</li>
	<li class="nav-item" role="presentation">
		<a class="nav-link <?php echo (isset($_GET['slug']) && $_GET['slug'] == 'remote') ? 'active' : ''; ?>" data-bs-toggle="tab" href="#remote"><?php _e('Remote add') ?></a>
	</li>
	<li class="nav-item" role="presentation">
		<a class="nav-link" data-bs-toggle="tab" href="#json"><?php _e('JSON Importer') ?></a>
	</li>
</ul>
	
<!-- Tab panes -->
<div class="general-wrapper">
	<div class="tab-content">
		<?php
		$selected_categories = []; //Used for showing last selected categories
		if(isset($_SESSION['category'])){
			if(is_array($_SESSION['category'])){
				$selected_categories = (array)$_SESSION['category'];
			} else {
				$selected_categories = commas_to_array($_SESSION['category']);
			}
		}
		if(isset($_GET['status'])){
			echo '<div class="mb-4"></div>';
			if($_GET['status'] == 'added'){
				show_alert('Game added!', 'success');
			} elseif($_GET['status'] == 'exist'){
				show_alert('Game already exist!', 'warning');
			} elseif($_GET['status'] == 'error'){
				$error = json_decode($_GET['error-data']);
				foreach ($error as $value) {
					show_alert($value, 'warning');
				}
			}
		}
		?>
		<div class="tab-pane tab-container <?php echo (!isset($_GET['slug'])) ? 'active' : 'fade'; ?>" id="addgame">
			<form id="form-uploadgame" action="upload.php" enctype="multipart/form-data" autocomplete="off" method="post">
				<input type="hidden" name="source" value="self"/>
				<input type="hidden" name="tags" value=""/>
				<div class="mb-3">
					<label class="form-label" for="title"><?php _e('Game title') ?>:</label>
					<input type="text" class="form-control" name="title" value="<?php echo (isset($_SESSION['title'])) ? $_SESSION['title'] : "" ?>" id="game-title-upload" required/>
				</div>
				<?php
					if(CUSTOM_SLUG){ ?>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Game slug') ?>:</label>
						<input type="text" class="form-control" name="slug" placeholder="game-title" value="<?php echo (isset($_SESSION['slug'])) ? $_SESSION['slug'] : "" ?>" minlength="3" maxlength="50" id="game-slug-upload" required>
					</div>
					<?php }
				?>
				<div class="mb-3">
					<label class="form-label" for="description"><?php _e('Description') ?>:</label>
					<textarea class="form-control" name="description" rows="3" required><?php echo (isset($_SESSION['description'])) ? $_SESSION['description'] : "" ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="instructions"><?php _e('Instructions') ?>:</label>
					<textarea class="form-control" name="instructions" rows="3"><?php echo (isset($_SESSION['instructions'])) ? $_SESSION['instructions'] : "" ?></textarea>
				</div>
				<label class="form-label" for="gamefile"><?php _e('Game file') ?> (.zip):</label>
				<ul>
					<li>Must contain index.html on root</li>
					<li>Must contain "thumb_1.jpg" (512x384px) on root</li>
					<li>Must contain "thumb_2.jpg"(512x512px) on root</li>
				</ul>
				<div class="input-group mb-3">
					<div class="custom-file">
						<input type="file" name="gamefile" class="custom-file-input" id="input_gamefile" accept=".zip" required>
						<label class="form-label" class="custom-file-label" for="input_gamefile">Choose file</label>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label" for="width"><?php _e('Game width') ?>:</label>
					<input type="number" class="form-control" name="width" value="<?php echo (isset($_SESSION['width'])) ? $_SESSION['width'] : "720" ?>" required/>
				</div>
				<div class="mb-3">
					<label class="form-label" for="height"><?php _e('Game height') ?>:</label>
					<input type="number" class="form-control" name="height" value="<?php echo (isset($_SESSION['height'])) ? $_SESSION['height'] : "1080" ?>" required/>
				</div>
				<div class="mb-3">
					<label class="form-label" for="category"><?php _e('Category') ?>:</label>
					<select multiple class="form-control" name="category[]" size="8" required/>
						<?php
							$results = array();
							$data = Category::getList();
							$categories = $data['results'];
							foreach ($categories as $cat) {
								$selected = (in_array($cat->name, $selected_categories)) ? 'selected' : '';
								echo '<option '.$selected.'>'.$cat->name.'</option>';
							}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label" for="tags"><?php _e('Tags') ?>:</label>
					<input type="text" class="form-control" name="tags" value="<?php echo (isset($_SESSION['tags'])) ? $_SESSION['tags'] : "" ?>" id="tags-upload" placeholder="<?php _e('Separated by comma') ?>">
				</div>
				<div class="tag-list">
					<?php
					$tag_list = get_tags('usage');
					if(count($tag_list)){
						echo '<div class="mb-3">';
						foreach ($tag_list as $tag_name) {
							echo '<span class="badge rounded-pill bg-secondary btn-tag" data-target="tags-upload" data-value="'.$tag_name.'">'.$tag_name.'</span>';
						}
						echo '</div>';
					}
					?>
				</div>
				<div class="mb-3">
					<input id="published" type="checkbox" name="published" <?php echo (isset($_SESSION['published']) ? filter_var($_SESSION['published'], FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
					<label class="form-label" for="published"><?php _e('Published') ?></label><br>
					<p style="margin-left: 20px;" class="text-secondary">
						<?php _e('If unchecked, this game will set as Draft.') ?>
					</p>
				</div>
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Upload game') ?></button>
			</form>
		</div>
		<div class="tab-pane tab-container fade" id="fetch">
			<div class="mb-3">
				<label class="form-label"><?php _e('Distributor') ?></label> 
				<select name="distributor" class="form-control" id="distributor-options">
					<option value="" disabled selected hidden><?php _e('Choose game distributor') ?>...</option>
					<option value="#gamedistribution">GameDistribution</option>
					<option value="#gamepix">GamePix</option>
					<option value="#playsaurus">Playsaurus</option>
					<option value="#more-distributors">More</option>
				</select>
			</div>
			<div class="fetch-games tab-container fade" id="gamedistribution">
				<div class="alert alert-warning alert-dismissible fade show" role="alert">You need joined <a href="https://gamedistribution.com/publishers" target="_blank">GameDistribution</a> publisher program to be able to publish their games on your site.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>

				<form id="form-fetch-gamedistribution" class="gamedistribution">
					<div class="mb-3">
						<label class="form-label">Collection</label> 
						<select name="Collection" class="form-control">
							<option selected="selected" value="all">All</option>
							<option value="11">Top Hypercasual</option>
							<option value="8">Ubisoft</option>
							<option value="3">Hot</option>
							<option value="2">Exclusive</option>
							<option value="1">Top Picks</option>
							<option value="4">New</option>
							<option value="5">In Game Purchase</option>
							<option value="6">IceStone</option>
							<option value="7">Ubisoft</option>
							<option value="10">Gameloft</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Category</label> 
						<select name="Category" class="form-control">
							<option selected="selected" value="All">All</option>
							<option value=".IO">.IO</option>
							<option value="2 Player">2 Player</option>
							<option value="3D">3D</option>
							<option value="Action">Action</option>
							<option value="Adventure">Adventure</option>
							<option value="Arcade">Arcade</option>
							<option value="Baby">Baby</option>
							<option value="Bejeweled">Bejeweled</option>
							<option value="Boys">Boys</option>
							<option value="Clicker">Clicker</option>
							<option value="Cooking">Cooking</option>
							<option value="Farming">Farming</option>
							<option value="Girls">Girls</option>
							<option value="Hypercasual">Hypercasual</option>
							<option value="Multiplayer">Multiplayer</option>
							<option value="Puzzle">Puzzle</option>
							<option value="Racing">Racing</option>
							<option value="Shooting">Shooting</option>
							<option value="Soccer">Soccer</option>
							<option value="Social">Social</option>
							<option value="Sports">Sports</option>
							<option value="Stickman">Stickman</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Item</label> 
						<select name="Limit" class="form-control">
							<option selected="selected" value="10">10</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option value="40">40</option>
							<option value="70">70</option>
							<option value="100">100</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Offset</label> 
						<select name="Offset" class="form-control">
							<option selected="selected" value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
						</select>
					</div>
					<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
				</form>
			</div>
			<div class="fetch-games tab-container fade" id="gamepix">
				<div class="alert alert-warning alert-dismissible fade show" role="alert">You need joined <a href="https://company.gamepix.com/publishers/" target="_blank">GamePix</a> publisher program to be able to publish their games on your site.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
				<form id="form-fetch-gamepix" class="gamepix">
					<div class="mb-3">
						<label class="form-label">Sort By</label> 
						<select name="Sort" class="form-control">
							<option value="d" selected>Newest</option>
							<option value="q">Most Played</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Category</label> 
						<select name="Category" class="form-control">
							<option value="1">All</option>
							<option value="2">Arcade</option>
							<option value="3">Adventure</option>
							<option value="4">Junior</option>
							<option value="5">Board</option>
							<option value="6">Classic</option>
							<option value="7">Puzzle</option>
							<option value="8">Sports</option>
							<option value="9">Strategy</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Item</label> 
						<select name="Limit" class="form-control">
							<option selected="selected" value="10">10</option>
							<option value="20">20</option>
							<option value="30">30</option>
							<option value="40">40</option>
							<option value="70">70</option>
							<option value="100">100</option>
						</select>
					</div>
					<div class="mb-3">
						<label class="form-label">Offset</label> 
						<select name="Offset" class="form-control">
							<option selected="selected" value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
							<option value="11">11</option>
							<option value="12">12</option>
							<option value="13">13</option>
							<option value="14">14</option>
							<option value="15">15</option>
						</select>
					</div>
					<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
				</form>
			</div>
			<div class="fetch-games tab-container fade" id="playsaurus">
				<form id="form-fetch-playsaurus" class="playsaurus">
					<div class="mb-3">
						<label class="form-label">Item</label> 
						<select name="Limit" class="form-control">
							<option selected="selected" value="100">All</option>
						</select>
					</div>
					<input type="submit" class="btn btn-primary btn-md" value="<?php _e('Fetch games') ?>"/>
				</form>
			</div>
			<div class="fetch-games tab-container fade" id="more-distributors">
				<p><b>You can fetch or add game from other HTML5 game distributors with "Fetch Games Extended" plugin.</b></p>
				<p>If "Fetch Games Extended" plugin not installed. follow step below:</p>
				<p>
					Click "Plugin" tab (Left sidebar) > Manage Plugins > Load Plugin Repository > ( Add ) Fetch Games Extended.
				</p>
				<p>
					Then you can access it under plugin page.
				</p>

			</div>
			<br>
			<div class="fetch-loading" style="display: none;">
				<h4><?php _e('Fetching games') ?> ...</h4>
			</div>
			<div id="action-info"></div>
			<div class="fetch-list mb-3" style="display: none;">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th>#</th>
								<th><?php _e('Thumbnail') ?></th>
								<th><?php _e('Game name') ?></th>
								<th><?php _e('Category') ?></th>
								<th><?php _e('URL') ?></th>
								<th><?php _e('Action') ?></th>
							</tr>
						</thead>
						<tbody id="gameList">
						</tbody>
					</table>
				</div>
				<button class="btn btn-primary btn-md" id="add-all"><?php _e('Add all') ?></button>
			</div>
			<div class="div-stop" style="display: none;">
				<button class="btn btn-danger btn-md" id="stop-add"><?php _e('Stop') ?></button>
			</div>
		</div>
		<div class="tab-pane tab-container <?php echo (isset($_GET['slug']) && $_GET['slug'] == 'remote') ? 'active' : 'fade'; ?>" id="remote">
			<form id="form-remote" action="request.php" autocomplete="off" method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="addGame"/>
				<input type="hidden" name="source" value="remote"/>
				<input type="hidden" name="redirect" value="dashboard.php?viewpage=addgame&slug=remote">
				<input type="hidden" name="tags" value=""/>
				<div class="mb-3">
					<label class="form-label" for="title"><?php _e('Game title') ?>:</label>
					<input type="text" class="form-control" name="title" value="<?php echo (isset($_SESSION['title'])) ? $_SESSION['title'] : "" ?>" id="game-title-remote" required />
				</div>
				<?php
					if(CUSTOM_SLUG){ ?>
					<div class="mb-3">
						<label class="form-label" for="slug"><?php _e('Game slug') ?>:</label>
						<input type="text" class="form-control" name="slug" placeholder="game-title" value="<?php echo (isset($_SESSION['slug'])) ? $_SESSION['slug'] : "" ?>" minlength="3" maxlength="50" id="game-slug-remote" required>
					</div>
					<?php }
				?>
				<div class="mb-3">
					<label class="form-label" for="description"><?php _e('Description') ?>:</label>
					<textarea class="form-control" name="description" rows="3" required><?php echo (isset($_SESSION['description'])) ? $_SESSION['description'] : "" ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="instructions"><?php _e('Instructions') ?>:</label>
					<textarea class="form-control" name="instructions" rows="3"><?php echo (isset($_SESSION['instructions'])) ? $_SESSION['instructions'] : "" ?></textarea>
				</div>
				<div class="mb-3">
					<label class="form-label" for="thumb_1"><?php _e('Thumbnail') ?> 512x384:</label>
					<input type="text" class="form-control" name="thumb_1" placeholder="https://example.com/yourgames/thumb_1.jpg" value="<?php echo (isset($_SESSION['thumb_1'])) ? $_SESSION['thumb_1'] : "" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="thumb_2"><?php _e('Thumbnail') ?> 512x512:</label>
					<input type="text" class="form-control" name="thumb_2" placeholder="https://example.com/yourgames/thumb_2.jpg" value="<?php echo (isset($_SESSION['thumb_2'])) ? $_SESSION['thumb_2'] : "" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="url"><?php _e('Game URL') ?>:</label>
					<input type="text" class="form-control" name="url" value="<?php echo (isset($_SESSION['url'])) ? $_SESSION['url'] : "" ?>" placeholder="https://example.com/yourgames/index.html" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="width"><?php _e('Game width') ?>:</label>
					<input type="number" class="form-control" name="width" value="<?php echo (isset($_SESSION['width'])) ? $_SESSION['width'] : "720" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="height"><?php _e('Game height') ?>:</label>
					<input type="number" class="form-control" name="height" value="<?php echo (isset($_SESSION['height'])) ? $_SESSION['height'] : "1080" ?>" required />
				</div>
				<div class="mb-3">
					<label class="form-label" for="category"><?php _e('Category') ?>:</label>
					<select multiple class="form-control" name="category[]" size="8" required />
						<?php
							$results = array();
							$data = Category::getList();
							$categories = $data['results'];
							foreach ($categories as $cat) {
								$selected = (in_array($cat->name, $selected_categories)) ? 'selected' : '';
								echo '<option '.$selected.'>'.$cat->name.'</option>';
							}
						?>
					</select>
				</div>
				<div class="mb-3">
					<label class="form-label" for="tags"><?php _e('Tags') ?>:</label>
					<input type="text" class="form-control" name="tags" value="<?php echo (isset($_SESSION['tags'])) ? $_SESSION['tags'] : "" ?>" id="tags-remote" placeholder="<?php _e('Separated by comma') ?>">
				</div>
				<div class="tag-list">
					<?php
					$tag_list = get_tags('usage');
					if(count($tag_list)){
						echo '<div class="mb-3">';
						foreach ($tag_list as $tag_name) {
							echo '<span class="badge rounded-pill bg-secondary btn-tag" data-target="tags-remote" data-value="'.$tag_name.'">'.$tag_name.'</span>';
						}
						echo '</div>';
					}
					?>
				</div>
				<div class="mb-3">
					<input id="published" type="checkbox" name="published" <?php echo (isset($_SESSION['published']) ? filter_var($_SESSION['published'], FILTER_VALIDATE_BOOLEAN) : true) ? 'checked' : ''; ?>>
					<label class="form-label" for="published"><?php _e('Published') ?></label><br>
					<p style="margin-left: 20px;" class="text-secondary">
						<?php _e('If unchecked, this game will set as Draft.') ?>
					</p>
				</div>
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Add game') ?></button>
			</form>
		</div>
		<div class="tab-pane tab-container fade" id="json">
			<p>Bulk import your game data with JSON format.</p>
			<p>Read "User Documentation" for sample JSON structure or code.</p>
			<p>Open browser log to see the import progress.</p>
			<p>Paste your JSON data below.</p>
			<form id="form-json">
				<div class="mb-3">
					<label class="form-label" for="json-importer">JSON data:</label>
					<textarea class="form-control" name="json-importer" rows="8" required /></textarea>
				</div>
				<button type="submit" class="btn btn-primary btn-md"><?php _e('Import') ?></button>
			</form>
			<br>
			<p>Preview JSON data (Game list) before submited.</p>
			<button class="btn btn-primary btn-md" id="json-preview"><?php _e('Preview') ?></button>
			<br><br>
			<table class="table" style="display: none;" id="table-json-preview">
				<thead>
					<tr>
						<th>#</th>
						<th><?php _e('Title') ?></th>
						<th><?php _e('Slug') ?></th>
						<th><?php _e('URL') ?></th>
						<th><?php _e('Width') ?></th>
						<th><?php _e('Height') ?></th>
						<th><?php _e('Thumb') ?> 1</th>
						<th><?php _e('Thumb') ?> 2</th>
						<th><?php _e('Category') ?></th>
						<th><?php _e('Source') ?></th>
					</tr>
				</thead>
				<tbody id="json-list-preview">
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php } ?>
</div>