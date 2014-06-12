Prevádzkový manuál
==================

C-server
--------
1. Po každej registrácií treba spustiť `./batch.sh print <den><cislo>` napr.
`./batch.sh print Pon1`
Skript vytlačí iba tie strany, ktoré ešte neboli vytlačené. Reťazec `<den><cislo>` musí byť unikátny.

2. Na konci dňa `./batch.sh export <den>` napr. `./batch.sh export Pon` 

Pre vytvorenie nového používateľa treba pridať záznam do súboru `users.php`.

Pre správne fungovanie sessions treba skontrolovať maximálnu dobu ich životnosti v súbore `/etc/php5/apache2/php.ini`, parameter `session.gc_maxlifetime`. Mal by byť nastavený aspoň na hodnotu 86400 (sekúnd). PHP v Ubuntu 14.04 má cron script, ktorý podľa tohto parametra maže session súbory na disku.


H-server
--------

1. Zablokovanie prihlasovania.
    `./console.py login block`
2. Reštartnúť všetky počítače pomocou `Alt-Sysrq-b`, po nabootovaní zapnúť NumLock a stlačiť Enter
pre prihlásenie do Guest-Kiosk módu. 
Mali by zobrazovať 
obrazovku vyžadujúcu zadanie PIDu.
3. Počkať kým koordinátor povie účastníkom všetky úvodné informácie.
4. Po signále povoliť prihlasovanie.
    `./console.py login unblock`
5. Monitorovať priebeh prijímačiek - sledovať či sa účastníci prihlásili,
či sa priebežne zvyšuje počet vyplnených odpovedí, či sa testy automaticky
odovzdajú po vypršaní časového limitu.
    `./console.py monitorwatch`
Spraviť kolečko po počítačoch 
a osobne skontrolovať či sedí kód na náramku s 
kódom prihláseného.
6. Keď sa všetci prihlásia, spustiť príkaz na automatické tlačenie odovzdaných testov: 
    `./console.py printwatch <den><skupina>`
    napr. 
    `./console.py printwatch Pon1`
Vytlačený test spárovať s titulnou stranou a scvaknúť.
7. Keď všetci odovzdajú, odniesť na podpis. 
8. Spustiť zálohu na USB kľúč.
    `utils/backup <den><skupina>`
    napr. 
    `utils/backup Pon1`

Pre ďaľší turnus pokračovať bodom 1.

Na konci dňa exportovať vypočítané body, uploadnuť na stránku.

    ./console.py exportresults > hbody.php

Na konci prijímacích pohovorov vyrobiť PDF pre ohodnotené testy, vytlačiť
štatistiku a urobiť export do AIS. Potom vhodne uložiť na USB kľúč.

    ./console.py bucketstats > stats.csv
    ./console.py printevaluatedexam --all

Info page
---------
Pre zobrazenie výsledkov treba:

1. Nahrať súbory s bodmi (`cbody.php` a `hbody.php`) do adresára `body` (vetva `info-page`).
2. Vhodne upraviť texty a nadpisy vo `vysledky.php` podľa Vinkových inštrukcií.
2. V `include/header.php` nastaviť `$vidno_vysledky` na `true`.

Pre pridanie čiary oddeľujúcej prijatých a neprijatých uchádzačov v tabuľke je potrebné zadať minimálny počet bodov ako 4. argument vo volaní funkcie `vypisTabulku()`. Bez tohto argumentu sa nezobrazí žiadna čiara.


Vyhodnotenie
------------

Na konci treba zhotoviť celkové výsledky a dať ich na USB kľúč.

* `cbody.php` a `hbody.php` vygenerovať podľa postupu vyššie. (Tie netreba ukladať na USB.)
* `ais-export/PraF-{denne,ext}.TXT` je zoznam uchádzačov, čo niekto nejako vytiahne z AISu.
* `out_{denna,externa}_{1,2}.xml` sa vygeneruje pomocou `exportais.py` (na C serveri) - spusti ho a uvidíš usage.
* `out_{denna,externa}_csv.csv` sa vygeneruje pomocou `exportvysledkycsv.py` (na C serveri) - spusti ho a uvidíš usage.
* `vysledky20XX.xls` treba v Exceli vyrobiť z CSV súborov. Má mať štyri karty: "Denná forma podľa mena", "Denná forma podľa poradia", "Externá forma podľa mena" a "Externá forma podľa poradia". Riadky v CSV nie sú zoradené, takže to treba spraviť v Exceli. Jednotlivé karty potom treba vyexportovať do `vysledky20XX-{den,ext}-{meno,poradie}.pdf`.
* `stat-{denna,externa,spolu}.csv` treba vyrobiť pomocou `./console.py bucketstats YYYY-MM-DD YYYY-MM-DD`.
* `flaw-vyhodnotenie.xls` treba vyrobiť zo `stat*.csv` tak, že interné ID okruhov zmeníme na mená z otázkového XML, a pridáme k tomu súčty a pekný graf. Z toho potom treba spraviť `flaw-vyhodnotenie-{denna,externa}.pdf`.


FAQ
---

Q: Ako zablokovať otázku?

A: Je potrebné zistiť v databáze číslo otázky a spustiť
`./console.py disable <pid> <qorder>`

Q: Čo robiť v prípade zrakovo postihnutých účastníkov?

A: Je potrebné im vytlačiť test veľkým fontom.
`./console.py printexamlarge <pid>`

Q: Ako vytlačiť jeden konkrétny ohodnotený test?

A: 
`./console.py printevaluatedexam <pid>`


Q: Ako pridať čas konkrétnemu účastníkovi v prípade technických porúch?

A:
`./console.py givetime <pid> <extra minutes>`

Q: Čo so zmenami v kóde?

A: Git add/commit, a push na github po skončení pohovorov.
