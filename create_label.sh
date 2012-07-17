#!/bin/bash

ncol=1;
nrow=1;

labelwidth=19 #in mm
labelheight=13.5 #in mm

colmargin=0 #in mm
rowmargin=0 #in mm

leftmargin=0 #in pt
bottommargin=0 #in pt

width=$(echo "($labelwidth+$colmargin) *72/25.4" | bc) #in pt
height=$(echo "($labelheight+$rowmargin) *72/25.4" | bc) #in pt
number=$1


cat <<EOD
%!PS-Adobe-2.0 EPSF-1.2
%%BoundingBox: 0 0 $width  $height
%%EndComments
%%EndProlog

%%Page: 1 1
gsave
-10 72 25.4 div add 4.5 translate
EOD
barcode -b $[ 5500000 + $number ] -e ean8 -u mm -g 17x4 -n -E
cat <<EOD
grestore
/Helvetica findfont
16.75 scalefont setfont
newpath
%$labelwidth 72 25.4 div 13 moveto
$labelwidth 72 25.4 div 1.5 moveto
($(printf %05d $number))

show
/Helvetica findfont
12 scalefont setfont
$labelwidth 72 25.4 div 26.5 moveto
(BGO-OD) show
showpage
EOD

