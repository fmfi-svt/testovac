#!/bin/bash

count=1416
count=96

rm -f tmp.txt
> result.txt

x=0
while [ $x -lt $count ]
do
	dd if=/dev/urandom bs=8 count=200 2> /dev/null | od -tu8 -w8 | grep -P "^\d{7}\s+\d*[1-9]\d{13}$" | cut -c15-28 >> result.txt
	sort result.txt | uniq > tmp.txt
	mv tmp.txt result.txt
	x=`cat result.txt | wc -l`
done

head -n$count result.txt > tmp.txt

php appendVerhoeff.php < tmp.txt > result.txt
rm tmp.txt

cat result.txt | php barQrCode3.php > stickers.tex

pdflatex --shell-escape stickers.tex
rm stickers.aux
rm stickers.log
rm stickers-pics.pdf

pdf2ps stickers.pdf
