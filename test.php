<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file test.php
 * @brief Testovaci skript pre parse.php a interpret.py.
 *
 * @author Roman Fulla <xfulla00>
 * @date 10.03.2020
 ******************************************************************************/

require_once __DIR__ . '/includes/argument_processor.php';
//TBA
require_once __DIR__ . '/includes/exception_handler.php';

const MANUAL =                                                                  // Napoveda
'Napoveda:
Skript testuje...                                                               //TBA
';

try {                                                                           // Skript
  if (!fopen('php://stderr', 'w')) exit(12);
  //TBA

  $arguments = Argument::process($argc, $argv, MANUAL);                         // Spracovanie parametrov skriptu

  //TBA

  /*
     throw new Int_Exp("Interna chyba na riadku $this->line_num.");
     TBA Vynimky testovacieho ramca
  */

  exit(0);                                                                      // Ukoncenie s navratovou hodnotou 0
} catch (My_Exp $exception) {
  $exception->final_breath();                                                   // Vypis spravy & ukoncenie skriptu so spravnou navratovou hodnotou
}
  catch (Exception $exception) {                                                // Neocakavana chyba
  exit(99);                                                                     //TBA Exception => Error
}
