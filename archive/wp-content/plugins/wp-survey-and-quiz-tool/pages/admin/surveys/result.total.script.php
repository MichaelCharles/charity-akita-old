<?php if ( $sections == false ) { ?>
	<p>There are no results for this survey yet.</p>
<?php 
} else {
	foreach ( $sections as $sectionKey => $secton ){
		foreach ( $secton['questions'] as $questonKey => $question ) {
			require('result.total.common.php');
		}
	}
} 
