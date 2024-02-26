<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file parse.php
 * @brief Prepisovac kodu IPPcode20 na jeho XML reprezentaciu.
 *
 * @author Roman Fulla <xfulla00>
 * @date 11.03.2020
 ******************************************************************************/

require_once __DIR__ . '/includes/argument_processor.php';
require_once __DIR__ . '/includes/ippcode20_handler.php';
require_once __DIR__ . '/includes/exception_handler.php';

const MANUAL =                                                                  // Napoveda
'Napoveda:
Skript typu filter nacita zo standartneho vstupu zdrojovy kod v IPPcode20,
zkontroluje lexikalnu a syntakticku spravnost kodu a vypise na standartny
vystup XML reprezentaciu programu podla specifikacie.

Podporovane parametre:
--help       -> Vypis napovedy.

--stats=file -> Zapis statistik do suboru "file" podla ostatnych parametrov.
                Ak ziadne nedostane, "file" bude prazdny subor.
                Nasledujuce parametre ho pre cinnost vyzaduju.
                Nasledujuce parametre sa mozu opakovat.
--loc        -> Vypise do statistik pocet riadkov s instrukciami.
--comments   -> Vypise do statistik pocet riadkov s komentarom.
--labels     -> Vypise do statistik pocet definovanych navesti.
--jumps      -> Vypise do statistik pocet skokovych instrukcii.';

try {
  $stats = Argument::process($argc, $argv, MANUAL);                             // Spracovanie parametrov skriptu

  $code = new Code();
  $code->parse_and_check();                                                     // Syntakticka a lexikalna analyza kodu
  $code->generate_stats($stats);                                                // Generovanie statistik
  $code->generate_XML();                                                        // Generovanie XML reprezentacie IPPcode20

  exit(0);                                                                      // Ukoncenie s navratovou hodnotou 0
} catch (My_Exp $exception) {                                                   // Zachytenie vynimky
  $exception->final_breath();                                                   // Vypis spravy & ukoncenie skriptu so spravnou navratovou hodnotou
}
