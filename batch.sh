#!/bin/bash

BACKUPDIR=$(readlink -f "output")
BATCH="$2"

if [ "$BATCH" == "" ]
then
	echo "Zadaj nazov batchu. Usage: batch.sh print|export <batch-name>"
	exit 2
fi

if [ -d "$BACKUPDIR/$BATCH" ]
then
	echo "Vystupny adresar $BACKUPDIR/$BATCH uz existuje"
	exit 2
fi

USER=$(cat config.php | grep '\$user =' | cut -d"'" -f2)
PASS=$(cat config.php | grep '\$pass =' | cut -d"'" -f2)

mkdir "$BACKUPDIR/$BATCH"

case "$1" in
	'print')
		cd output
		php ../src/printStudents.php &&
		pdflatex printed.tex --interaction=nonstopmode &&
		cp "printed.pdf" "$BACKUPDIR/$BATCH/printed.pdf" &&
		lp "$BACKUPDIR/$BATCH/printed.pdf" &&
		echo "Uspesne vytlacene" 
		cd ..
	;;
	'export')
		php src/exportStudents.php
		cp "output/cbody.php" "$BACKUPDIR/$BATCH/cbody.php"
	;;
        *)
		echo "Zly prvy argument. Usage: batch.sh print|export <batch-name>"
		exit 2
	;;
esac

mysqldump -u "$USER" --password="$PASS" testovac >"$BACKUPDIR/$BATCH/db.sql"
cp "logs/log.log" "$BACKUPDIR/$BATCH/log.log"
