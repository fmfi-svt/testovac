# we get screen resolution

res=$(xrandr -q | awk -F'current' -F',' 'NR==1 {gsub("( |current)","");print $2}')
resx=$(echo $res | awk '{split($0,array,"x")} END{print array[1]}')
resy=$(echo $res | awk '{split($0,array,"x")} END{print array[2]}')

# starting xscreensaver

xscreensaver -nosplash &

xmodmap -e 'keycode 64 = NoSymbol'
xmodmap -e 'keycode 67 = NoSymbol'
xmodmap -e 'keycode 68 = NoSymbol'
xmodmap -e 'keycode 69 = NoSymbol'
xmodmap -e 'keycode 70 = NoSymbol'
xmodmap -e 'keycode 71 = NoSymbol'
xmodmap -e 'keycode 72 = NoSymbol'
xmodmap -e 'keycode 73 = NoSymbol'
xmodmap -e 'keycode 74 = NoSymbol'
xmodmap -e 'keycode 75 = NoSymbol'
xmodmap -e 'keycode 76 = NoSymbol'
xmodmap -e 'keycode 95 = NoSymbol'
xmodmap -e 'keycode 96 = NoSymbol'
#xmodmap -e 'keycode 77 = NoSymbol'
xmodmap -e 'keycode 78 = NoSymbol'
xmodmap -e 'keycode 108 = NoSymbol' 
xmodmap -e 'keycode 48 = NoSymbol' 
xmodmap -e 'keycode 135 = NoSymbol' 
#xmodmap -e 'keycode 77 = NoSymbol' 
xmodmap -e 'keycode 107 = NoSymbol' 
xmodmap -e 'keycode 218 = NoSymbol' 
xmodmap -e "remove control = Control_R"
xmodmap -e "remove control = Control_L"
#xmodmap -e "remove control = F1"
#xmodmap -e "remove control = F2"
#xmodmap -e "remove control = F3"
#xmodmap -e "remove control = F4"
#xmodmap -e "remove control = F5"
#xmodmap -e "remove control = F6"
#xmodmap -e "remove control = F7"
#xmodmap -e "pointer = 1 2 32 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 3"
#xmodmap -e "keycode 117 =" 
xmodmap -e "pointer = 1 2 11"

while true;
        do chromium-browser http://prijimacky.flaw.uniba.sk/demo/ %u --no-first-run --disable-translate --disable-new-tab-first-run --kiosk --window-size=$resx,$resy
        sleep 5s;
done
