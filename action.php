<?php

// TODO HTTPS check (ak je to v configu tak nastavene)

/*
akcie:
- init (vypluje HTML kostru atd, vratane obsahu konfiguraku)
- login (zvaliduje ID a stiahne zoznam otazok)
- save (uklada cely stav, mozno po kazdom kliknuti ale mozno len
  kazdych 10 sekund)
- finish (oznaci ako hotovo, posle na tlaciaren atd)
- mozno nejake heartbeatove veci ale neviem co a ako (a mozno na to staci save)

externe akcie:
- uploadovanie zoznamu otazok
- downloadovanie vysledkov
- veci okolo tlacenia

mozno chceme nejako ochranovat aby jeden nemohol byt prihlaseny na roznych
miestach (uz len preto ze JS to automaticky sejvuje a prepisovalo by sa to).
takze chceme nejake "session ID" rozdavane pri logine.

tiez treba zabezpecit nech su vsetky tie kompy zosynchronizovane cez NTP,
podla moznosti na podsekundove rozdiely. lebo pravdepodobne bude aj klient
aj server checkovat ze uz sa neda hlasovat.

btw bolo by fajn keby boli konfigurovatelne vsetky tie texty a pokyny atd.

*/


