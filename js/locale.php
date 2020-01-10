<?php
header('Content-type: application/x-javascript; charset=UTF8'); 

$translator = $application->getTranslator();  
?>
var LangData = <?php echo json_encode($translator->getMessages());?>;

function _(key) {
	if (LangData[key]) return LangData[key];
	return key;
}