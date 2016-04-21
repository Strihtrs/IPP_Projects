from asyncore import read

#JMP:xkalou03

__author__ = 'xkalou03'

import sys
import argparse
import re

import reader
import table

def checkParameters():

    parser = argparse.ArgumentParser(description = 'Projekt do IPP.', add_help = False)

    parser.add_argument('--help', action = "count", default = 0, help = 'Prints help')
    parser.add_argument('--input=', action = "store", default = [], dest = "input", nargs = 1, help = 'Input file')
    parser.add_argument('--output=', action = "store", default = [], dest = "output", nargs = 1, help = 'Output file')
    parser.add_argument('--cmd=', action = "store", default = "", nargs = '+', dest = "text", help = 'Input text')
    parser.add_argument('-r', action = "store_true", dest = "redef", default = False, help = 'Redefination macros')

    try:
        args = parser.parse_args()
    except:
        print("Parameters Error", file = sys.stderr)
        exit(1)

    if(args.help == 1):

        if len(sys.argv) == 2:
            print(parser.print_help())
            exit(0)
        else:
            print("Zadany help + jine parametry", file = sys.stderr)
            exit(1)

    return args

def readInput(input, reader, table, params):

    stringIn = ""
    rest = ""
    outputString = ""

    count = 0

    c = reader.getc()

    while c:

        if c == '@':
            x = macro(reader, table, params, c)

            if x in {'@', '{', '}', '$'}:
                outputString += x

        elif c in {'$', '}'}:
            exit(55)

        elif c == '{':

            block = reader.readBlock(False)
            if block != None:
                outputString += block

            else:

                exit(55)

        else:
            outputString += c

        c = reader.getc()

    return outputString


def macro(reader, table, params, x):

    macroString = ""
    reg = '^[a-zA-Z_][0-9a-zA-Z_]*$'
    i = 0

    c = reader.getc()

    if c in {'@', '{', '}', '$'} and x == '@':  # kontrola, zda nejde o escape sekvenci
        return c

    while c:

        i += 1
        if re.match(reg, macroString + c):

            macroString += c
            c = reader.getc()

            if not c:

                find = table.readMacro(macroString)

                if find:
                    argumentsMacro(reader, find, c, table, params)
                    c = '%'
                    break

                else:

                    print("Toto makro neexistuje", file = sys.stderr)
                    exit(56)

            continue

        else:
            # mam precten nazev - po kouknuti do tabulky budu koukat dale
            # print("Budu koukat do tabulky", macroString)

            find = table.readMacro(macroString)

            if find:
                argumentsMacro(reader, find, c, table, params)
                c = '%'
                break

            else:
                if i == 1:
                    exit(55)

                print("Toto makro neexistuje", file = sys.stderr)
                exit(56)


        macroString += c
        c = reader.getc()

    return c

def argumentsMacro(reader, find, x, table, params):

    name = ""
    temp = False

    if params.redef:
        temp = True
    c = x   # pismeno po nazvu makra

    if find.name == 'def' or find.name == '__def__':

        table.insertMacro(reader, x, temp)

    elif find.name == 'undef' or find.name == '__undef__':

        table.deleteMacro(reader, x)

    elif find.name == 'set' or find.name == '__set__':

        table.setMacro(reader, x)

    else:

        stringExpand = table.expandMacro(reader, x, find.name)
        reader.attachString(stringExpand)

    return

def main():

    params = checkParameters()

    if params.input:                           # Otevreni vstupniho souboru
        try:
            inputFile = open(params.input[0], 'r')

        except IOError:
            print("Vstupni soubor nejde otevrit", file = sys.stderr)
            exit(2)
    else:
        inputFile = sys.stdin

    if params.output:                          # Otevreni vystupniho souboru
        try:
            outputFile = open(params.output[0], 'w')

        except IOError:
            print("Soubor nejde otevrit", file = sys.stderr)
            exit(2)
    else:
        outputFile = sys.stdout

    r = reader.Reader(inputFile)    # vytvoreni readeru
    if params.text:
        r.attachString(str(params.text[0]))

    macroTable = table.Table()      # vytvoreni tabulky maker
    stringInput = readInput(inputFile, r, macroTable, params)   # spusteni cteni vstupu

    if params.output:
        print(stringInput, file = outputFile, end="")  # vytisknuti vystupu
    else:
        print(stringInput, file = sys.stdout)

if __name__ == "__main__":
   main()