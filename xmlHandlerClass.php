<?php

/**
 * XMLHandler Class - Development Notes
 *
 * TODO List for Future Development and Improvements:
 *
 * 1. Validation and Security:
 *    - Ensure all inputs are validated and escaped (htmlspecialchars()) to prevent XML Injection.
 *    - Sanitize file paths and ensure secure handling of file locations.
 *
 * 2. Version Control and Backups:
 *    - Implement versioning for XML files.
 *    - Automatically back up XML files before any changes are made.
 *
 * 3. Performance Optimizations:
 *    - Consider using XMLReader for handling large XML files in streaming mode.
 *    - Implement caching for XPath query results to improve performance.
 *
 * 4. Modularization and Extensibility:
 *    - Add support for handling multiple XML schemas or structures.
 *    - Expand support for additional data formats like YAML, CSV, etc.
 *
 * 5. Logging and Error Handling:
 *    - Add logging for all operations (e.g., adding, deleting, validating elements).
 *    - Log errors for easier debugging and maintenance.
 *
 * 6. Testing and Maintenance:
 *    - Implement unit tests (e.g., PHPUnit) for critical methods to ensure robustness.
 *    - Keep documentation up to date for all new methods and functionalities.
 *
 * 7. API Integration:
 *    - Add support for sending/receiving XML via REST APIs.
 *    - Implement batch processing for handling multiple XML files at once.
 *
 * 8. Future Features:
 *    - Implement a rollback system for reverting to previous versions of XML files.
 *
 * Last Updated: [27.09.2024]
 */


class XMLHandler
{
    private $xml;

    // Konstruktor može primiti prazan XML ili učitati iz fajla
    public function __construct($xmlContent = null, $isFile = false)
    {
        if ($isFile && $xmlContent !== null) {
            $this->xml = simplexml_load_file($xmlContent);
        } elseif ($xmlContent !== null) {
            $this->xml = simplexml_load_string($xmlContent);
        } else {
            $this->xml = new SimpleXMLElement('<library/>');
        }

        if ($this->xml === false) {
            throw new Exception('Error loading XML');
        }
    }

    // Prikaz XML-a sa formatiranjem (pretty-print)
    public function displayXML()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml->asXML());
        return $dom->saveXML();
    }

    // Dodavanje knjiga u XML
    public function addBook($title, $author, $year, $publisher)
    {
        $newBook = $this->xml->addChild('book');
        $newBook->addChild('title', htmlspecialchars($title));
        $newBook->addChild('author', htmlspecialchars($author));
        $newBook->addChild('year', htmlspecialchars($year));
        $newBook->addChild('publisher', htmlspecialchars($publisher));
    }

    // Dodavanje atributa elementu
    public function addAttribute($element, $attributeName, $attributeValue)
    {
        $element->addAttribute($attributeName, htmlspecialchars($attributeValue));
    }

    // Pretraga elemenata po nazivu koristeći XPath
    public function searchByXPath($query)
    {
        return $this->xml->xpath($query);
    }

    // Validacija XML-a prema XSD šemi
    public function validateAgainstXSD($xsdPath)
    {
        $dom = new DOMDocument();
        $dom->loadXML($this->xml->asXML());
        return $dom->schemaValidate($xsdPath);
    }

    // Brisanje elemenata iz XML-a
    public function deleteElement($query)
    {
        $elements = $this->xml->xpath($query);
        foreach ($elements as $element) {
            unset($element[0]);
        }
    }

    // Snimanje XML-a u fajl
    public function saveToFile($filePath)
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->xml->asXML());
        $dom->save($filePath);
    }

    // Snimanje XML u JSON format
    public function saveToJSON($filePath)
    {
        $json = json_encode($this->xml);
        file_put_contents($filePath, $json);
    }

    // Transformacija XML-a u JSON format
    public function toJSON()
    {
        return json_encode($this->xml, JSON_PRETTY_PRINT);
    }

    // Učitavanje XML iz JSON fajla
    public function loadFromJSON($jsonFilePath)
    {
        $jsonContent = file_get_contents($jsonFilePath);
        $array = json_decode($jsonContent, true);
        $this->xml = new SimpleXMLElement('<library/>');
        $this->arrayToXML($array, $this->xml);
    }

    // Konvertovanje niza u XML
    private function arrayToXML($data, &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $subNode = $xmlData->addChild($key);
                $this->arrayToXML($value, $subNode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

    // XSLT transformacija XML-a
    public function transformXML($xsltFile, $outputFile)
    {
        $xsl = new DOMDocument();
        $xsl->load($xsltFile);

        $proc = new XSLTProcessor();
        $proc->importStyleSheet($xsl);

        $xmlDom = new DOMDocument();
        $xmlDom->loadXML($this->xml->asXML());

        $output = $proc->transformToXML($xmlDom);
        file_put_contents($outputFile, $output);
    }

    // Dodavanje novog elementa
    public function addElement($parentElement, $elementName, $elementValue = null)
    {
        $newElement = $parentElement->addChild($elementName, htmlspecialchars($elementValue));
        return $newElement;
    }

    // Brisanje XML fajla
    public function deleteXMLFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    // Učitavanje XML iz fajla
    public function loadFromFile($filePath)
    {
        if (file_exists($filePath)) {
            $this->xml = simplexml_load_file($filePath);
        } else {
            throw new Exception("File does not exist: $filePath");
        }
    }
}
