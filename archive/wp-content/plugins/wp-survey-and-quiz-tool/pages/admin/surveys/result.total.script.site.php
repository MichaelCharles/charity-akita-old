<?php if ( $sections == false ) { ?>

	<p>There are no results for this survey yet.</p>

<?php 
} else { 
	foreach ( $sections as $sectionKey => $secton ){
		foreach ( $secton['questions'] as $questonKey => $question ) {
?>
			<div class="wpsqt-question-review">
				<?php require('result.total.common.php');?>
				<div class="wpsqt-question-info">
					<div class="wpsqt-question-title">Question Info</div>
					<?php 
						for ($i = 0; $i < count($nameArray); $i++) {
							echo '<div class="wpsqt-question-response">'.$nameArray[$i].':&nbsp;'.$valueArray[$i].'&nbsp;entries</div>';
						}
						$givenAnswers = $wpdb->get_row("SELECT `sections` FROM `".WPSQT_TABLE_RESULTS."` ORDER BY `id` DESC LIMIT 1", ARRAY_A);
						$givenAnswers = unserialize($givenAnswers['sections']);
						if (isset($givenAnswers[$sectionKey]['answers'][$questonKey]['given'])) {
							$givenAnswers = $givenAnswers[$sectionKey]['answers'][$questonKey]['given'];
							echo '<div class="wpsqt-question-response-you">You entered: ';
							if (is_array($givenAnswers)) {
								$i = 1;
								foreach ($givenAnswers as $givenAnswer) {
									foreach($_SESSION['wpsqt'][$_SESSION['wpsqt']['current_id']]['sections'][$sectionKey]['questions'] as $question) {
										if ($question['id'] == $questonKey) {
											if ($question['type'] == 'Likert Matrix') {
												if (is_array($givenAnswer)) {
													$givenAnswerDetails = explode("_", $givenAnswer[0]);
												} else {
													$givenAnswerDetails = explode("_", $givenAnswer);
												}
												echo '<em>'.$givenAnswerDetails[0].'</em>: '.$givenAnswerDetails[1];
											} else if(isset($question['answers'][$givenAnswer]['text'])) {
												echo $question['answers'][$givenAnswer]['text'];
											} else {
												echo '<em>'.__('You didn\'t answer this question', 'wp-survey-and-quiz-tool').'</em>';
											}
											if ($i < count($givenAnswers)) 
												echo ', ';
										}
									}
									$i++;
								}
							} else {
								echo $givenAnswers;
							}
						} else {
							echo '<div class="wpsqt-question-response-you">' . __('You didn\'t answer this question', 'wp-survey-and-quiz-tool');
						}
					?>
					</div>
				</div>
			</div>
	<?php 
		} 
	} 
	?>
	<div class="wpsqt-survey-info">
		<strong>Survey info</strong> 
		<?php
		$nOfParticipants = $wpdb->get_var("SELECT `total` FROM `".WPSQT_TABLE_SURVEY_CACHE."` WHERE `item_id` = '".$surveyId."'");
		echo '<p>There has been '.$nOfParticipants.' participants</p>';
		?>
	</div>
<?php
} 
