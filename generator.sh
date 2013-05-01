#!/bin/bash

stickers_needed=1400

pages=$((stickers_needed/24))
if [[ $((stickers_needed%24)) -gt 0 ]]
	then
	pages=$((pages+1))
fi
pages=$((pages+1))
count=$((pages*24))

rm -f tmp.txt
> result.txt

x=0
while [[ $x -lt $count ]]
do
	dd if=/dev/urandom bs=8 count=200 2> /dev/null | od -tu8 -w8 | grep -P "^\d{7}\s+\d*[1-9]\d{13}$" | cut -c15-28 >> result.txt
	sort result.txt | uniq > tmp.txt
	mv tmp.txt result.txt
	x=`cat result.txt | wc -l`
done
shuf result.txt > tmp.txt
head -n$count tmp.txt > result.txt

php appendVerhoeff.php < result.txt > tmp.txt
mv tmp.txt result.txt

cat result.txt | php barQrCode3.php > stickers.tex

pdflatex --shell-escape stickers.tex
rm stickers.aux
rm stickers.log
rm stickers-pics.pdf
rm stickers.tex

pdftk stickers.pdf cat 2-$pages output stickers-cut.pdf
mv stickers-cut.pdf stickers.pdf
tail -n+25 result.txt > tmp.txt
mv tmp.txt result.txt
