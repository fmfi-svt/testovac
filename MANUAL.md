Prevádzkový manuál
==================

1. Zablokovanie prihlasovania.

    ./console.py login block
2. Reštartnúť všetky počítače. Mali by zobrazovať úvodnú prihlasovaciu 
obrazovku.
3. Počkať kým koordinátor povie účastníkom všetky úvodné informácie.
4. Po signále povoliť prihlasovanie.

    ./console.py login unblock
5. Monitorovať priebeh prijímačiek - sledovať či sa účastníci prihlásili,
či sa priebežne zvyšuje počet vyplnených odpovedí, či sa testy automaticky
odovzdajú po vypršaní časového limitu.

    ./console.py monitor
Spraviť kolečko po počítačoch 
a osobne skontrolovať či sedí kód na náramku s 
kódom prihláseného.
6. Keď niekto finálne odovzdá test, spustiť príkaz na tlačenie. 

    ./console.py printfinished
Vytlačený test spárovať s titulnou stranou a scvaknúť.
7. Keď všetci odovzdajú, odniesť na podpis. 
8. Spustiť zálohu na USB kľúč.

Pre ďaľší turnus pokračovať bodom 1.

Na konci dňa exportovať vypočítané body, uploadnuť na stránka.

    ./console.py exportresults > hbody.php

Na konci prijímacích pohovorovu vytlačiť ohodnotené testy, vytlačiť
štatistiku a urobiť export do AIS.

    ./console.py bucketstats > stats.csv
    ./console.py printevaluatedexam --all

FAQ

Q: Ako zablokovať otázku?

A:

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
