
Testovač EPV
------------

Jednotlivé vetvy repozitára obsahujú rôzne časti projektu:

* `info-page`: obsah informačnej stránky prijimacky.flaw.uniba.sk
* `c-server`: aplikácia na registráciu uchádzačov a zadávanie kódov
* `h-server`: aplikácia na samotné vypĺňanie testu a vyhodnocovanie
* `generator-nalepiek`: generuje nálepky s kódmi

Na prácu s viacerými časťami sa odporúča vyrobiť si na každú samostatný clone
repozitára, a v každom clone checkoutnúť inú vetvu.

(Iná možnosť je pomocou [git-new-workdir][] spraviť viacero pracovných
adresárov používajúcich jeden repozitár. V každom workdire sa potom dá
checkoutnúť inú vetvu alebo commit, ale stále budú zdieľať spoločný commitový
graf. Len si treba dať pozor, aby nebola jedna vetva checkoutnutá vo viacerých
workdiroch. V bežných prípadoch sa väčšinou viac hodia samostatné clones.)

  [git-new-workdir]: http://nuclearsquid.com/writings/git-new-workdir/


ToDo
----

### Image
  - Remote shutdown/reboot (asi UDP nech mozeme broadcastovat) (a ak sa da, iba ak neprebieha test)
  - Remote guest login (asi UDP nech mozeme broadcastovat)
  - Vypnut screensaver (aby nezhasinala obrazovka)
  - Zabezpecit, aby bol zapnuty numlock
  - Zabezpecit, aby WakeOnLan prezil restart, vid [ArchLinux Wiki](https://wiki.archlinux.org/index.php/Wake-on-LAN)

### Testovac
  - Doplnanie pomlciek (zvazit) a zobrazenie vzoru
  - Kontrola roku v PIDoch
  - Autocomplete prikazov console.py
  - Monitor ma asi nejaky problem, ze ak su viaceri useri co nemaju ziadne eventy, nezobrazi vsetkych

### Info page
  - Mat v info page nejaku utilitu, co skontroluje ze cbody.php a hbody.php maju rovnaku mnozinu PIDov.
  - Pridat vo vysledky.php exception pre pripady, ze PID je v cbody.php a nie v hbody.php (opacnu uz mame).

### C server
  - Otestovat zmenu 04ddc8 v C serveri - importStudents maze CR aj LF (predtym sa v databaze ukladalo ^M vo forma_studia)

### H server (fyzicky)
  - Zvazit dalsi monitor
