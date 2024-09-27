<?php

/**
 * JSONHandler Class - Development Notes
 *
 * TODO List for Future Development and Improvements:
 *
 * 1. Validation and Security:
 *    - Ensure that all input keys and values are properly sanitized before adding them to the JSON structure.
 *    - Implement stricter validation for file paths and JSON content before processing (e.g., check for malformed JSON).
 *
 * 2. Error Handling and Logging:
 *    - Add logging for all critical operations, especially for load/save operations to track any file errors.
 *    - Consider more granular error messages, possibly using custom exception classes for better debugging.
 *
 * 3. Performance Optimizations:
 *    - Optimize the handling of very large JSON files by implementing streaming solutions or paginated loading.
 *    - Consider caching frequently accessed elements from the JSON structure to minimize repeated access.
 *
 * 4. Modularization and Extensibility:
 *    - Consider extracting specific operations (like nested element manipulation) into their own helper classes for better separation of concerns.
 *    - Add support for handling multiple file formats, such as YAML or TOML, alongside JSON in the same class or in an abstracted manner.
 *
 * 5. Testing and Maintenance:
 *    - Implement unit tests (e.g., PHPUnit) for all methods to ensure code robustness, especially for nested operations.
 *    - Keep detailed documentation on each method and any new functionality to ensure clarity for future developers.
 *
 * 6. Data Manipulation:
 *    - Implement more advanced querying capabilities (e.g., JSONPath) to allow for more powerful and flexible searches within the JSON structure.
 *    - Consider adding functionality for merging multiple JSON documents or structures together.
 *
 * 7. Versioning and Backup:
 *    - Add a version control system or automatic backups for JSON files before making changes, so users can revert to previous versions.
 *    - Implement a rollback system to revert the last applied operation if something goes wrong.
 *
 * 8. Future Features:
 *    - Add support for JSON Schema validation to ensure the structure adheres to expected standards.
 *    - Implement auto-formatting of JSON based on different user preferences (e.g., compact vs. pretty-printed).
 *    - Add support for asynchronous operations (e.g., async file saving/loading) for more advanced use cases.
 *
 * Last Updated: [Insert Date Here]
 */

class JSONHandler
{
    private $jsonData; // Sadrži učitani JSON kao asocijativni niz

    // Konstruktor može primiti JSON string ili učitati iz fajla
    public function __construct($jsonContent = null, $isFile = false)
    {
        if ($isFile && $jsonContent !== null) {
            $this->loadFromFile($jsonContent); // Učitaj JSON iz fajla
        } elseif ($jsonContent !== null) {
            $this->jsonData = json_decode($jsonContent, true); // Parsiraj JSON string
        } else {
            $this->jsonData = []; // Prazan asocijativni niz ako nema sadržaja
        }
    }

    // Učitavanje JSON iz fajla
    public function loadFromFile($filePath)
    {
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            $this->jsonData = json_decode($content, true);
        } else {
            throw new Exception("File does not exist: $filePath");
        }
    }

    // Snimanje JSON podataka u fajl
    public function saveToFile($filePath)
    {
        $jsonContent = json_encode($this->jsonData, JSON_PRETTY_PRINT);
        file_put_contents($filePath, $jsonContent);
    }

    // Dodavanje novog elementa u JSON
    public function addElement($key, $value)
    {
        $this->jsonData[$key] = $value;
    }

    // Ažuriranje postojećeg elementa u JSON
    public function updateElement($key, $value)
    {
        if (isset($this->jsonData[$key])) {
            $this->jsonData[$key] = $value;
        } else {
            throw new Exception("Key does not exist: $key");
        }
    }

    // Brisanje elementa iz JSON-a
    public function deleteElement($key)
    {
        if (isset($this->jsonData[$key])) {
            unset($this->jsonData[$key]);
        } else {
            throw new Exception("Key does not exist: $key");
        }
    }

    // Dohvatanje elementa po ključu u JSON-u
    public function getElement($key)
    {
        return $this->jsonData[$key] ?? null;
    }

    // Prikaz JSON-a sa formatiranjem
    public function displayJSON()
    {
        return json_encode($this->jsonData, JSON_PRETTY_PRINT);
    }

    // Pretvaranje JSON-a u asocijativni niz
    public function toArray()
    {
        return $this->jsonData;
    }

    // Pretvaranje niza u JSON format
    public function fromArray(array $arrayData)
    {
        $this->jsonData = $arrayData;
    }

    // Provera da li JSON sadrži ključ
    public function hasKey($key)
    {
        return isset($this->jsonData[$key]);
    }

    // Dodavanje novog elementa unutar postojeće strukture (ugnežđeni ključ)
    public function addNestedElement(array $path, $value)
    {
        $ref = &$this->jsonData;
        foreach ($path as $key) {
            if (!isset($ref[$key])) {
                $ref[$key] = [];
            }
            $ref = &$ref[$key];
        }
        $ref = $value;
    }

    // Dohvatanje vrednosti iz ugnežđenih struktura
    public function getNestedElement(array $path)
    {
        $ref = $this->jsonData;
        foreach ($path as $key) {
            if (isset($ref[$key])) {
                $ref = $ref[$key];
            } else {
                return null; // Putanja ne postoji
            }
        }
        return $ref;
    }

    // Brisanje ugnežđenog elementa iz JSON strukture
    public function deleteNestedElement(array $path)
    {
        $ref = &$this->jsonData;
        $lastKey = array_pop($path); // Poslednji ključ je element koji želimo obrisati
        foreach ($path as $key) {
            if (isset($ref[$key])) {
                $ref = &$ref[$key];
            } else {
                throw new Exception("Path does not exist: " . implode('->', $path));
            }
        }
        unset($ref[$lastKey]);
    }
}
