<?php
$code=trim($_GET['number'],"'")+5500000;
header("Content-Type: image/png");
passthru("barcode -b $code -e ean8 -u in -g 1x0.5 -E | gs -r150 -g200x100 -sOutputFile=- -sDEVICE=pnggray -q -");
?>
