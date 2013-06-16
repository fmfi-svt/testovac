#!/usr/bin/env python
# -*- coding: utf-8 -*-


stickers_per_page = 13 * 3


import os
import sys
import time
from Crypto.Random import random
from subprocess import check_call


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


def calculate_verhoeff(digits):
    c = 0
    for i, digit in enumerate(reversed(digits)):
        c = D[c][P[i % 8][int(digit)]]
    return c


def generate_pid():
    digits = [
        random.randint(1, 9),  # nechceme aby prva cifra bola 0 (mozno by ju potom niekto neopisal)
        random.randint(0, 9),
        0, 0  # tretiu a stvrtu cifru neskor dopocitame
    ]
    digits.extend([random.randint(0, 9) for i in xrange(11)])
    digits.append(0)  # sestnastu cifru dopocitame neskor (kontrolny sucet)

    # stvrta cifra + osma cifra === rok (modulo 10)
    digits[3] = (time.localtime().tm_year - digits[7]) % 10

    # tretia cifra + osma cifra != 8
    if digits[7] > 8:
        digits[2] = random.randint(0, 9)
    else:
        avoid = 8 - digits[7]
        digits[2] = random.randint(1, 9)
        if digits[2] <= avoid:
            digits[2] -= 1

    # sestnasta cifra je kontrolny sucet
    digits[15] = INV[calculate_verhoeff(digits)]

    return ''.join(map(str, digits))


def uniq_pid_generator():
    previous_pids = set()
    while True:
        new = generate_pid()
        if new not in previous_pids:
            previous_pids.add(new)
            yield new
        else:
            print 'Skipping duplicated pid %s.' % new


def generate_stickers(stickers_needed):
    pages = stickers_needed / stickers_per_page
    if stickers_needed % stickers_per_page > 0:
        pages += 1

    count = pages * stickers_per_page

    pid_generator=uniq_pid_generator()
    with open('result.txt', 'w') as f:
        for i in xrange(count):
            f.write(pid_generator.next())
            f.write('\n')

    with open('result.txt', 'r') as input_file:
        with open('stickers.tex', 'w') as output_file:
            check_call(['php', 'barQrCode3.php'], stdin=input_file, stdout=output_file)

    check_call(['pdflatex', '--shell-escape', 'stickers.tex'])
    os.remove('stickers.aux')
    os.remove('stickers.log')
    os.remove('stickers-pics.pdf')
    os.remove('stickers.tex')


def usage():
    print '%s stickers_needed' % sys.argv[0]
    print '\t stickers_needed: positive integer'
    sys.exit(2)


if __name__ == '__main__':
    if len(sys.argv) == 2:
        try:
            stickers_needed = int(sys.argv[1])
        except ValueError:
            print 'Error: Unable to parse argument "%s" as integer.\n' % sys.argv[1]
            usage()
        if stickers_needed > 0:
            generate_stickers(stickers_needed)
        else:
            print 'Error: Got "%s" but expecting positive integer.\n' % sys.argv[1]
            usage()
    else:
        usage()
