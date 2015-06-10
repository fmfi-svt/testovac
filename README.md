
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
  - Remote shutdown/reboot
  - Vypnut screensaver (aby nezhasinala obrazovka)
  - Zabezpecit, aby bol zapnuty numlock

### Testovac
  - Doplnanie pomlciek a zobrazenie vzoru
  - Kontrola roku v PIDoch

### console.py
  - Autocomplete prikazov
