<?php
require_once WPSQT_DIR.'lib/Wpsqt/Export.php';

	/**
	 *
	 *
	 * @author Iain Cambridge
	 * @copyright Fubra Limited 2010-2011, all rights reserved.
  	 * @license http://www.gnu.org/licenses/gpl.html GPL v3
  	 * @package WPSQT
	 */

class Wpsqt_Export_Csv extends Wpsqt_Export {

	private $csvLines = array();

	public $quizId;

	public function output(){

		$csv = "";
		foreach ( $this->_data as $array ) {
			$csv .= implode(",",$array).PHP_EOL;
		}

		return $csv;
	}

	public function generate($id) {
		global $wpdb;
		
		$type = $wpdb->get_row($wpdb->prepare("SELECT `type` FROM `".WPSQT_TABLE_QUIZ_SURVEYS."` WHERE id = %d", array($id)),ARRAY_A);

		switch ($type['type']){
			case 'quiz':
				$results = $wpdb->get_results('SELECT * FROM '.WPSQT_TABLE_RESULTS.' WHERE item_id = "'.$id.'"', ARRAY_A);
				$this->csvLines[] = 'id, Name, Score, Total, Percentage, Pass/Fail, Status, Date';
				foreach( $results as $result ){
					$csvline = $result['id'].",";
					$csvline .= $result['person_name'].',';
					if($result['total'] == 0) {$csvline .= ',,';} else {$csvline .= $result['score'].",".$result['total'].",";}
					if($result['total'] == 0) {$csvline .= ',';} else {$csvline .= $result['percentage']."%,";}
					if ($result['pass'] == 1) {$csvline .= "Pass,";} else {$csvline .= "Fail,";}
					$csvline .= ucfirst($result['status']).",";
					if (!empty($result['datetaken'])) { $csvline .= date('d-m-y G:i:s',$result['datetaken']); };
					$this->csvLines[] = $csvline;
				}	
				break;
			case 'survey':
			case 'poll':
				$results = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPSQT_TABLE_SURVEY_CACHE."` WHERE item_id = %d",array($id)), ARRAY_A);
				$sections = unserialize($results['sections']);
				$this->csvLines[] = 'Question Name,Answer,Votes,Percentages';

				if (empty($sections) || !is_array($sections)) {
					break;
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
						if ($question['type'] == 'Free Text') {
							$csvLine .= $question['name'].',This question has free text answers which cannot be shown,,,';
							$this->csvLines[] = $csvLine;
							continue;
						}else{
							foreach ($answers_display as $name => $count) {
								$csvLine = $question['name'];
								$question['name'] = str_repeat(' ',strlen($question['name'])); //After the first use, we left it blank to avoid repetitions
								$csvLine .= ','.$name.'';
								if ($question['type'] == 'Likert Matrix'){
									$porcentage = array();
									
									$csvLine .= ',';
									foreach ($count as $c){
										$csvLine .= $c.'|';
										$porcentage[] = $total[$name] ? round($c / $total[$name] * 100, 2) : 0;
									}

									$csvLine .= ','.implode('%|', $porcentage).'%"';
								}else{
									$percentage = round($count / $total * 100, 2);
									$csvLine .= ','.$count;
									$csvLine .= ','.$percentage.'%';
								}
								$this->csvLines[] = $csvLine;
							}
						}
					}
				}
				break;
			default:
				break;
		}
		

		return $this->csvLines;
	}

	public function saveFile() {
		$path = 'tmp/results-'.$this->quizId.'.csv';
		file_put_contents(WPSQT_DIR.$path, implode($this->csvLines, "\r\n"));
		return $path;
	}

}
