1. Nakopírovať do `/usr/share/xsessions`
2. Spustiť `/usr/lib/lightdm/lightdm-set-defaults --session kiosk`
3. Spustiť `chmod 775 /usr/share/xsessions/kiosk.sh`
4. Nainštalovať `sudo apt-get install numlockx`
5. V System Settings>Brightness and Lock treba nastaviť Turn screen off
when inactive for `never` a Lock nastaviť na `Off`.
