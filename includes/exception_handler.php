<?php
declare(strict_types=1);

/******************************************************************************
 * @project IPP2020
 *
 * @file exception_handler.php
 * @brief Spracovanie vynimiek.
 *
 * @author Roman Fulla <xfulla00>
 * @date 11.03.2020
 ******************************************************************************/

class My_Exp extends Exception {                                                // Standartna vynimka
  private string $msg;                                                          // Sprava
  protected $rvl = 99;                                                          // Navratova hodnota

  public function __construct(string $msg) {                                    // Konstruktor
    $this->msg = $msg;
  }

  public function final_breath() {                                              // Vypis spravy & ukoncenie skriptu so spravnou navratovou hodnotou
    fwrite(STDERR, "Chyba: \"" . $this->msg . "\" (" .                          // Chyba: "Sprava" (subor.php[riadok])
                   basename($this->getFile()) .
                   "[" . $this->getLine() . "])\n");
    array_map('fclose', get_resources('stream'));                               // Zatvorenie otvorenych suborov
    die($this->rvl);
  }
}

class Arg_Exp extends My_Exp {                                                  // Vynimka parametrov
  protected $rvl = 10;
}

class In_Exp extends My_Exp {                                                   // Vynimka vstupnych suborov
  protected $rvl = 11;
}

class Out_Exp extends My_Exp {                                                  // Vynimka vystupnych suborov
  protected $rvl = 12;
}

class Int_Exp extends My_Exp {                                                  // Interna vynimka
  protected $rvl = 99;
}

class Hed_Exp extends My_Exp {                                                  // Vynimka hlavicky
  protected $rvl = 21;
}

class Op_Exp extends My_Exp {                                                   // Vynimka operacneho kodu
  protected $rvl = 22;
}

class Oth_Exp extends My_Exp {                                                  // Ina vynimka zdrojoveho kodu
  protected $rvl = 23;
}
