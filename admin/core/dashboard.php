<?php
$warning_list = get_admin_warning();
if(!empty($warning_list)){
	echo('<div class="site-warning">');
	foreach ($warning_list as $val) {
		show_alert($val, 'warning');
	}
	echo('</div>');
}
if(file_exists(ABSPATH.'static/') && file_exists(ABSPATH.'index_static.php')){
	show_alert('Static Site is active.', 'info');
}
?>
<div class="update-info"></div>
<div class="row">
	<div class="col-lg-9">
		<div class="section section-stats">
			<select class="form-select stats-option" id="stats-option">
				<option value="week"><?php echo _t('Last %a days', 7) ?></option>
				<option value="month"><?php echo _t('Last %a days', 30) ?></option>
			</select>
			<h3 class="section-title">
				<i class="fas fa-chart-line"></i> <?php echo _t('Statistics') ?>
			</h3>
			<div class="container-stats">
				<div class="chart-container" style="position: relative; height:40vh; width:80vw">
					<canvas id="statistics"></canvas>
				</div>
			</div>
		</div>
		<div class="section-boxes">
			<div class="boxes">
				<div class="row">
					<div class="col-6 col-md-3">
						<div class="box box-1">
							<h2 class="amount">
								<?php echo Game::getTotalGames() ?>
							</h2>
							<div class="box-info">
								<b><?php _e('Games') ?></b>
								<div class="small">
									<?php
									$conn = open_connection();
									$sql = "SELECT COUNT(*) FROM games WHERE MONTH(createddate) = MONTH(CURRENT_DATE()) AND YEAR(createddate) = YEAR(CURRENT_DATE())";
									$st = $conn->prepare($sql);
									$st->execute();
									$amount = $st->fetchColumn();
									_e('+%a this month', $amount);
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-6 col-md-3">
						<div class="box box-2">
							<h2 class="amount">
								<?php echo User::getTotalUsers() ?>
							</h2>
							<div class="box-info">
								<b><?php _e('Users') ?></b>
								<div class="small">
									<?php
									$conn = open_connection();
									$sql = "SELECT COUNT(*) FROM users WHERE MONTH(join_date) = MONTH(CURRENT_DATE()) AND YEAR(join_date) = YEAR(CURRENT_DATE())";
									$st = $conn->prepare($sql);
									$st->execute();
									$amount = $st->fetchColumn();
									_e('+%a this month', $amount);
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-6 col-md-3">
						<div class="box box-3">
							<h2 class="amount">
								<?php
								$conn = open_connection();
								$sql = "SELECT COUNT(*) FROM comments";
								$st = $conn->prepare($sql);
								$st->execute();
								echo $st->fetchColumn();
								?>
							</h2>
							<div class="box-info">
								<b><?php _e('Comments') ?></b>
								<div class="small">
									<?php
									$conn = open_connection();
									$sql = "SELECT COUNT(*) FROM comments WHERE MONTH(created_date) = MONTH(CURRENT_DATE()) AND YEAR(created_date) = YEAR(CURRENT_DATE())";
									$st = $conn->prepare($sql);
									$st->execute();
									$amount = $st->fetchColumn();
									_e('+%a this month', $amount);
									?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-6 col-md-3">
						<div class="box box-4">
							<h2 class="amount">
								<?php
								$conn = open_connection();
								$sql = "SELECT COUNT(*) FROM posts";
								$st = $conn->prepare($sql);
								$st->execute();
								echo $st->fetchColumn();
								?>
							</h2>
							<div class="box-info">
								<b><?php _e('Posts') ?></b>
								<div class="small">
									<?php
									$conn = open_connection();
									$sql = "SELECT COUNT(*) FROM posts WHERE MONTH(created_date) = MONTH(CURRENT_DATE()) AND YEAR(created_date) = YEAR(CURRENT_DATE())";
									$st = $conn->prepare($sql);
									$st->execute();
									$amount = $st->fetchColumn();
									_e('+%a this month', $amount);
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-6">
				<div class="section section-full">
					<h3 class="section-title"><i class="fa fa-comments"></i> <?php _e('Comments') ?></h3>
					<?php
					$index = 0;
					$conn = open_connection();
					$sql = "SELECT * FROM comments ORDER BY id DESC LIMIT 3";
					$st = $conn->prepare($sql);
					$st->execute();
					$row = $st->fetchAll();
					//
					if(count($row)){
						?>
						<div class="table-responsive">
							<table class="table custom-table">
								<thead>
									<tr>
										<th>#</th>
										<th>Sender</th>
										<th>Date</th>
										<th>Comment</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ( $row as $item ) {
										$index++;
										?>
										<tr>
											<td scope="row"><?php echo $index ?></td>
											<td>
												<?php echo $item['sender_username'] ?>
											</td>
											<td>
												<?php echo $item['created_date'] ?>
											</td>
											<td class="td-ellipsis">
												<?php echo $item['comment'] ?>
											</td>
										</tr>
										<?php
									}
									?>
											
								</tbody>
							</table>
						</div>
						<div class="text-center section-bottom-link">
							<a href="dashboard.php?viewpage=plugin&name=comments-manager"><?php _e('Manage Comments') ?></a>
						</div>
						<?php
					} else {
						?>
						<div class="general-wrapper">
							<?php _e('No comment') ?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<div class="col-lg-6">
				<div class="section section-full">
					<h3 class="section-title"><i class="fas fa-flag"></i> <?php _e('Game Reports') ?></h3>
					<?php
					if(is_plugin_exist('game-reports')){
						$reports = get_pref('game-reports');
						if($reports){
							$reports = json_decode($reports, true);
						} else {
							$reports = [];
						}
						if(count($reports)){
							?>
							<div class="table-responsive">
								<table class="table custom-table">
									<thead>
										<tr>
											<th>#</th>
											<th>Game</th>
											<th>Type</th>
											<th>Comment</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$index = 0;
										foreach ( $reports as $item ) {
											$index++;
											$color = '';
											if($item['type'] == 'bug'){
												$color = 'bg-warning';
											} elseif($item['type'] == 'error'){
												$color = 'bg-danger';
											} elseif($item['type'] == 'other'){
												$color = 'bg-success';
											}
											$game = Game::getById($item['game_id']);
											?>
											<tr>
												<td scope="row"><?php echo $index ?></td>
												<td class="td-ellipsis">
													<a href="<?php echo get_permalink('game', $game->slug) ?>" target="_blank"><?php echo $game->title ?></a>
												</td>
												<td>
													<span class="<?php echo $color ?> text-dark"> <?php echo $item['type'] ?> </span>
												</td>
												<td class="td-ellipsis">
													<?php echo $item['comment'] ?>
												</td>
											</tr>
											<?php
											if($index >= 3){
												break;
											}
										}
										?>	
									</tbody>
								</table>
							</div>
							<div class="text-center section-bottom-link">
								<a href="dashboard.php?viewpage=plugin&name=game-reports"><?php _e('Manage Reports') ?></a>
							</div>
							<?php
						} else {
							?>
							<div class="general-wrapper">
								<?php _e('No report') ?>
							</div>
							<?php
						}
					} else {
						?>
						<div class="general-wrapper">
							<?php _e('Game Reports plugin not installed') ?>
						</div>
						<?php
					}
					?>	
				</div>
			</div>
		</div>
		<div class="section section-full">
			<h3 class="section-title"><i class="fas fa-dice-d6"></i> <?php echo _t('Top games') ?></h3>
			<div class="table-responsive">
				<table class="table custom-table">
					<thead>
						<tr>
							<th>#</th>
							<th><?php _e('Game Name') ?></th>
							<th><?php _e('Played') ?></th>
							<th><?php _e('Category') ?></th>
							<th><?php _e('Likes') ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$index = 0;
						$data = get_game_list('popular', 10);
						$games = $data['results'];
						foreach ( $games as $game ) {
							$index++;
							?>
						<tr>
							<td><?php echo esc_int($index); ?></td>
							<td class="td-ellipsis">
								<a href="<?php echo get_permalink('game', $game->slug) ?>" target="_blank"><?php echo esc_string($game->title); ?></a>
							</td>
							<td>
								<?php echo format_number_abbreviated(esc_int($game->views)); ?>
							</td>
							<td class="td-ellipsis">
								<?php echo '<span class="categories">'.esc_string($game->category).'</span>'; ?>
							</td>
							<td>
								<?php
									$vote_percentage = '';
									$value = "-";
									if($game->upvote+$game->downvote > 0){
										$vote_percentage = floor(($game->upvote/($game->upvote+$game->downvote))*100);
										$value = $vote_percentage.'%';
									}
									echo '<div class="row">';
									echo '<div class="col-4">'.$value.'</div>';
									echo '<div class="col-4"><i class="fa fa-thumbs-up" aria-hidden="true"></i>'.esc_int($game->upvote).'</div><div class="col-4"><i class="fa fa-thumbs-down" aria-hidden="true"></i>'.esc_int($game->downvote).'</div>';
									echo '</div>';
								?>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-lg-3">
		<?php if(!ADMIN_DEMO) echo('<div class="section"><div class="official-info"></div></div>') ?>
		<div class="section">
			<div class="quote-box">
				<div id="quote"></div>
			</div>
		</div>
	</div>
</div>