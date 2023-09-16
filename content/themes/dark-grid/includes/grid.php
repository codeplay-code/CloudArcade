<div class="grid-item item-grid">
	<a href="<?php echo get_permalink('game', $game->slug) ?>">
	<div class="list-game">
		<div class="list-thumbnail"><img src="<?php echo get_template_path(); ?>/images/thumb-placeholder1.png" data-src="<?php echo get_small_thumb($game) ?>" class="small-thumb lazyload" alt="<?php echo esc_string($game->title) ?>"></div>
		<div class="list-title">
			<div class="star-rating text-center"><img src="<?php echo DOMAIN . TEMPLATE_PATH . '/images/star-'.get_rating('5', $game).'.png' ?>" alt="rating"></div><?php echo esc_string($game->title); ?>
		</div>
	</div>
	</a>
</div>