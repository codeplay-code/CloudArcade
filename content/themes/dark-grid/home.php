<?php include  TEMPLATE_PATH . "/includes/header.php" ?>
<div class="container">
	<div class="game-container">
		<?php widget_aside('top-content') ?>
		<h3 class="item-title"><i class="fa fa-plus" aria-hidden="true"></i><?php _e('NEW GAMES') ?></h3>
		<div class="grid-layout grid-wrapper" id="section-new-games">
			<?php
			$index = 0;
			$games = fetch_games_by_type('new', 30, 0, false)['results'];
			foreach ( $games as $game ) { $index++; ?>
				<?php include  TEMPLATE_PATH . "/includes/grid-masonry.php" ?>
			<?php } ?>
		</div>
		<!-- Load more games -->
		<div class="load-more-games-wrapper">
			<!-- Template -->
			<div class="item-append-template" style="display: none;">
				<div class="grid-item item-grid">
					<a href="<?php echo get_permalink('game') ?>{{slug}}">
					<div class="list-game">
						<div class="list-thumbnail"><img src="<?php echo get_template_path(); ?>/images/thumb-placeholder1.png" data-src="{{thumbnail}}" class="small-thumb lazyload" alt="{{title}}"></div>
						<div class="list-title">
							<div class="star-rating text-center"><img src="<?php echo DOMAIN . TEMPLATE_PATH . '/images/star-{{rating}}.png' ?>" alt="rating"></div>{{title}}
						</div>
					</div>
					</a>
				</div>
			</div>
			<!-- The button -->
			<div class="btn btn-primary btn-load-more-games">
				<?php _e('Load more games') ?> <i class="fa fa-chevron-down" aria-hidden="true"></i>
			</div>
		</div>
		<h3 class="item-title"><i class="fa fa-certificate" aria-hidden="true"></i><?php _e('POPULAR GAMES') ?></h3>
		<div class="grid-layout grid-wrapper">
			<?php
			$games = fetch_games_by_type('popular', 14, 0, false)['results'];
			foreach ( $games as $game ) { ?>
				<?php include  TEMPLATE_PATH . "/includes/grid.php" ?>
			<?php } ?>
		</div>
		<h3 class="item-title"><i class="fa fa-gamepad" aria-hidden="true"></i><?php _e('YOU MAY LIKE') ?></h3>
		<div class="grid-layout grid-wrapper">
			<?php
			$games = fetch_games_by_type('random', 14, 0, false)['results'];
			foreach ( $games as $game ) { ?>
				<?php include  TEMPLATE_PATH . "/includes/grid.php" ?>
			<?php } ?>
		</div>
		<?php widget_aside('bottom-content') ?>
	</div>
	<div class="mb-4 mt-4 hp-bottom-container">
		<?php widget_aside('homepage-bottom') ?>
	</div>
</div>
<?php include  TEMPLATE_PATH . "/includes/footer.php" ?>