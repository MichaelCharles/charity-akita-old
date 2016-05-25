<?php 
$currentPoints = 0; 
$totalPoints = 0;
$hardPoints = 0;
// Set up the token object to be able to parse the explanation text with the token replacement method
require_once WPSQT_DIR.'/lib/Wpsqt/Tokens.php';
$objTokens = Wpsqt_Tokens::getTokenObject();
$objTokens->setDefaultValues();

foreach ( $_SESSION['wpsqt'][$quizName]['sections'] as $section ){ ?>
		<h3><?php echo $section['name']; ?></h3>
		
		<?php
			if (!isset($section['questions'])){
				echo 'Error - no questions<br />Quitting.';
				exit;
			}
				foreach ($section['questions'] as $questionKey => $questionArray){ 
					$questionId = $questionArray['id'];
					if ( ucfirst($questionArray['type']) == 'Multiple' || ucfirst($questionArray['type']) == 'Single'  ){
						$correct = isset($section['answers'][$questionId]['mark']) && $section['answers'][$questionId]['mark'] == 'correct';
						if ( $correct){
							$currentPoints++;
							$hardPoints++;
						}
						$totalPoints++;	
						if ($correct){
							$color = '009900';
							$symbol = '&#10004';
						}else{
							$color = 'FF0000';
							$symbol = '&#10006;';
						}
					?>
						
					<h4 style="color: #<?php echo $color;?>;"><?php print stripslashes($questionArray['name']); ?> <?php echo $symbol; ?></h4>
						<p class="answer_given">
							<ol>
								<?php foreach ($questionArray['answers'] as $answerKey => $answer){ ?>
									  <li><font color="<?php echo ( $answer['correct'] != 'yes' ) ?  (isset($section['answers'][$questionId]['given']) &&  in_array($answerKey, $section['answers'][$questionId]['given']) ) ? '#FF0000' :  '#000000' : '#009900' ; ?>"><?php echo esc_html(stripslashes($answer['text'])); ?></font><?php if (isset($section['answers'][$questionId]['given']) && in_array($answerKey, $section['answers'][$questionId]['given']) ){ ?> - Given<?php }?></li>
								<?php } ?>
							</ol>
						</p>
						<?php if (isset($questionArray['explanation'])) { 
							
							// replace the tokens
							$explanation = $objTokens->doReplacement( $questionArray['explanation'] );
							?>
							<p class="wpsqt-answer-explanation"><?php echo $explanation; ?></p>
						<?php } ?>
				<?php } else { 
					?>				
					<b><u><?php _e('Answer Given', 'wp-survey-and-quiz-tool'); ?></u></b>
					<p class="answer_given" style="background-color : #c0c0c0; border : 1px dashed black; padding : 5px;overflow:auto;height : 200px;"><?php if ( isset($section['answers'][$questionId]['given']) && is_array($section['answers'][$questionId]['given']) ){ echo nl2br(esc_html(stripslashes(current($section['answers'][$questionId]['given'])))); } ?></p>
					<?php if (isset($questionArray['explanation'])) { 
						// replace the tokens
						$explanation = $objTokens->doReplacement( $questionArray['explanation'] );
						?>
						<p class="wpsqt-answer-explanation"><?php echo $explanation; ?></p>
					<?php } ?>
					<?php if ( isset($questionArray['hint']) && $questionArray['hint'] != "" ) { ?>- <a href="#" class="show_hide_hint"><?php _e('Show/Hide Hint', 'wp-survey-and-quiz-tool'); ?></a></p>
					<div class="hint">
						<h5><?php _e('Hint', 'wp-survey-and-quiz-tool'); ?></h5>
						<p style="background-color : #c9c9c9;padding : 5px;"><?php echo nl2br(esc_html(stripslashes($questionArray['hint']))); ?></p>
					</div>
					<?php } else { ?></p><?php } ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	<p><font size="+3"><?php _e('Total Points', 'wp-survey-and-quiz-tool'); echo ': '.$_SESSION['wpsqt']['current_score']; ?></font></p>
