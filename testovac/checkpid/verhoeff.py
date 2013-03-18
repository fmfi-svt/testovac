
import re
import random


D = (
    (0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
    (1, 2, 3, 4, 0, 6, 7, 8, 9, 5),
    (2, 3, 4, 0, 1, 7, 8, 9, 5, 6),
    (3, 4, 0, 1, 2, 8, 9, 5, 6, 7),
    (4, 0, 1, 2, 3, 9, 5, 6, 7, 8),
    (5, 9, 8, 7, 6, 0, 4, 3, 2, 1),
    (6, 5, 9, 8, 7, 1, 0, 4, 3, 2),
    (7, 6, 5, 9, 8, 2, 1, 0, 4, 3),
    (8, 7, 6, 5, 9, 3, 2, 1, 0, 4),
    (9, 8, 7, 6, 5, 4, 3, 2, 1, 0),
)

P = (
    (0, 1, 2, 3, 4, 5, 6, 7, 8, 9),
    (1, 5, 7, 6, 2, 8, 3, 0, 9, 4),
    (5, 8, 0, 3, 7, 9, 6, 1, 4, 2),
    (8, 9, 1, 6, 0, 4, 3, 5, 2, 7),
    (9, 4, 5, 3, 1, 2, 6, 8, 7, 0),
    (4, 2, 8, 6, 5, 7, 3, 9, 0, 1),
    (2, 7, 9, 3, 8, 0, 6, 4, 1, 5),
    (7, 0, 4, 6, 9, 1, 3, 2, 5, 8),
)

INV = (0, 4, 3, 2, 1, 5, 6, 7, 8, 9)


def calculate(digits):
    c = 0
    for i, digit in enumerate(reversed(digits)):
        c = D[c][P[i%8][int(digit)]]
    return c


_check_re = re.compile(r'^[0-9]{4}(?:-[0-9]{4}){3}$')
def check(pid):
    if not _check_re.match(pid): return False
    pid = pid.replace('-', '')
    return calculate(pid) == 0


def generate(demo=False):
    digits = [random.randint(0, 9) for i in range(15)]
    if demo:
        # demo: tretia a osma cifra maju sucet 8
        digits[2] = random.randint(0, 8)
        digits[7] = 8 - digits[2]
    else:
        # rocnik 2012: tretia a osma cifra maju sucet 12
        digits[2] = random.randint(3, 9)
        digits[7] = 12 - digits[2]
    digits.append(INV[calculate(digits + [0])])
    string = ''.join(map(str, digits))
    return '-'.join([string[0:4], string[4:8], string[8:12], string[12:16]])
