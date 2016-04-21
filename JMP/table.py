__author__ = 'Filip'

import sys
import re

class Macro:

    name = ""
    argc = 0
    args = []
    protected = False
    expand = ""


    def __init__(self, name, argc, protected, expand, args):

        self.name = name
        self.argc = argc
        self.protected = protected
        self.args = args
        self.expand = expand


class Table:

    macros = {}

    def __init__(self):

        self.macros = {'__def__': Macro('__def__', 3, True, "", ""),
                       '__undef__': Macro('__undef__', 1, True, "", ""),
                       '__set__': Macro('__set__', 1, True, "", ""),
                       'def': Macro('def', 3, False, "", ""),
                       'undef': Macro('undef', 1, False, "", ""),
                       'set': Macro('set', 1, False, "", "")}

    def insertMacro(self, reader, x, protected):

        c = x
        name = ""
        args = []
        argc = 0
        count = 2
        temp = ""
        makroStr = ""
        reg = '^[a-zA-Z_][0-9a-zA-Z_]*$'

        c = x
        if c == '@':
            # zapsani jmena
            c = reader.getc()

            while c != '{':

                name += c
                c = reader.getc()

            if re.match(reg, name) == None:
                exit(55)

            # predelat na cteni bloku pomoci readeru!!!
            # vyhledani 2. argumentu

            temp = reader.readBlock(False)

            args = re.findall('\$\w+', temp)

            if len(temp) != 0 and len(args) == 0:
                exit(55)

            for i in range(0, len(args)):

                if re.match('^\$[a-zA-Z_][0-9a-zA-Z_]*$', args[i]):
                    argc += 1
                else:

                    exit(55)

            # vyhledani 3. argumentu
            c = reader.getc()

            if c == '{':
                makroStr = reader.readBlock(True)
            else:
                exit(55)

            # dodelat vkladani args a 3. argumentu

            self.macros.update({name: Macro(name, argc, protected, makroStr, args)})

        return

    def deleteMacro(self, reader, x):

        name = ""

        if x == '@':

            c = reader.getc()
            while c:

                if c in {'@', '{'}:
                    reader.attachString(c)
                    break
                else:
                    name += c

                c = reader.getc()

            deleteMacro = self.readMacro(name)
            if deleteMacro != None:
                if deleteMacro.protected == False:

                    self.macros.pop(name, None)
                else:
                    print("Toto makro nejde zrusit", file = sys.stderr)
                    exit(57)


        return ""

    def readMacro(self, macroName):

        if macroName in self.macros:

            return self.macros[macroName]

        else:
            return None


    def setMacro(self, reader, x):

        arg = ""

        if x == '{':

            c = reader.getc()
            while c:
                if c != '}':
                    arg += c
                else:
                    break

                c = reader.getc()

            if arg == '+INPUT_SPACES':
                reader.changeSpaces(False)

            elif arg == '-INPUT_SPACES':
                reader.changeSpaces(True)

            else:
                print("Nepovoleny argument k makru set", file = sys.stderr)
                exit(56)

        return

    def expandMacro(self, reader, c, name):

        expandMacro = self.macros.get(name)     # nacteni vyhledaneho makra
        temp = []                               # pole na ukladani nactenych parametru
        count = 0                               # pocet nactenych argumentu
        stringOut = ""
        dictMacros = {}
        cc = 0

        while c:

            if c == '{':
                count += 1
                temp.insert(cc, reader.readBlock(False))
                cc += 1
                c = reader.getc()

            else:
                break

        if (expandMacro.argc == 0 and count > 0) and (expandMacro != count):
            exit(56)

        for i in range(0, len(expandMacro.args)):

            dictMacros.update({expandMacro.args[i]: temp[i]})

        i = 0
        stringOut = expandMacro.expand
        for key in dictMacros.keys():

            stringOut = re.sub('\\' + key, dictMacros.get(key), stringOut)
            i += 0

        return stringOut + c







