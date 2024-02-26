<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file operand_handler.php
 * @brief Spracovanie operandov.
 *
 * @author Roman Fulla <xfulla00>
 * @date 11.03.2020
 ******************************************************************************/

class Operand {
  public function process_var(string $var, string &$instruction) {              // Spracovanie premennej
    if(!preg_match('/^(LF|TF|GF)@(_|-|\$|&|%|\*|!|\?|[a-zA-Z])(_|-|\$|&|%|\*|!|\?|[a-zA-Z0-9])*$/', $var)) {
      throw new Oth_Exp ("Nespravne zapisana premenna.");                       // Nespravne zapisana premenna
    }
    $instruction = $instruction . ' ' . 'var';                                  // Typ
    $instruction = $instruction . ' ' . $var;                                   // Hodnota
  }

  public function process_symb(string $symb, string &$instruction) {            // Spracovanie symbolu
    if      (preg_match('/^int@(\+|-)?[0-9]+$/', $symb)) {                      // Int
      $instruction = $instruction . ' ' . 'int';                                // Typ
      $instruction = $instruction . ' ' . substr($symb, 4);                     // Hodnota
    }
    else if (preg_match('/^bool@(true|false)$/', $symb)) {                      // Bool
      $instruction = $instruction . ' ' . 'bool';                               // Typ
      $instruction = $instruction . ' ' . substr($symb, 5);                     // Hodnota
    }
    else if (preg_match('/^string@([^\s#\\\\]|\\\\[0-9]{3})*$/', $symb)) {      // String
      if ($symb == 'string@') $symb = '#_EMPTY_STRING_#';                       // Prazdny string
      else                    $symb = substr($symb, 7);                         // Neprazdny string

      $instruction = $instruction . ' ' . 'string';                             // Typ
      $instruction = $instruction . ' ' . $symb;                                // Hodnota
    }
    else if (preg_match('/^nil@nil$/', $symb)) {                                // Nil
      $instruction = $instruction . ' ' . 'nil';                                // Typ
      $instruction = $instruction . ' ' . 'nil';                                // Hodnota
    }
    else if (preg_match('/^(LF|TF|GF)@(_|-|\$|&|%|\*|!|\?|[a-zA-Z])(_|-|\$|&|%|\*|!|\?|[a-zA-Z0-9])*$/', $symb)) {  // Premenna
      $instruction = $instruction . ' ' . 'var';                                // Typ
      $instruction = $instruction . ' ' . $symb;                                // Hodnota
    }
    else throw new Oth_Exp ("Nespravne zapisany symbol.");                      // Nespravne zapisany symbol
  }

  public function process_label(string $label, string &$instruction) {          // Spracovanie navestia
    if(!preg_match('/^(_|-|\$|&|%|\*|!|\?|[a-zA-Z])(_|-|\$|&|%|\*|!|\?|[a-zA-Z0-9])*$/', $label)) {
      throw new Oth_Exp ("Nespravne zapisane navestie.");                       // Nespravne zapisane navestie
    }
    $instruction = $instruction . ' ' . 'label';                                // Typ
    $instruction = $instruction . ' ' . $label;                                 // Hodnota
  }

  public function process_type(string $type, string &$instruction) {            // Spracovanie typu
    if(!preg_match('/^(int|string|bool)$/', $type)) {
      throw new Oth_Exp ("Nespravne zapisany typ.");                            // Nespravne zapisany typ
    }
    $instruction = $instruction . ' ' . 'type';                                 // Typ
    $instruction = $instruction . ' ' . $type;                                  // Hodnota
  }
}
