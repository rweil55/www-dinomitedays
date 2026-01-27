class PrintDinos {
private $dinos;

public function __construct($dinos) {
$this->dinos = $dinos;
}

public function printDinos() {
global $wpdbExtra;

$html = "https://dinomitedays.org/designs/aiken.htm";
printDino($html);
}
public function printDino($html) {
msg .= "Printing Dino $html<br>";

Win = window.open($html, "newWindow");
win.print();
return:


} // end of printDinos
} // end of class PrintDinos
}