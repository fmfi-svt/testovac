#!/usr/bin/env python
import sys

print sys.stdin.readline()[:-1] + ',Graf'

for line in sys.stdin:
	fields = line.split(',')
	num = int(round(float(fields[4]) / 100 * 50))
	print (line[:-1] + ',|' + '*' * num + ' ' * (50-num) + '|') 
