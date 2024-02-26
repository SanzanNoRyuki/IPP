<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file ippcode20_handler.php
 * @brief Pracovanie s IPPcode20.
 *
 * @author Roman Fulla <xfulla00>
 * @date 11.03.2020
 ******************************************************************************/

require_once __DIR__ . '/operand_handler.php';

class Code {
  private int $loc = 0, $comments = 0, $labels = 0, $jumps = 0;                 // Statistiky
  private string $instructions = '';                                            // Instrukcie programu

  public function parse_and_check() {                                           // Syntakticka a lexikalna analyza kodu
    $header_flag = false;                                                       // Priznak hlavicky
    $labels = array();                                                          // Pole navesti

    while (($line = fgets(STDIN)) !== false) {                                  // Nacitanie vstupu po riadkoch
      if     (preg_match('/^\s*$/', $line)) {                                   // Prazdny riadok
        continue;
      }
      else if (preg_match('/^\s*#/', $line)) {                                  // Riadok so samostatnym komentarom
        $this->comments++;
        continue;
      }
      else if (preg_match('/^\s*\.IPPcode20\s*($|#)/i', $line)) {               // Hlavicka
        if ($header_flag) throw new Op_Exp ("Viacero hlaviciek.");              // Viacero hlaviciek

        if (preg_match('/^\s*\.IPPcode20\s*#/i', $line)) $this->comments++;

        $header_flag = true;
        continue;
      }
      else if (!$header_flag) {                                                 // Chybna alebo chybajuca hlavicka
        throw new Hed_Exp ("Chybna alebo chybajuca hlavicka.");
      }

      if ($comment = strpos($line, '#')) {                                      // Komentar za instrukciou
        $this->comments++;
        $line = substr($line, 0, $comment);                                     // Odstranenie komentara
      }

      $line = trim($line);                                                      // Zbavenie sa medzier
      $line = preg_split('/\s+/', $line);                                       // Rozdelenie riadku na pole slov

      $opcode = strtoupper(current($line));                                     // Spracovanie operacneho kodu
      $instruction = $opcode;

      switch($opcode) {                                                         // Spracovanie instrukcie
        case 'CREATEFRAME':                                                     // Instrukcie bez operandov
        case 'PUSHFRAME':
        case 'POPFRAME':
        case 'RETURN':
        case 'BREAK':
          if (count($line) == 1) break;
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'DEFVAR':                                                          // Instrukcie s operandom <var>
        case 'POPS':
          if (count($line) == 2) {
            Operand::process_var(next($line), $instruction);                    // Spracovanie premennej
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'DPRINT':                                                          // Instrukcie s operandom <symb>
        case 'EXIT':
        case 'WRITE':
        case 'PUSHS':
          if (count($line) == 2) {
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'CALL':                                                            // Instrukcie s operandom <label>
        case 'LABEL':
        case 'JUMP':
          if (count($line) == 2) {
            Operand::process_label(next($line), $instruction);                  // Spracovanie navestia
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'MOVE':                                                            // Instrukcie s operandami <var> a <symb>
        case 'NOT':
        case 'INT2CHAR':
        case 'STRLEN':
        case 'TYPE':
          if (count($line) == 3) {
            Operand::process_var(next($line), $instruction);                    // Spracovanie premennej
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'READ':                                                            // Instrukcie s operandami <var> a <type>
          if (count($line) == 3) {
            Operand::process_var(next($line), $instruction);                    // Spracovanie premennej
            Operand::process_type(next($line), $instruction);                   // Spracovanie typu
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'ADD':                                                             // Instrukcie s operandami <var>, <symb1> a <symb2>
        case 'SUB':
        case 'MUL':
        case 'IDIV':
        case 'LT':
        case 'GT':
        case 'EQ':
        case 'AND':
        case 'OR':
        case 'STRI2INT':
        case 'CONCAT':
        case 'GETCHAR':
        case 'SETCHAR':
          if (count($line) == 4) {
            Operand::process_var(next($line), $instruction);                    // Spracovanie premennej
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        case 'JUMPIFEQ':                                                        // Instrukcie s operandami <label>, <symb1> a <symb2>
        case 'JUMPIFNEQ':
          if (count($line) == 4) {
            Operand::process_label(next($line), $instruction);                  // Spracovanie navestia
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            Operand::process_symb(next($line), $instruction);                   // Spracovanie symbolu
            break;
          }
          else throw new Oth_Exp ("Nespravny pocet operandov.");                // Nespravny pocet operandov
        default:                                                                // Neznamy operacny kod
          throw new Op_Exp ("Neznamy operacny kod.");
      }

      if      ($opcode === 'RETURN'   ||                                        // Pocitanie skokov
               $opcode === 'CALL'     ||
               $opcode === 'JUMP'     ||
               $opcode === 'JUMPIFEQ' ||
               $opcode === 'JUMPIFNEQ'  ) {
                 $this->jumps++;
      }
      else if ($opcode === 'LABEL') {                                           // Pocitanie navesti
        if (empty($labels) || !in_array(substr($instruction, 6), $labels, true)) {
          $labels[] = substr($instruction, 6);                                  // Navestie je unikatne
          $this->labels++;
        }
      }
      $this->loc++;                                                             // Pocitanie riadkov kodu

      $this->instructions = $this->instructions . $instruction . "\n";          // Pridanie spracovanej instrukcie do instrukcii programu
    }

    if (!$header_flag) throw new Hed_Exp ("Chybajuca hlavicka.");               // Chybajuca hlavicka
  }

  public function generate_stats($stats) {                                      // Generovanie statistik
    if (!$stats) return;                                                        // Statistika nebola pozadovana

    $handle = fopen($stats[0], 'w');                                            // Statisticky subor
    if (!$handle) throw new Out_Exp ("Neotvoritelny subor STATS.");             // Neotvoritelny subor STATS

    $new_line = 0;
    foreach($stats as $stat) {                                                  // Zapis do statistickeho suboru
      switch($stat) {
        case '--loc':
          fwrite($handle, "$this->loc");
          break;
        case '--comments':
          fwrite($handle, "$this->comments");
          break;
        case '--labels':
          fwrite($handle, "$this->labels");
          break;
        case '--jumps':
          fwrite($handle, "$this->jumps");
          break;
        default:
          break;
      }

      $new_line++;                                                              // Zapis konca riadkov (\n)
      if ($new_line == 1)             continue;
      if ($new_line == count($stats)) break;
      fwrite($handle, "\n");
    }

    fclose($handle);                                                            // Zatvorenie statistickeho suboru
  }

  public function generate_XML() {                                              // Generovanie XML reprezentacie IPPcode20
    $xml = new SimpleXMLElement('<program></program>');
    $xml->addAttribute('language', 'IPPcode20');

    $instructions = explode("\n", $this->instructions);                         // Rozdelenie instrukcii na jednotlive
    array_pop($instructions);                                                   // Posledna instrukcia je prazdna

    $inst_order = 0;                                                            // Poradie instrukcie
    foreach($instructions as $instruction) {
      $instruction = explode(' ', $instruction);                                // Rozdelenie instrukcie na pole - Operacny kod Typ Hodnota Typ Hodnota...

      $opcode = $instruction[0];                                                // Operacny kod

      $inst = $xml->addChild('instruction');                                    // Generovanie instrukcie
      $inst->addAttribute('order' , strval(++$inst_order));
      $inst->addAttribute('opcode', $opcode);

      $arg_order = 0;                                                           // Poradie parametru
      for($i = 1; $i < count($instruction) - 1; $i = $i + 2) {                  // Generovanie parametrov instrukcie
        if ($instruction[$i + 1] === '#_EMPTY_STRING_#') {                      // Parameter je prazdny string
          $arg = $inst->addChild('arg' . ++$arg_order);
        }
        else {                                                                  // Nasledujuci prvok v poli je hodnota
          $arg = $inst->addChild('arg' . ++$arg_order, htmlspecialchars($instruction[$i + 1]));
        }

        $arg->addAttribute('type', $instruction[$i]);                           // Sucasny prvok v poli je typ
      }
    }

    $dom = dom_import_simplexml($xml)->ownerDocument;                           // Formatovanie XML vystupu pomocou nastroja DOM
    $dom->encoding = 'utf-8';
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    echo $dom->saveXML();                                                       // Vypis na standartny vystup
  }
}
