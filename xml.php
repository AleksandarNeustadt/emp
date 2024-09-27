<?php

require_once 'fileHandlerClass.php';
require_once 'xmlHandlerClass.php';

// Putanja do resurs fajla (CSV)
$csvFile = 'books.csv';

// Putanja do XML fajla koji ćemo kreirati
$xmlFile = 'example.xml';

// Kreiramo instancu klase za fajlove
$fileHandler = new FileHandler();

// Proveravamo da li XML fajl postoji i brišemo ga ako postoji
if ($fileHandler->fileExists($xmlFile)) {
    $fileHandler->deleteFile($xmlFile);
}

// Kreiramo prazan XML fajl
$xmlHandler = new XMLHandler();

// Učitavamo knjige iz CSV resurs fajla i dodajemo ih u XML
if (($handle = fopen($csvFile, "r")) !== false) {
    $header = fgetcsv($handle); // Čitamo zaglavlje (naslove kolona)
    while (($data = fgetcsv($handle)) !== false) {
        $xmlHandler->addBook($data[0], $data[1], $data[2], $data[3]);
    }
    fclose($handle);
}

// Dodajemo još jednu knjigu
$xmlHandler->addBook('The Catcher in the Rye', 'J.D. Salinger', '1951', 'Little, Brown and Company');

// Snimanje XML-a nazad u fajl
$xmlHandler->saveToFile($xmlFile);

// Prikaz sadržaja XML-a
echo "<h3>Sadržaj XML-a:</h3><pre>";
echo $xmlHandler->displayXML();
echo "</pre>";
