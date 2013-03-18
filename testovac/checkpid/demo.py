
import re


def check(pid):
    # priklad checkera: podmienka je ze posledna cifra musi byt 7
    # (v ostrom configu sa odporuca mat nejaku lepsiu funkciu)
    return re.match(r'^[1-9][0-9]{15}$', pid) and pid[15] == '7'


def generate(demo=False):
    return '4567456745674567'
