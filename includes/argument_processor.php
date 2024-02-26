<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file argument_processor.php
 * @brief Spracovanie parametrov skriptu.
 *
 * @author Roman Fulla <xfulla00>
 * @date 09.03.2020
 ******************************************************************************/

class Argument {
  public function process(int $argc, array $argv, string $manual) {             // Spracovanie parametrov
    $arguments = array();                                                       // Pole spracovanych parametrov

    if      ($argc == 1) return $arguments;                                     // Skript bez parametrov
    else if ($argc == 2 && $argv[1] === '--help') {                             // Vypis napovedy
      echo $manual;
      exit(0);
    }
    else if ($argv[0] === 'parse.php') {                                        // Spracovanie parametrov pre parse.php
      array_shift($argv);                                                       // Preskocenie prveho prvku

      foreach($argv as $arg) {
        if ($arg === '--loc'      ||                                            // Statisticke parametre
            $arg === '--comments' ||
            $arg === '--labels'   ||
            $arg === '--jumps'      ) {
                   $arguments[] = $arg;
        }
        else if (substr($arg, 0, 8) === '--stats=') {                           // Parameter STATP
          if (!empty($arguments) && $arguments[0] != '--loc'      &&            // Viacero statistickych suborov
                                    $arguments[0] != '--comments' &&
                                    $arguments[0] != '--labels'   &&
                                    $arguments[0] != '--jumps'      ) {
                throw new Arg_Exp ("Viacero statistickych suborov.");
              }

          $stat_file = substr($arg, 8);
          if ($arguments) array_unshift($arguments, $stat_file);                // Umiestnenie suboru na prve miesto pola
          else $arguments[] = $stat_file;
        }
        else throw new Arg_Exp ("Neznamy parameter.");                          // Neznamy parameter
      }

      if (empty($arguments) || $arguments[0] === '--loc'      ||                // Nebol nastaveny statisticky subor
                               $arguments[0] === '--comments' ||
                               $arguments[0] === '--labels'   ||
                               $arguments[0] === '--jumps'      ) {
            throw new Arg_Exp ("Nebol nastaveny statisticky subor.");
      }

      return $arguments;                                                        // stat_file [--loc] [--comments] [--labels] [--jumps] ...
    }
    else if ($argv[0] === 'test.php') {                                         // Spracovanie parametrov pre test.php
      array_shift($argv);                                                       // Preskocenie prveho prvku

      foreach($argv as $arg) {
        //TBA
      }

      //TBA

      return $arguments;
    }
    else throw new Arg_Exp ("Nepodporovana kombinacia parametrov.");            // Nepodporovana kombinacia parametrov
  }
}
