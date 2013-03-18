# -*- coding: utf-8 -*-

import os
import json
from werkzeug.wrappers import Response
from . import settings


def front(request):
    return Response('Hello', content_type='text/html; charset=UTF-8')
