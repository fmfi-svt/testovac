#!/usr/bin/env python

from testovac.app import TestovacApp
application = TestovacApp()

if __name__ == '__main__':
    import sys
    sys.exit(application.run_command(sys.argv[1:]))
