<?php

namespace JacksonJeans;

use JacksonJeans;

/**
 * File - Klasse
 * 
 * @category    Class
 * @package     JacksonJeans
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     1.0
 */
class File implements FileInterface
{

    /**
     * @var resource $file 
     * - Dateiresource
     */
    public $file;

    /**
     * @var string $filepath 
     * - Pfad zur Datei
     */
    private $filepath;

    /**
     * @var bool $binary 
     * - Binärdatei
     */
    public $binary;

    /**
     * @var string $name 
     * - Name der Datei (PATHINFO_FILENAME)
     */
    public $name;

    /**
     * @var int $size 
     * - Größe in [bytes]
     */
    public $size;

    /**
     * @var string $lastLine
     * - letzte gelesene Zeile 
     */
    private $lastLine;

    /**
     * @var bool $action_before_reading 
     * - Indikator für den Dateizeiger
     */
    private $action_before_reading = false;

    /**
     * @var string $tmpName 
     * - Temporärer RealName der Datei
     * - Hinweis: Wird nur gesetzt, wenn eine temporäre Datei erstellt wird.
     */
    private $tmpName = null;

    /**
     * @var string $tmpSuffix 
     * - Temporärer RealExtension der Datei
     * - Hinweis: Wird nur gesetzt, wenn eine temporäre Datei erstellt wird.
     */
    private $tmpSuffix = null;

    /**
     * Rufe den Konstrukor auf, wenn eine neue Datei geschrieben werden soll.
     * @param string $newFileName 
     * - Neuer Dateiname
     * @param string $extension 
     * - Dateierweiterung
     * @param bool $binary 
     * - Indikator für Binär-Datei
     */
    public function __construct($newFileName = null, $extension = null, $binary = false)
    {
        if (is_string($newFileName)) {
            $this->tmpName = $newFileName;
            $this->tmpSuffix = $extension;
            $this->setFile(tempnam(sys_get_temp_dir(), $newFileName), $binary);
            $this->size = $this->getSize();
        }

        return $this;
    }

    /**
     * Legt die Datei anhand des Pfads fest.
     * @param string $filepath 
     * - Pfad zur Datei
     * @param bool $binary 
     * - Binär einlesen Optional. Falls die Datei eine Binary-Datei ist, dann muss $binary auf true.
     */
    public function setFile(string $filepath, $binary = false)
    {
        if (is_file($filepath)) {
            $this->filepath = $filepath;
            $this->name = pathinfo($filepath, PATHINFO_FILENAME);
            $this->binary = $binary;
            if ($binary) {
                $this->file = @fopen($filepath, "a+b");
                if (!$this->file) {
                    $this->file = @fopen($filepath, "rb");
                }
            } else {
                $this->file = @fopen($filepath, "a+");
                if (!$this->file) {
                    $this->file = @fopen($filepath, "r");
                }
            }

            $this->size = $this->getSize();
            return $this;
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_FILE_NOT_FOUND, $filepath);
        }
    }

    /**
     * Legt die Datei anhand einer Resource fest.
     * @param resource $resource [required]
     * - Dateiresource
     * @param bool $binary [optional|default:false]
     * - Binär Indikator. Standard FALSE
     */
    public function &setResource($resource, $binary = false)
    {
        $this->binary = $binary;
        if (is_resource($resource)) {
            $this->file = $resource;
            $meta_data = stream_get_meta_data($resource);
            if (isset($meta_data['uri'])) {
                $this->filepath = $meta_data["uri"];
                $this->name = pathinfo($this->filepath, PATHINFO_BASENAME);
            }
            return $this;
        } else {
            $t = gettype($resource);
            throw new \InvalidArgumentException("Argument ist keine resource. {$t} wurde anstelle übergeben.");
        }
    }

    /**
     * Liefert die Dateigröße in Bytes
     * @param bool $formatted
     * @return int $filesize Die Dateigröße in Bytes
     */
    public function getSize($formatted = false)
    {
        return ($formatted) ? $this->_format_bytes(filesize($this->filepath)) : filesize($this->filepath);
    }

    /**
     * Liefert den Zeitstempel der letzten Änderung
     * @return int $time Der Zeitpunkt der letzten Änderung als Timestamp, wenn $dateTimeFormat == null
     * @return string $time Der Zeitpunkt der letzten Änderung als string in der Schreibweise Y-m-d\TH:i:s\Z, wenn $dateTimeFormat == true
     * @return string $time Der Zeitpunkt der letzten Änderung als string in der in $dateTimeFormat definierten Schreibweise.
     */
    public function getLastChange($dateTimeFormat = null)
    {
        $timestamp = fileatime($this->filepath);
        if (is_string($dateTimeFormat)) {
            $time = gmdate($dateTimeFormat, $timestamp);
        } elseif ((is_bool($dateTimeFormat)) && ($dateTimeFormat == true)) {
            $time = gmdate('Y-m-d\TH:i:s\Z', $timestamp);
        } else {
            $time = $timestamp;
        }

        return $time;
    }

    /**
     * Holt die zuletzt gelesene Zeile
     * @return string $this->lastLine
     */
    public function getLastLine()
    {
        return $this->lastLine;
    }

    /**
     * Legt den Namen der Datei fest.
     * @param string $name 
     * - Name der Datei
     * @return File
     * - File Objekt
     */
    public function setName(string $name)
    {
        if (FileInfo::isValidName($name)) {
            if (!is_null($this->tmpName)) {
                $this->tmpName = $name;
            }
            $this->name = $name;
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_INVALID_FILENAME, $name);
        }

        return $this;
    }

    /**
     * Liefert den Dateinamen
     * @return string $filename Der Dateiname
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Liefert den Dateipfad
     * @return string $filepath 
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Liefert die Benutzerkennung der Datei
     * @return string $user_id Die Benutzerkennung der Datei
     */
    public function getOwnerId()
    {
        return fileowner($this->filepath);
    }

    /**
     * Liefert die Gruppen-ID der Datei
     * @return string $group_id Die Gruppen-ID der Datei
     */
    public function getGroupId()
    {
        return filegroup($this->filepath);
    }

    /**
     * Liefert das Suffix der Datei
     * @return string $suffix Das Suffix der Datei. Wenn kein Suffix existiert, wird FALSE zurückgegeben
     */
    public function getSuffix()
    {
        if (strpos($this->filepath, '.') !== FALSE) {
            # Aufspaltung von Präfix und Suffix eines realen Dateinamens
            $file_array = explode(".", $this->filepath);
            $suffix = $file_array[count($file_array) - 1];

            $suffix = pathinfo($this->filepath, PATHINFO_EXTENSION);

            if (strlen($suffix) > 0) {
                return $suffix;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Setzt die aktuelle Zeigerposition
     * @return int $offset Gibt die aktuelle Position des Zeigers zurück
     */
    public function setPointer($offset)
    {
        $this->action_before_reading = true;
        return fseek($this->file, $offset);
    }

    /**
     * Liefert die aktuelle Zeigerposition
     * @return int $offset Liefert die aktuelle Position des Zeigers
     */
    public function getPointer()
    {
        return ftell($this->file);
    }

    /**
     * Setzt den Dateizeiger zurück.
     */
    public function resetPointer()
    {
        $this->action_before_reading = false;
        if ($bool = rewind($this->file)) {
            return $bool;
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_POINTER_RESET);
        }
    }

    /**
     * Ermittelt den Datei MIME-Typ (Multipurpose Internet Mail Extensions)
     * @param  string $mimepath 
     * - Der Pfad zur Magic-Datei von finfo [default: /usr/share/misc/magic]
     * @return string JacksonJeans\FileInfo::getMimeContentType()
     */
    public function getMimeContentType($mimepath = '/usr/share/misc/magic')
    {
        return JacksonJeans\FileInfo::getMimeContentType($this->filepath, $mimepath);
    }

    /**
     * Liest eine Zeile aus der Datei
     * @return string $line Eine Zeile aus der Datei. Wenn EOF ist, wird false zurückgegeben
     */
    public function readLine($line = null)
    {
        if (($this->action_before_reading) && (!is_int($line))) {
            if (rewind($this->file)) {
                $this->action_before_reading = false;
                return $this->lastLine = fgets($this->file);
            } else {
                throw new JacksonJeans\FileException(FileException::CODE_POINTER_RESET);
            }
        } elseif (is_int($line)) {
            $this->setPointer($line);
            return $this->lastLine = fgets($this->file);
        } else {
            return $this->lastLine = fgets($this->file);
        }
    }

    /**
     * Liest Daten aus einer Binärdatei
     * @param int $bytes
     * - Anzahl der Bytes die gelesen werden soll. Wenn auf -1, dann werden alle Bytes gelesen.
     * @param int $offset 
     * - Legt die Position des Dateizeigers auf die übergebene Position von wo aus gelesen werden soll. Standard 0
     * @return string $line Daten aus einer Binärdatei
     */
    public function read($bytes = -1, $offset = 0)
    {
        # If, dann lese alles
        if ($bytes < 0) {
            $bytes = $this->getSize(false);
        }

        if (is_int($offset)) {
            if (rewind($this->file)) {
                if ($offset > 0) {
                    $this->setPointer($offset);
                    $result = @fread($this->file, $bytes);
                } else {
                    $result = @fread($this->file, $bytes);
                }

                return ($result) ? $result : null;
            } else {
                throw new JacksonJeans\FileException(FileException::CODE_POINTER_RESET);
                return false;
            }
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_BYTE_INT);
            return false;
        }
    }

    /**
     * Schreibt Daten in die Datei
     * @param string $data 
     * - Die Daten, die geschrieben werden sollen
     * @return boolean $overwrite 
     * - Gibt TRUE zurück, wenn die Daten geschrieben werden konnten, FALSE wenn nicht
     */
    public function write(string $data, $overwrite = false)
    {
        $this->action_before_reading = true;

        if ($overwrite) {
            ftruncate($this->file, 0);
        }

        if (strlen($data) > 0) {
            if ($this->binary) {
                $bytes = fwrite($this->file, $data);
                fclose($this->file);
                $this->setFile($this->filepath, $this->binary);
                if (is_int($bytes)) {
                    return $bytes;
                } else {
                    throw new JacksonJeans\FileException(FileException::CODE_PERMISSION_DENIED);
                    return false;
                }
            } else {
                $bytes = fputs($this->file, $data);
                fclose($this->file);
                $this->setFile($this->filepath, $this->binary);
                if (is_int($bytes)) {
                    return $bytes;
                } else {
                    throw new JacksonJeans\FileException(FileException::CODE_PERMISSION_DENIED);
                    return false;
                }
            }
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_WRITE_ZERO_BYTE);
        }
    }

    /**
     * Kopiert eine Datei in das angegebene Ziel.
     * @param string $destination 
     * - Das neue Dateiziel
     * @return boolean $copied 
     * - Gibt TRUE zurück, wenn die Datei kopiert werden konnte, FALSE wenn nicht
     */
    public function copy(string $destination)
    {
        if (strlen($destination) > 0) {
            if ((is_dir($destination)) && (!is_file($destination))) {
                if ($destination[strlen($destination) - 1] == '/') {
                    $destination = substr($destination, 0, strlen($destination) - 1);
                }

                if (!is_null($this->tmpName)) {
                    $this->name = $this->tmpName;
                }

                if (!($suffix = $this->getSuffix())) {
                    $suffix = 'tmp';
                }

                if (!is_null($this->tmpSuffix)) {
                    $suffix = $this->tmpSuffix;
                }

                $destination = $destination . '/' . $this->name . '.' . $suffix;
            }

            if (copy($this->filepath, $destination)) {
                return true;
            } else {
                throw new JacksonJeans\FileException(FileException::CODE_FILE_COPY_PERMISSION, $this->filepath);
                return false;
            }
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_FILE_DESTINATION_ERROR, $destination);
        }
    }

    /**
     * Verschiebt eine Datei in das angegebene Ziel.
     * @param string $destination 
     * - Das neue Dateiziel
     * @return boolean $moved 
     * - Gibt TRUE zurück, wenn die Datei kopiert werden konnte, FALSE wenn nicht
     */
    public function move(string $destination)
    {
        if (strlen($destination) > 0) {
            if ((is_dir($destination)) && (!is_file($destination))) {
                if ($destination[strlen($destination) - 1] == '/') {
                    $destination = substr($destination, 0, strlen($destination) - 1);
                }

                if (!is_null($this->tmpName)) {
                    $this->name = $this->tmpName;
                }

                if (!($suffix = $this->getSuffix())) {
                    $suffix = 'tmp';
                }

                if (!is_null($this->tmpSuffix)) {
                    $suffix = $this->tmpSuffix;
                }

                $destination = $destination . '/' . $this->name . '.' . $suffix;
            }
            chmod($this->filepath, 0755);
            if (rename($this->filepath, $destination)) {
                $this->filepath = $destination;
                fclose($this->file);
                $this->setFile($this->filepath, $this->binary);
                return true;
            } else {
                throw new JacksonJeans\FileException(FileException::CODE_FILE_COPY_PERMISSION, $this->filepath);
                return false;
            }
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_FILE_DESTINATION_ERROR, $destination);
        }
    }

    /**
     * Löscht die Datei
     */
    public function delete()
    {
        fclose($this->file);
        unlink($this->filepath);
    }

    /**
     * Schließt die Datei und entlädt das Objekt.
     */
    public function close()
    {
        $this->__destruct();
    }

    /**
     * Sucht eine Zeichenkette in der Datei
     * @param string $string 
     * - Die Zeichenkette, die gesucht werden soll
     * @return array $found_bytes 
     * - Zeiger-Offsets, an denen String gefunden wurde. Bei keinem Treffer gibt die Funktion FALSE zurück.
     */
    public function search(string $string)
    {
        if (strlen($string) != 0) {

            $offsets = array();

            $offset = $this->getPointer();
            rewind($this->file);

            # Alle Daten aus Datei holen
            $data = fread($this->file, $this->getSize());

            # Ersetzen von \r in Windows die NewLines
            $data = preg_replace("[\r]", "", $data);

            $found = false;
            $k = 0;

            for ($i = 0; $i < strlen($data); $i++) {

                $char = $data[$i];
                $search_char = $string[0];

                # Wenn das erste Zeichen der Zeichenkette gefunden wurde und das erste Zeichen nicht gefunden wurde
                if ($char == $search_char && $found == false) {
                    $j = 0;
                    $found = true;
                    $found_now = true;
                }

                # Wenn der Anfang der Zeichenkette gefunden wurde und das nächste Zeichen gesetzt wurde
                if ($found == true && $found_now == false) {
                    $j++;
                    # Wenn das nächste Zeichen gefunden wurde
                    $datastring = substr($data, $i, 1);
                    $stringdata = substr($string, $j, 1);

                    #if ($data[(int)$i] == $string[(int)$j]) {
                    if ($datastring == $stringdata) {
                        # Wenn vollständige Zeichenfolge übereinstimmt
                        if (($j + 1) == strlen($string)) {
                            $found_offset = $i - strlen($string) + 2;
                            $offsets[$k++] = $found_offset;
                        }
                    } else {
                        $found = false;
                    }
                }
                $found_now = false;
            }
            $this->setPointer($offset);

            return $offsets;
        } else {
            throw new JacksonJeans\FileException(FileException::CODE_SEARCH_ERROR);
        }
    }

    /**
     * Dekomprimiert ein Dateiarchiv optional in ein vordefinierten Pfad der im Übergabeparameter repräsentiert wird. 
     * 
     * @param string $destination
     * - Ziel
     * @return bool|array $files [JacksonJeans\File, JacksonJeans\File,...]
     * - Im Fehlerfall gibt decompress() false zurück.
     * - Liefert ein Array mit den Dateien die dekomprimiert wurden.
     */
    public function decompress(string $destination)
    {
        $zip = new \ZipArchive;
        if ($zip->open($this->filepath) === TRUE) {
            $zip->extractTo($destination);
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

                return $files;
            }
        } else {
            return false;
        }
    }

    /**
     * Entschlüsselt die Datei mit PKSC_5
     * @param string $key
     * - Schluessel
     * @param string $iv 
     * - Vektor
     * @param bool $overwrite 
     * - Datei überschreiben
     * @return string $str;
     */
    public function decrypt(string $key, string $iv, $overwrite = false)
    {
        $str = JacksonJeans\AESCryptoStreamFactory::decrypt($this->read(), $key, $iv);
        if ($overwrite) {
            $this->write($str, $overwrite);
        }
        return $str;
    }

    /**
     * Verschlüsselt die Datei mit PKSC_5
     * @param string $key
     * - Schluessel
     * @param string $iv 
     * - Vektor
     * @param bool $overwrite 
     * - Datei überschreiben
     * @return string $str;
     */
    public function encrypt(string $key, string $iv, $overwrite = false)
    {
        $str = JacksonJeans\AESCryptoStreamFactory::encrypt($this->read(), $key, $iv);
        if ($overwrite) {
            $this->write($str, $overwrite);
        }
        return $str;
    }

    /**
     * Formatiert die Dateigröße in ein lesbares Byte-Format
     * @return string
     */
    private function _format_bytes(int $size)
    {
        # ermittle $size über den Logarithmus mit der Base 1024.
        $base = log($size, 1024);
        $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
        return round(pow(1024, $base - floor($base)), 2) . '' . $suffixes[floor($base)];
    }

    /**
     * Setter
     * 
     * @param string $name 
     * - Eigenschaft 
     * @param mixed $value 
     * - Wert
     * @return mixed $results
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'File':
                $this->setFile($value);
                break;
            case 'BinaryFile':
                $this->setFile($value, true);
                break;
            default:
                throw new \InvalidArgumentException("Ungültiger Methodenaufruf. {$name} existiert nicht als Methode.");
                break;
        }
    }

    /**
     * toString Methode, gibt die Datei aus und sendet den MIME-Type als Header falls noch kein Header gesendet worden ist.
     */
    public function __toString()
    {
        $mimetype = $this->getMimeContentType();
        if (!headers_sent()) {
            header("Content-Type: {$mimetype}");
        }

        return $this->read();
    }

    /**
     * Entlädt das Objekt
     */
    public function __destruct()
    {
        if (is_resource($this->file)) {
            fclose($this->file);
        }

        $vars = array_keys(get_class_vars('JacksonJeans\File'));
        foreach ($vars as $var) {
            unset($this->$var);
        }
    }
}
