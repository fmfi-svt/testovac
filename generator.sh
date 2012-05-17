#!/bin/bash
cat /dev/null > output.txt
echo "Bash version ${BASH_VERSION}..."
cat /dev/null > result.txt
x=1
while [ $x -le 200 ]
do
	</dev/urandom tr -dc 0-9 | head -c15 | php appendVerhoeff.php > output.txt
	a=`cat output.txt result.txt | sort | uniq -d | wc -l`
	if [ $a -gt 0 ]
	then
		continue
	fi
	s=`cat output.txt`
	if [ -z $s ]
	then
		continue
	fi
	#echo $s
	#echo ${s:3:1}
	#echo ${s:7:1}
	sum=`expr ${s:3:1} + ${s:7:1}`
	#echo sum
	sum=`expr $sum % 10`
	#echo sum
	#echo $check
	#check=`expr $sum - 2`
	#echo $check

	if [ $sum -ne 2 ]
	then
		continue
	fi
	cat output.txt >> result.txt
	x=$(( $x + 1 ))
done
#sort result.txt  > result2.txt
#mv result2.txt result.txt
cat result.txt | php barQrCode3.php > stickers.tex
pdflatex --shell-escape stickers.tex
pdf2ps stickers.pdf
#latex stickers.tex
#dvips stickers.dvi
