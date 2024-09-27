<?php
/**
 * FileHandler Class - Development Notes
 *
 * TODO List for Future Development and Improvements:
 *
 * 1. Validation and Path Handling:
 *    - Ensure valid file and directory paths using realpath() or sanitization functions.
 *    - Consider adding validation for absolute and relative paths.
 *
 * 2. Error Handling and Logging:
 *    - Implement try-catch blocks and throw custom exceptions for error cases.
 *    - Add logging functionality to track errors (e.g., file creation failures, permission issues).
 *
 * 3. Efficiency Improvements:
 *    - Implement caching for frequently accessed file information (e.g., size, modification date).
 *    - Optimize handling of large files by using streaming methods like fgets() instead of loading entire files into memory.
 *
 * 4. Security Considerations:
 *    - Sanitize user input (paths, filenames) to prevent directory traversal and injection attacks.
 *    - Ensure proper file and directory permissions during creation (e.g., chmod for write/read control).
 *
 * 5. Expand Functionality:
 *    - Add support for handling more file formats (JSON, XML, etc.).
 *    - Consider separating directory handling into its own class (DirectoryHandler).
 *    - Implement file versioning or backup mechanisms to track changes over time.
 *
 * 6. Testing and Modularity:
 *    - Create unit tests for each method to ensure proper functionality (consider PHPUnit for automated testing).
 *    - Keep methods modular and isolated to maintain class flexibility and future scalability.
 *
 * 7. Additional Functionality to Consider:
 *    - Support for compressing and decompressing files (e.g., ZIP).
 *    - Adding features to manipulate file metadata (timestamps, ownership).
 *    - Implement support for batch processing of files (e.g., processing multiple files in one go).
 *    - Logging file actions for audit trails (e.g., log all file modifications, creations, deletions).
 *
 * Last Updated: [27.09.2024]
 */

class FileHandler
{
    // Proverava da li fajl postoji
    public function fileExists($filePath)
    {
        return file_exists($filePath);
    }

    // Briše fajl ako postoji
    public function deleteFile($filePath)
    {
        if ($this->fileExists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }

    // Kreira fajl sa sadržajem
    public function createFile($filePath, $content = "")
    {
        $file = fopen($filePath, 'w');
        fwrite($file, $content);
        fclose($file);
    }

    // Učitava sadržaj fajla
    public function loadFile($filePath)
    {
        if ($this->fileExists($filePath)) {
            return file_get_contents($filePath);
        }
        return false;
    }

    // Čitanje CSV fajla u asocijativni niz
    public function readCSV($filePath)
    {
        $rows = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            $header = fgetcsv($handle); // Čitanje zaglavlja
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($header, $data); // Kreiramo asocijativni niz sa zaglavljem kao ključevima
            }
            fclose($handle);
        }
        return $rows;
    }

    // Kopira fajl sa jedne putanje na drugu
    public function copyFile($sourcePath, $destinationPath)
    {
        if ($this->fileExists($sourcePath)) {
            return copy($sourcePath, $destinationPath);
        }
        return false;
    }

    // Premješta fajl sa jedne lokacije na drugu
    public function moveFile($sourcePath, $destinationPath)
    {
        if ($this->copyFile($sourcePath, $destinationPath)) {
            return $this->deleteFile($sourcePath);
        }
        return false;
    }

    // Vraća informacije o fajlu (veličina, datum izmene, ekstenzija)
    public function getFileInfo($filePath)
    {
        if ($this->fileExists($filePath)) {
            return [
                'size' => filesize($filePath), // Veličina fajla
                'last_modified' => filemtime($filePath), // Datum poslednje izmene
                'extension' => pathinfo($filePath, PATHINFO_EXTENSION) // Ekstenzija fajla
            ];
        }
        return false;
    }

    // Piše ili dodaje sadržaj u fajl
    public function writeToFile($filePath, $content, $append = false)
    {
        $mode = $append ? 'a' : 'w'; // 'a' za dodavanje, 'w' za pisanje (brisanje starog sadržaja)
        $file = fopen($filePath, $mode);
        if ($file) {
            fwrite($file, $content);
            fclose($file);
            return true;
        }
        return false;
    }

    // Preimenuje fajl
    public function renameFile($oldName, $newName)
    {
        if ($this->fileExists($oldName)) {
            return rename($oldName, $newName);
        }
        return false;
    }

    // Učitava fajl i pretražuje određeni tekst unutar njega
    public function searchInFile($filePath, $searchTerm)
    {
        if ($this->fileExists($filePath)) {
            $content = file_get_contents($filePath);
            return strpos($content, $searchTerm) !== false;
        }
        return false;
    }

    // Kreira direktorijum ako ne postoji
    public function createDirectory($directoryPath)
    {
        if (!is_dir($directoryPath)) {
            return mkdir($directoryPath, 0777, true); // Kreiraj sa svim potrebnim nad-direktorijumima
        }
        return false;
    }

    // Briše direktorijum i sav njegov sadržaj
    public function deleteDirectory($directoryPath)
    {
        if (is_dir($directoryPath)) {
            $files = array_diff(scandir($directoryPath), ['.', '..']);
            foreach ($files as $file) {
                $filePath = "$directoryPath/$file";
                is_dir($filePath) ? $this->deleteDirectory($filePath) : unlink($filePath);
            }
            return rmdir($directoryPath);
        }
        return false;
    }

    // Vraća listu fajlova u direktorijumu
    public function listFilesInDirectory($directoryPath)
    {
        if (is_dir($directoryPath)) {
            return array_diff(scandir($directoryPath), ['.', '..']); // Vraćamo fajlove bez . i ..
        }
        return false;
    }

    // Kompresuje fajlove u ZIP format
    public function zipFiles($files = [], $zipFilePath)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            foreach ($files as $file) {
                if ($this->fileExists($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
            return true;
        }
        return false;
    }

    // Raspakuje ZIP fajl u određeni direktorijum
    public function unzipFile($zipFilePath, $destination)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) === true) {
            $zip->extractTo($destination);
            $zip->close();
            return true;
        }
        return false;
    }
}
