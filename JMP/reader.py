__author__ = 'Filip'


class Reader:

    inputString = ""
    inputFile = None
    whiteSpace = False

    def __init__(self, input):

        self.inputFile = input

    def getc(self):

        if len(self.inputString):

            c = self.inputString[0]

            if c.isspace() and self.whiteSpace == True:

                while c.isspace() and self.whiteSpace == True:
                    self.inputString = self.inputString[1:]
                    c = self.inputString[0]

            else:
                self.inputString = self.inputString[1:]

        else:

            c = self.inputFile.read(1)
            while c.isspace() and self.whiteSpace == True:
                c = self.inputFile.read(1)

        return c

    def attachString(self, attach):

        self.inputString = self.inputString + attach

    def changeSpaces(self, change):

        self.whiteSpace = change

    def readBlock(self, eF):

        count = 1
        blockStr = ""
        c = Reader.getc(self)
        while c:

            if c == '{':
                count += 1

            elif c == '}':
                count -= 1
                if count == 0:
                    break

            elif c == '@':
                x = Reader.getc(self)
                if x in {'{', '}', '@', '$'} and eF == False:
                    blockStr += x
                    c = Reader.getc(self)
                    continue
                else:
                    blockStr += c
                    blockStr += x
                    c = Reader.getc(self)
                    continue

            blockStr += c

            c = Reader.getc(self)

        if count != 0:
            return None

        return blockStr