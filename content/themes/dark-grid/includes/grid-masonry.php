<?php
$span = '';
$thumb = get_small_thumb($game);
if($index == 1 || $index == 9 || $index == 15 || $index == 23){
	$span = 'span-2';
	$thumb = esc_url($game->thumb_2);
	if(substr($thumb, 0, 1) == '/'){
		$thumb = DOMAIN . substr($thumb, 1);
	}
}
?>
<div class="grid-item <?php echo esc_string($span) ?> item-grid">
	<a href="<?php echo get_permalink('game', $game->slug) ?>">
	<div class="list-game">
		<div class="list-thumbnail"><img src="<?php echo get_template_path(); ?>/images/thumb-placeholder1.png" data-src="<?php echo $thumb ?>" class="small-thumb lazyload" alt="<?php echo esc_string($game->title) ?>"></div>
		<div class="list-title">
			<div class="star-rating text-center"><img src="<?php echo DOMAIN . TEMPLATE_PATH . '/images/star-'.get_rating('5', $game).'.png' ?>" alt="rating"></div>
			<?php echo esc_string($game->title); ?>
		</div>
	</div>
	</a>
</div>