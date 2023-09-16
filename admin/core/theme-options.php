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
				<?php
				if(file_exists( ABSPATH . TEMPLATE_PATH . '/options.php' )){
					require_once( ABSPATH . TEMPLATE_PATH . '/options.php' );
				}
				?>
			</div>
		</div>
	</div>
</div>