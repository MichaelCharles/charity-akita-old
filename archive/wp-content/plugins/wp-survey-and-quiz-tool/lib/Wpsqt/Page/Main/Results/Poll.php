<?php

require_once WPSQT_DIR.'lib/Wpsqt/Page/Main/Results.php';

class Wpsqt_Page_Main_Results_Poll extends Wpsqt_Page_Main_Results {
	
	public function init(){

		if (isset($_POST['deleteall'])) {
			Wpsqt_System::deleteAllResults($_GET['id']);
		}

		$this->_pageView = 'admin/poll/result.php';
	}

	public static function displayResults($pollId) {
		global $wpdb;

		$results = $wpdb->get_row(
					$wpdb->prepare("SELECT * FROM `".WPSQT_TABLE_SURVEY_CACHE."` WHERE item_id = %d",
								   array($pollId)), ARRAY_A
								);
		
		$sections = unserialize($results['sections']);

		if (empty($sections) || !is_array($sections)) {
			echo 'There are no results for this poll yet';
			return;
		}
		
		foreach ($sections as $section) {
			foreach ($section['questions'] as $question) {
				$answers_display = array();
				if (!empty($question['answers']) && is_array($question['answers'])) {
					switch ($question['type']){
						case 'Likert Matrix':
							$total = array();
							foreach($question['answers'] as $answer_name => $values) {
								//TODO: Look up this other
								if ($answer_name == 'other') continue;
								$answers_display[$answer_name] = array();
								$total[$answer_name] =0;
								foreach ($values as $value){
									$answers_display[$answer_name][] = $value['count'];
									$total[$answer_name] += $value['count'];
								}
							}
							break;
						case 'Likert':
							$total = 0;
							foreach($question['answers'] as $answer_name => $values) {
								$answers_display[$answer_name] = $values['count'];
								$total += $values['count'];
							}
							break;
						default:
							$total = 0;
							foreach($question['answers'] as $answer) {
								$answers_display[$answer['text']] = $answer['count'];
								$total += $answer['count'];
							}
							break;
					}
				}
				echo '<h3>'.$question['name'].'</h3>';
				if ($question['type'] == 'Free Text') {
					echo 'This question has free text answers which cannot be shown';
					continue;
				}
				?>
				<table class="widefat post fixed" cellspacing="0">
				<thead>
					<tr>
						<th class="manage-column column-title" scope="col"><?php _e('Answer', 'wp-survey-and-quiz-tool'); ?></th>
						<th scope="col" width="75"><?php _e('Votes', 'wp-survey-and-quiz-tool'); ?>
						
						<?php 
							//TODO: display this information nicer
							if ($question['type'] == 'Likert Matrix'){
								echo '<br/>1|2|3|4|5<br/>';
							}
						?>
						</th>
						<th scope="col" width="90"><?php _e('Percentage', 'wp-survey-and-quiz-tool'); ?>
						<?php 
							if ($question['type'] == 'Likert Matrix'){
								echo '<br/>1|2|3|4|5<br/>';
							}
						?>
						</th>
					</tr>			
				</thead>
				<tfoot>
					<tr>
						<th class="manage-column column-title" scope="col"><?php _e('Answer', 'wp-survey-and-quiz-tool'); ?></th>
						<th scope="col" width="75"><?php _e('Votes', 'wp-survey-and-quiz-tool'); ?></th>
						<th scope="col" width="90"><?php _e('Percentage', 'wp-survey-and-quiz-tool'); ?></th>
					</tr>			
				</tfoot>
				<tbody>
				<?php
				foreach ($answers_display as $name => $count) {
					echo '<tr>';
					echo '<td>'.$name.'</td>'; 
					if ($question['type'] == 'Likert Matrix'){
						$porcentage = array();
						echo '<td>';
						foreach ($count as $c){
							echo $c.'|';
							$porcentage[] = $total[$name] ? round($c / $total[$name] * 100, 2) : 0;
						}
						echo '</td>';

						echo '<td>'.implode('%|', $porcentage).'%</td>';
					}else{
						$percentage = round($count / $total * 100, 2);
						echo '<td>'.$count.'</td>';
						echo '<td>'.$percentage.'%</td>';
					}
					echo '</tr>';
				}
				?>
				</tbody>
				</table>
				<?php
			}
		}
	}
	
}

?>
