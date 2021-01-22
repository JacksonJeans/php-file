<?php

namespace JacksonJeans;

use JacksonJeans;

/**
 * FileList - Klasse
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     1.0
 */
class FileList
{
    /**
     * @var array $files [JacksonJeans\File, JacksonJeans\File,...]
     * - Array mit Dateien
     */
    private $files;

    /**
     * @var string $name
     * - Name der Liste/ des Archiv
     */
    public $name = null;

    /**
     * @var JacksonJeans\File $archiv 
     * - Das Archiv
     */
    private $archiv;

    /**
     * @var string $archivFilepath;
     * - Dateipfad zum Archiv
     */
    private $archivFilepath;

    public function __construct($files = null)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                if ($file instanceof JacksonJeans\File) {
                    $this->files[] = $file;
                }
            }
        } elseif ($files instanceof JacksonJeans\File) {
            $this->files[] = $files;
        } elseif (is_string($files)) {
            if (is_file($files)) {
                if (FileInfo::isArchiv($files)) {
                    $this->setArchiv($files);
                }
            }
        }

        return $this;
    }

    /**
     * Setzt den Namen des Archivs
     * @param string $name 
     * - Dateiname
     */
    public function setName($name)
    {
        if (FileInfo::isValidName($name)) {
            $this->name = $name;
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_INVALID_FILENAME, $name);
        }

        return $this;
    }

    /**
     * Liefert ein Array mit allen im Archiv beinhalteten Dateien.
     * @return array $result 
     */
    public function listArchivFiles()
    {
        $results = [];
        if ($this->archiv !== null) {
            $zip = new \ZipArchive();

            $zip->open($this->archivFilepath);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $results[] = $zip->statIndex($i);
            }
        }

        return $results;
    }

    /**
     * Setzt das Archiv
     * @param string $filepath 
     * - Pfad zur Datei
     * @return JacksonJeans\FileList
     */
    public function setArchiv(string $filepath)
    {
        if (FileInfo::isArchiv($filepath)) {
            $this->name = pathinfo($filepath, PATHINFO_FILENAME);
            $this->archivFilepath = $filepath;
            $this->archiv = @fopen($filepath, "a+");
            if (!$this->archiv) {
                $this->archiv = @fopen($filepath, "r");
            } else {
                # Exception - Datei konnte nicht eingelesen werden
            }
        } else {
            # Exception - Datei ist kein Archiv
        }

        return $this;
    }

    /**
     * Archiviert einen Ordner
     * 
     * @param string $sourcePath 
     * - Pfad des Verzeichnisses was archiviert wird.
     * @param string $destination 
     * - Zielverzeichnis
     * @return JacksonJeans\FileList
     */
    public function setArchivFromDir($sourcePath, $destination)
    {
        if ((is_string($this->name)) && (strlen($this->name) > 0)) {
            if (JacksonJeans\FileInfo::isDirName($destination)) {
                $pathInfo = pathinfo($sourcePath);
                $parentPath = $pathInfo['dirname'];
                $dirName = $pathInfo['basename'];

                if ($destination[strlen($destination) - 1] == '/') {
                    $destination = substr($destination, 0, strlen($destination) - 1);
                }

                $destination .= '/' . $this->name . '.zip';

                $zip = new \ZipArchive();
                if ($zip->open($destination, \ZIPARCHIVE::CREATE) !== false) {
                    $zip->addEmptyDir($dirName);
                    self::folderToZip($sourcePath, $zip, strlen("$parentPath/"));
                    $zip->close();

                    return $this->setArchiv($destination);
                } else {
                    # EXCEPTION Zip konnte nicht geschrieben werden.
                }
            } else {
                # EXCEPTION Ziel ist kein Verzeichnisname
            }
        } else {
            # EXCEPTION kein name
        }
    }

    /**
     * Hinzufügen von Dateien und Unterverzeichnissen in einem Ordner zur Zip-Datei. 
     * 
     * @param string $folder 
     * - Verzeichnis 
     * @param \ZipArchive $zipFile
     * - Bereits aufgerufenes ZipArchiv Resource [\ZipArchive->open()] 
     * @param int $exclusiveLength 
     * - Nummer des Textes, der aus dem Dateipfad ausgeschlossen werden soll. 
     */
    private static function folderToZip($folder, &$zipFile, int $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                # Entferne Präfix aus dem Dateipfad vor dem Hinzufügen zum Zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    # Unterverzeichnisse hinzufügen.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    /**
     * Fügt der Liste eine Datei oder ein Archiv hinzu.
     * @param JacksonJeans\File $file 
     * - Array mit JacksonJeans\File Elementen
     */
    public function append(JacksonJeans\File $file)
    {
        $this->files[] = $file;
    }

    /**
     * Komprimiert ein Dateien zu einem Dateiarchiv in ein vordefinierten Pfad der im Übergabeparameter repräsentiert wird. 
     * 
     * @param string $destination [required]
     * - Verzeichnisziel
     * @return FileList $archiv
     */
    public function compress($destination, $overwrite = false)
    {
        if ((is_string($this->name)) && (strlen($this->name) > 0)) {
            if ($destination[strlen($destination) - 1] == '/') {
                $destination = substr($destination, 0, strlen($destination) - 1);
            }

            $destinationValid = $destination . $this->name . '.zip';

            # Existiert die ZIP bereits ?
            if (file_exists($destinationValid) && !$overwrite) {
                # Error
            }

            $destination .= $this->name . '.zip';

            $zip = new \ZipArchive();

            if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== false) {
                foreach ($this->files  as $file) {
                    $zip->addFile($file->getFilepath());
                }

                $zip->close();
                return new JacksonJeans\FileList($destination);
            } else {
                # ERROR Pfad konnte nicht gefunden werden
            }
        } else {
            # Exception kein Name
        }
    }

    /**
     * @param string $destination 
     * - absolute Verzeichnisziel
     * @return false|JacksonJeans\FileList
     */
    public function decompress($destination, $files = null)
    {
        if ($this->archiv !== null) {
            $zip = new \ZipArchive;
            if ($zip->open($this->archiv) === TRUE) {


                if ($destination[strlen($destination) - 1] == '/') {
                    $destination = substr($destination, 0, strlen($destination) - 1);
                }

                $destination .= '/' . $this->name;

                $zip->extractTo($destination, $files);
                $zip->close();

                if ($load = opendir($destination)) {
                    $files = [];
                    while (FALSE !== ($file = readdir($load))) {
                        if (($file != ".") && ($file != "..")) {
                            $fileInstance = new JacksonJeans\File;
                            $fileInstance->setFile($destination . '/' . $file);
                            $files[] = $fileInstance;
                        }
                    }
                    closedir($load);
                    return $files;
                }
            } else {
                return false;
            }
        }
    }
}
