<?php

// Uključujemo JSONHandler i FileHandler klase
require_once 'jsonHandlerClass.php';
require_once 'fileHandlerClass.php';

// Putanja do CSV fajla
$csvFile = 'books.csv';

// Putanja gde ćemo snimiti JSON fajl
$jsonFile = 'books.json';

// Kreiramo instance JSONHandler i FileHandler klasa
$jsonHandler = new JSONHandler();
$fileHandler = new FileHandler();

// Proveravamo da li CSV fajl postoji i koristimo FileHandler za njegovo učitavanje
if ($fileHandler->fileExists($csvFile)) {
    $csvContent = $fileHandler->loadFile($csvFile);
    $lines = explode(PHP_EOL, $csvContent);
    
    $header = str_getcsv(array_shift($lines)); // Čitanje zaglavlja (naslovi kolona)

    // Učitavanje podataka iz CSV i dodavanje u JSON strukturu
    foreach ($lines as $line) {
        if (!empty(trim($line))) {
            $data = str_getcsv($line);
            $book = array_combine($header, $data); // Pravljenje asocijativnog niza
            $jsonHandler->addElement($book['title'], [
                'author' => $book['author'],
                'year' => $book['year'],
                'publisher' => $book['publisher']
            ]);
        }
    }
}

// Dodavanje nove knjige
$newBookTitle = 'The Catcher in the Rye';
$newBookData = [
    'author' => 'J.D. Salinger',
    'year' => '1951',
    'publisher' => 'Little, Brown and Company'
];
$jsonHandler->addElement($newBookTitle, $newBookData);

// Izmena knjige '1984'
$jsonHandler->updateElement('1984', [
    'author' => 'George Orwell',
    'year' => '1950', // Ažuriramo godinu
    'publisher' => 'Secker & Warburg'
]);

// Brisanje knjige 'Brave New World'
$jsonHandler->deleteElement('Brave New World');

// Snimamo JSON fajl koristeći FileHandler
$jsonContent = $jsonHandler->displayJSON();
$fileHandler->writeToFile($jsonFile, $jsonContent);

// Prikazujemo sadržaj JSON-a u pregledniku
echo "<h3>Generisani JSON podaci nakon dodavanja nove knjige:</h3><pre>";
echo $jsonContent;
echo "</pre>";

?>
