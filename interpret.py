#!/usr/bin/env python3
# -*- coding: utf-8 -*-

################################################################################
# @project IPP2020
#
# @file interpret.py
# @brief Interpret XML reprezentacie IPPcode20.
#
# @author Roman Fulla <xfulla00>
# @date 15.04.2020
################################################################################

import os
import sys
from io import IOBase
from xml.etree import ElementTree

import lib.exceptions as MyExp
from lib.program import Program

MANUAL = """Napoveda:
Interpret nacita XML reprezentaciu programu a s vyuzitim vstupu
podla parametrov prikazovej riadky ho interpretuje a generuje vystup.

Podporovane parametre:
--help        -> Vypis napovedy.

--source=file -> Subor "file" s XML reprezentaciou zdrojoveho kodu.
--input=file  -> Subor "file" so vstupmi pre samotnu interpretaciu.
                 Aspon jeden z predchadzajucich parametrov je vyzadovany.

--stats=file  -> Zapis statistik do suboru "file" podla ostatnych parametrov.
                 Ak ziadne nedostane, "file" bude prazdny subor.
                 Nasledujuce parametre ho pre cinnost vyzaduju.
                 Nasledujuce parametre sa mozu opakovat.
--insts       -> Zapise do statistik pocet vykonanych instrukcii.
--vars        -> Zapise do statistik maximalny pocet inicializovanych
                 premennych pritomnych vo vsetkych platnych ramcoch."""


def main():                                                                     # Hlavna funkcia
    try:
        src_file = None                                                         # Subor s XML reprezentaciou zdrojoveho kodu
        in_file  = None                                                         # Subor so vstupmi pre samotnu interpretaciu
        stats    = None                                                         # Statisticky subor & pozadovane statistiky

        (src_file, in_file, stats) = argument_parser(src_file, in_file, stats)  # Spracovanie parametrov

        try:                                                                    # Spracovanie XML reprezentacie pomocou kniznice ElementTree
            tree = ElementTree.parse(src_file)                                  # Spracovany strom
            root = tree.getroot()                                               # Korenovy element
        except ElementTree.ParseError:                                          # Chybny XML format vstupneho suboru
            raise MyExp.FormatError('Chybny XML format vstupneho suboru.')

        if in_file:                                                             # Presmerovanie vstupneho suboru na standartny vstup
            sys.stdin = in_file

        program = Program(root)                                                 # Vytvorenie objektu program
        return_value = program.run()                                            # Vykonanie programu
        if stats:                                                               # Zapisanie statistik do statistickeho suboru
            program.stats(stats)

        sys.exit(return_value)
    except HelpException:                                                       # Zachytenie volania napovedy
        print(MANUAL)                                                           # Vypis napovedy
        sys.exit(0)
    except MyExp.MyException as my_exception:                                   # Zachytenie vynimky
        my_exception.last_words()                                               # Vypis spravy & ukoncenie interpretu so spravnou navratovou hodnotou
    finally:                                                                    # Zatvorenie otvorenych suborov
        if src_file:                                                            # Subor s XML reprezentaciou zdrojoveho kodu
            src_file.close()
        if in_file:                                                             # Subor so vstupmi pre samotnu interpretaciu
            in_file.close()
        if stats:                                                               # Statisticky subor
            stats[0].close()


def argument_parser(src_file, in_file, stats):                                  # Spracovanie parametrov
    stats_list = list()                                                         # Zoznam pozadovanych statistik

    if len(sys.argv) == 2 and sys.argv[1] == "--help":                          # Volanie napovedy
        raise HelpException()

    for arg in sys.argv[1:]:                                                    # Spracovanie parametru
        arg = arg.split("=")
        if   len(arg) == 1:                                                     # Parameter bez hodnoty
            if   arg[0] == "--insts":                                           # Statistika instrukcii
                stats_list.append("insts")
            elif arg[0] == "--vars":                                            # Statistika premennych
                stats_list.append("vars")
            elif arg[0] == "--help":                                            # Parameter "--help" nie je jediny parameter.
                raise MyExp.ArgError('Parameter "--help" nie je jediny parameter.')
            else:                                                               # Neznamy parameter bez hodnoty
                raise MyExp.ArgError('Neznamy parameter bez hodnoty.')
        elif len(arg) == 2:                                                     # Parameter s hodnotou
            if   arg[0] == "--source":                                          # Subor so zdrojovym kodom
                if src_file is None:
                    src_file = arg[1]
                else:                                                           # Viacero parametrov "--source"
                    raise MyExp.ArgError('Viacero parametrov "--source".')
            elif arg[0] == "--input":                                           # Subor so vstupmi
                if in_file is None:
                    in_file = arg[1]
                else:                                                           # Viacero parametrov "--input"
                    raise MyExp.ArgError('Viacero parametrov "--input".')
            elif arg[0] == "--stats":                                           # Statisticky subor
                if stats is None:
                    stats = arg[1]
                else:                                                           # Viacero parametrov "--stats"
                    raise MyExp.ArgError('Viacero parametrov "--stats".')
            else:                                                               # Neznamy parameter s hodnotou
                raise MyExp.ArgError('Neznamy parameter s hodnotou.')
        else:                                                                   # Neznamy parameter
            raise MyExp.ArgError('Neznamy parameter.')

    try:                                                                        # Spracovanie vstupnych suborov
        if   src_file is None and in_file is None:                              # Nebol zadany ani jeden vstupny subor
            raise MyExp.ArgError('Aspon jeden z parametrov "--source"/"--input" musi byt zadany.')
        elif src_file is None:                                                  # Nebol zadany zdrojovy kod
            src_file = sys.stdin
            in_file  = open(in_file, "r")
        elif in_file is None:                                                   # Nebol zadany subor so vstupmi
            src_file = open(src_file, "r")
            in_file  = sys.stdin
        else:                                                                   # Boli zadane obidva vstupne subory
            src_file = open(src_file, "r")
            in_file  = open(in_file,  "r")
    except OSError:                                                             # Niektory vstupny subor sa nepodarilo otvorit pre citanie
        if isinstance(src_file, IOBase):                                        # Podarilo sa otvorit zdrojovy subor ale nie subor so vstupmi
            src_file.close()
        raise MyExp.InError('Niektory vstupny subor sa nepodarilo otvorit pre citanie.')

    try:                                                                        # Spracovanie vystupnych suborov
        if   stats_list and stats is None:                                      # Pozadovane statistiky bez statistickeho suboru
            raise MyExp.ArgError('Pozadovane statistiky bez statistickeho suboru.')
        elif stats is not None:                                                 # Boli pozadovane statistiky
            stats = open(stats, "w")
            stats = [stats]                                                     # Zmena na zoznam
            stats.extend(stats_list)                                            # Pridanie pozadovanych statistik
    except OSError:                                                             # Niektory vystupny subor sa nepodarilo otvorit pre zapis
        src_file.close()
        in_file.close()
        raise MyExp.OutError('Niektory vystupny subor sa nepodarilo otvorit pre zapis.')

    return (src_file, in_file, stats)


class HelpException(Exception):                                                 # Vynimka napovedy
    pass


if __name__ == "__main__":                                                      # Vykonanie hlavnej funkcie
    main()
