<?php

$widget_data = get_pref('widgets') ?: "[]";
$stored_widgets = json_decode($widget_data, true);

require_once( '../' . TEMPLATE_PATH . '/functions.php' );

if(isset($stored_widgets['head']) && empty($stored_widgets['head'])){
	$stored_widgets['head'] = json_decode('[{"id":"html","widget":"Widget_HTML","text":""}]', true);
	update_option('widgets', json_encode($stored_widgets));
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
				<p><?php _e('Placement') ?>:</p>
				<p>
					<?php
					if(empty($registered_sidebars)){
						_e('There is no widget placement on your current theme!');
					} ?>
				</p>
				<div id="panel-area">
					<?php

					if(count($registered_sidebars)){
						$count = 0;
						foreach ($registered_sidebars as $item) {
							?>

							<div class="panel panel-default panel-section" id="widget-panel" data-id="<?php echo $item['id'] ?>">
								<div class="panel-heading">
									<div class="panel-title" data-bs-toggle="collapse" data-bs-target="#<?php echo $item['id'] ?>"><?php echo $item['name'] ?></div>
								</div>
								<div id="<?php echo $item['id'] ?>" class="panel-collapse collapse">
									<div class="panel-description small">
										<?php echo $item['description'] ?>
									</div>
									<div class="panel-body">
										<?php
										if(isset($stored_widgets[$item['id']])){
											$list = $stored_widgets[$item['id']];
											$index = 0;
											foreach ($list as $item) {
												$count++;
												$key = $item['widget'];
												$widget;
												$missing = false;
												$inactive_class = '';
												if(widget_exists($item['widget'])){
													$widget = get_widget( $item['widget'], $item );
												} else {
													$widget = new Class {
														public $name;
														public $id_base;
														public function form($e = 0){
															echo 'This widget is missing or inactive.';
														}
													};
													$widget->name = $item['widget'];
													$widget->id_base = $item['id'];
													$missing = true;
													$inactive_class = 'widget-inactive';
												}
												
												?>

												<div class="widget-item-sortable">
													<div class="widget-item <?php echo $inactive_class ?>" data-bs-toggle="collapse" data-bs-target="#<?php echo 'ID_'.$count ?>">
														<div class="widget-title"><?php echo $widget->name ?></div>
													</div>
													<div id="<?php echo 'ID_'.$count ?>" class="item-panel-collapse collapse">
														<div class="widget-form">
															<form method="post">
																<input type="hidden" name="id" value="<?php echo $widget->id_base ?>">
																<input type="hidden" name="widget" value="<?php echo $key ?>">
																<?php $widget->form( $item ); ?>
																<div class="widget-control-actions">
																	<div class="float-left widget-action-button-area">
																		<span class="text-danger delete-widget"><?php _e('Delete') ?></span></span>
																	</div>
																	<?php if(!$missing){ ?>
																	<div class="float-right">
																		<button class="btn btn-primary btn-sm btn-save"><?php _e('Save') ?></button>
																	</div>
																	<?php } ?>
																	<div class="clearfix"></div>
																</div>
															</form>
														</div>
													</div>
												</div>

												<?php
												$index++;
											}
										}

										?>
									</div>
								</div>
							</div>

							<?php
						}
					}

					?>	
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-4">
		<div class="section" id="available-widgets">
			<p><?php _e('Available Widgets') ?>:</p>
			<div class="widget-list">
				<?php

				if(count($widget_factory->widgets)){
					foreach ($widget_factory->widgets as $key => $widget) {
						?>

						<div class="widget-block">
							<div class="widget-item-sortable">
								<div class="widget-item">
									<div class="widget-title"><?php echo $widget->name ?></div>
									<div class="d-none widget-inside">
										<div class="widget-item-sortable">
											<div class="widget-item" data-bs-toggle="collapse" data-bs-target="#ID_TO_REPLACE">
												<div class="widget-title"><?php echo $widget->name ?></div>
											</div>
											<div id="ID_TO_REPLACE" class="item-panel-collapse collapse">
												<div class="widget-form">
													<form method="post">
														<input type="hidden" name="id" value="<?php echo $widget->id_base ?>">
														<input type="hidden" name="widget" value="<?php echo $key ?>">
														<?php $widget->form(); ?>
														<div class="widget-control-actions">
															<div class="float-left widget-action-button-area">
																<span class="text-danger delete-widget"><?php _e('Delete') ?></span></span>
															</div>
															<div class="float-right">
																<button class="btn btn-primary btn-sm btn-save"><?php _e('Save') ?></button>
															</div>
															<div class="clearfix"></div>
														</div>
													</form>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="widget-description small">
								<?php echo $widget->description ?>
							</div>
						</div>

						<?php
					}
				} ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(()=>{
		$( ".panel-body" ).sortable({
			placeholder: "ui-sortable-placeholder",
				stop: function(event, ui) {
					widget_item_drop(event, ui);
				},
				update: function(event, ui) {
					update_widget_position();
				}
		});
		$( ".widget-block > .widget-item-sortable" ).draggable({
			connectToSortable: ".panel-body",
			helper: "clone",
			revert: "invalid",
			revertDuration: 0,
		});
		$( ".panel-body" ).disableSelection();

		$( ".panel-body > .widget-item-sortable" ).each(function() {
			$(this).removeClass('ui-sortable-handle');
		});

		$(document).on('click', '.delete-widget', function() {
			let parent_id = $(this).parents().eq(7).attr('id');
			let cur_index = $(this).parents().eq(5).index();
			let data = {
				action: 'delete_widget',
				parent: parent_id,
				index: cur_index,
			}
			let self = $(this);
			if(confirm('Confirm delete')){
				ajax_action(data).then((res)=>{
					if(res == 'ok'){
						self.parents().eq(5).remove();
					} else {
						console.log(res);
						alert('Error, check console log for more info');
					}
				});
			}
		});

		$('body').on("submit", ".widget-form > form", function( event ) {
			let arr = $( this ).serializeArray();
			event.preventDefault();

			let parent_id = $(this).parents().eq(4).attr('id');
			let cur_index = $(this).parents().eq(2).index();

			let data = {
				action: 'update_widget',
				parent: parent_id,
				index: cur_index,
				data: fix_array(arr),
			}
			
			ajax_action(data).then((res)=>{
				if(res == 'ok'){
					let btn = $(this).find('.float-right > button');
					btn.text('SAVED');
					btn.attr('disabled', 'disabled');
				} else {
					console.log(res);
					alert('Error, check console log for more info');
				}
			});
		});

		$('body').on("input change", ".widget-form > form", function( event ) {
			let btn = $(this).find('.float-right > button');
			btn.text('SAVE');
			btn.attr('disabled', false);
		});

		function widget_item_drop(event, ui){
			let content = ui.item.children('.widget-item').children('.widget-inside');
			if(content.length){
				//content.removeClass('d-none');
				const d = new Date();
				let uid = 'ID_'+d.getTime();
				let html = content.html().replace('ID_TO_REPLACE', uid);
				ui.item.replaceWith(html.replace('ID_TO_REPLACE', uid));
				$('#'+uid).collapse('toggle');
				update_widget_position();
			}
		}

		function update_widget_position(){
			let objs = {};
			$('.panel-section').each(function() {
				let id = $(this).data('id');
				let widgets = $(this).find('.widget-item-sortable');
				objs[id] = get_widget_list(widgets);
			});

			function get_widget_list(widgets){
				let arrs = [];
				widgets.each(function() {
					let arr = $(this).find('.widget-form > form').serializeArray();
					arrs.push(fix_array(arr));
				});
				return arrs;
			}

			let data = {
				action: 'save_widgets_position',
				data: objs,
			}

			ajax_action(data).then((res)=>{
				if(res == 'ok'){
					//
				} else {
					console.log(res);
					alert('Error, check console log for more info');
				}
			});
		}

		function fix_array(arr){
			let obj = {};
			arr.forEach((item)=>{
				obj[item.name] = item.value;
			});
			return obj;
		}

		function ajax_action(data){
			let wait = new Promise((res) => {
				$.ajax({
					url: 'includes/ajax-actions.php',
					type: 'POST',
					dataType: 'json',
					data:data,
					success: function (data) {
						//console.log(data.responseText);
					},
					error: function (data) {
						//console.log(data.responseText);
					},
					complete: function (data) {
						console.log(data.responseText);
						res(data.responseText);
					}
				});
			});
			return wait;
		}
	});
	
</script>