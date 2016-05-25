<?php

/**
 * Handles marking the result.
 * 
 * 
 * @author Iain Cambridge
 * @copyright Fubra Limited 2010-2011, all rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html GPL v3 
 * @package WPSQT
 */

 class Wpsqt_Page_Main_Results_Mark extends Wpsqt_Page {
 	
	public function init(){
		$this->_pageView = "admin/results/mark.php";
	}	
	
	/**
	 * 
	 * @since 2.0
	 */
	public function process() {
		
		global $wpdb;
		
		$rawResult = $wpdb->get_row(
						$wpdb->prepare("SELECT * FROM `".WPSQT_TABLE_RESULTS."` WHERE id = %d", 
									   array($_GET['resultid'])),ARRAY_A
									);
										
		$rawResult['sections'] = unserialize($rawResult['sections']);
		$rawResult['person'] = unserialize($rawResult['person']);
		
		if ( $_SERVER['REQUEST_METHOD'] != "POST" ) {
		
			$this->_pageVars['result'] = $rawResult;
			$timeTaken = "";
	
			$seconds = $rawResult['timetaken'] % 60;
			$minutes = intval($rawResult['timetaken'] / 60);
			$hours = intval($minutes / 60);
			$minutes = $minutes % 60;
			$days = intval($hours / 24);
			$hours = $hours % 24;
			
			if ($days != 0){
				$timeTaken .= $days.' Days ';
			}
		    if ($hours != 0){
		    	$timeTaken .= $hours.' Hours ';
		    }
		    if ($minutes != 0){
		    	$timeTaken .= $minutes.' Minutes ';
		    }
		    if ($seconds != 0){
		    	$timeTaken .= $seconds.' Seconds';
		    }
		    $timeTaken = trim($timeTaken);
		    $this->_pageVars['timeTaken'] = $timeTaken;
		} else {
			
			$currentPoints = (int)$_POST['overall_mark'];	
			$totalPoints = (int)$_POST['total_mark'];
			foreach ( $rawResult['sections'] as $sectionKey => $section ){
				foreach ( $section['questions'] as $questionKey => $question ){
					if ( isset($_POST['mark'][$questionKey]) ){					
						$rawResult['sections'][$sectionKey]['questions'][$questionKey]['mark'] = (int)$_POST['mark'][$questionKey];
						$currentPoints += (int)$_POST['mark'][$questionKey];
					}
					if ( isset($_POST['comment'][$questionKey]) ){
						$rawResult['sections'][$sectionKey]['questions'][$questionKey]['comment'] = $_POST['comment'][$questionKey];
					}
				}
			}
			$percentage = ($currentPoints / $totalPoints) * 100;
			
			$pass_fail = 0;
			$quiz = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".WPSQT_TABLE_QUIZ_SURVEYS."` WHERE id = %d", array($rawResult['item_id'])),ARRAY_A);
			$quiz_settings = unserialize($quiz['settings']);
			if($percentage >= $quiz_settings['pass_mark']){
				$pass_fail = 1;
			}
			
			$ser = serialize($rawResult['sections']);
			$ser = mysql_real_escape_string($ser);
			$wpdb->query( 
				$wpdb->prepare('UPDATE '.WPSQT_TABLE_RESULTS.' SET sections=%s,status=%s,score=%d,total=%d,percentage=%d,pass=%d WHERE id = %d', 
							array( $ser,$_POST['status'],$currentPoints,$totalPoints,$percentage,$pass_fail,$_GET['resultid']) ) 
						);
			$this->redirect(WPSQT_URL_MAIN."&section=results"."&subsection=quiz&id=".$_GET['id']."&marked=true");			
		}
			
	}
	
 }
 
