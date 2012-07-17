<?php
$code=trim($_GET['number'],"'");
header("Content-Type: image/png");
passthru("/bin/bash ./create_label.sh $code | convert -density 150  - -background white -flatten png:-");
?>
