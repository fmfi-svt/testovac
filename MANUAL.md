Prevádzkový manuál
==================

C-server
--------
1. Po každej registrácií treba spustiť `./batch.sh print <den><cislo>` napr.
`./batch.sh print Pon1`
2. Na konci dňa `./batch.sh export <den>` napr. `./batch.sh export Pon` 


H-server
--------

1. Zablokovanie prihlasovania.

    ./console.py login block
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

    `./console.py monitor`
Spraviť kolečko po počítačoch 
a osobne skontrolovať či sedí kód na náramku s 
kódom prihláseného.
6. Keď niekto finálne odovzdá test, spustiť príkaz na tlačenie. 

    `utils/printfinished <den><skupina>`
    napr. 
    `utils/printfinished Pon1`
Vytlačený test spárovať s titulnou stranou a scvaknúť.
7. Keď všetci odovzdajú, odniesť na podpis. 
8. Spustiť zálohu na USB kľúč.
    `utils/backup <den><skupina>`
    napr. 
    `utils/backup Pon1`

Pre ďaľší turnus pokračovať bodom 1.

Na konci dňa exportovať vypočítané body, uploadnuť na stránku.

    ./console.py exportresults > hbody.php

Na konci prijímacích pohovorov vytlačiť ohodnotené testy, vytlačiť
štatistiku a urobiť export do AIS.

    ./console.py bucketstats > stats.csv
    ./console.py printevaluatedexam --all

FAQ

Q: Ako zablokovať otázku?

A: Je potrebné zistiť v databáze číslo otázky a spustiť

    ./console.py disable <pid> <qorder>

Q: Čo robiť v prípade zrakovo postihnutých účastníkov?

A: Je potrebné im vytlačiť test veľkým fontom.

    ./console.py printexamlarge <pid>

Q: Ako vytlačiť jeden konkrétny ohodnotený test?

A: 

    ./console.py printevaluatedexam <pid>


Q: Ako pridať čas konkrétnemu účastníkovi v prípade technických porúch?

A:

    ./console.py givetime <pid> <extra minutes>

Q: Čo so zmenami v kóde?

A: Git add/commit, a push na github po skončení pohovorov.
