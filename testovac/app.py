
import os
import sys
import json
from werkzeug.wrappers import Request, Response
from werkzeug.wsgi import SharedDataMiddleware
from werkzeug.exceptions import HTTPException, NotFound, BadRequest
from sqlalchemy.orm import sessionmaker
from .settings import db_connect

from . import bucketstats, disableq, front, givetime, login, models, monitor, update, printing, results, serve
site_modules = [bucketstats, disableq, front, givetime, login, models, monitor, update, printing, results, serve]


def json_response(json_object):
    return Response(json.dumps(json_object),
                    content_type='application/json; charset=UTF-8')


class TestovacApp(object):
    def __init__(self):
        self.actions = {}
        for module in site_modules:
            if hasattr(module, 'actions'):
                self.actions.update(module.actions)
        self.commands = {}
        for module in site_modules:
            if hasattr(module, 'commands'):
                self.commands.update(module.commands)

        self.db_engine = db_connect()
        self.DbSession = sessionmaker(bind=self.db_engine)

        static_dir = os.path.join(os.path.dirname(__file__), 'static')
        self.static = SharedDataMiddleware(NotFound(), { '/': static_dir })

    def run_help(self, *args):
        print 'usage:'
        for module in site_modules:
            if hasattr(module, 'commands'):
                for command in module.commands.itervalues():
                    if hasattr(command, 'help'):
                        print command.help.replace('$0', sys.argv[0])

    def run_command(self, args):
        command = args[0] if args else None
        handler = self.commands.get(command, self.run_help)
        return handler(self, *args[1:])

    def dispatch_request(self, request):
        try:
            if request.method == 'GET':
                if request.path == '/500':
                    raise Exception()   # opens the debugger if debug is True
                if request.path == '/':
                    return front.front(request)
                return self.static

            if request.method == 'POST':
                handler = self.actions.get(request.form.get('action'))
                if handler:
                    return json_response(handler(request))

            return BadRequest()

        except HTTPException as e:
            return e

    @Request.application
    def wsgi_app(self, request):
        request.app = self

        request.max_content_length = 16 * 1024 * 1024
        request.max_form_memory_size = 2 * 1024 * 1024

        request.db = self.DbSession()

        response = self.dispatch_request(request)

        request.db.close()

        return response

    def __call__(self, *args):
        return self.wsgi_app(*args)
