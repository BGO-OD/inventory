#!/bin/bash

source barcodebase

ncol=10;
nrow=20;

labelwidth=19 #in mm
labelheight=13.5 #in mm

colmargin=0 #in mm
rowmargin=0 #in mm

leftmargin=28.4 #in pt
bottommargin=52 #in pt

colwidth=$(echo "($labelwidth+$colmargin) *72/25.4" | bc) #in pt
rowheight=$(echo "($labelheight+$rowmargin) *72/25.4" | bc) #in pt

cat <<EOD
%!PS-Adobe-2.0
%%DocumentPaperSizes: A4
%%EndComments
%%EndProlog

%%Page: 1 1
EOD

for column in $(seq 0 $[ $ncol - 1 ] ); do
	for row in $(seq 0 $[ $nrow - 1 ] ); do
		if [ $# -gt 0 ]; then	
				number=$1
				shift
		else
				number=$[ $number + 1 ]
		fi
		cat <<EOD
gsave
$column $colwidth mul $leftmargin add
$row $rowheight mul $bottommargin add
translate
%0 0 $labelwidth 72 mul 25.4 div $labelheight 72 mul 25.4 div rectstroke
-5 0 moveto 5 0 lineto
0 -5 moveto 0 5 lineto
$colwidth $rowheight moveto
-5 0 rmoveto 10 0 rlineto
-5 -5 rmoveto 0 10 rlineto
stroke
gsave
-10 72 25.4 div add 4.5 translate
EOD
barcode -b $[ $BARCODEBASE + $number ] -e ean8 -u mm -g 17x4 -n -E
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
($PROJECT_SHORT) show

grestore
EOD
	done
done;

cat <<EOD
showpage
EOD
