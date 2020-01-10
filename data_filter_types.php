<?php
include('common_bo.php');

echo json_encode(array(
    'success' => true,
    'rows'    => \Sale\Filter::getTypes()
));