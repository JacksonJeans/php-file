<?php

namespace JacksonJeans;

use JacksonJeans;

/**
 * Statische Klasse mit Methoden für File und FileList
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     1.0
 */
class FileInfo
{

    /**
     * @var string $Name 
     * - Name der Datei
     */
    public $Name;

    /**
     * @var string $Filename 
     * - Dateiname
     */
    public $Filename;

    /**
     * @var string $Filepath 
     * - Pfad zur Datei
     */
    public $Filepath;

    /**
     * @var int $LastChange 
     * - UNIX Zeitstempel 
     */
    public $LastChange;

    /**
     * @var string $OwnerId 
     * - Benutzer ID
     */
    public $OwnerId;

    /**
     * @var string $GroupId
     * - Gruppen ID
     */
    public $GroupId;

    /**
     * @var string $MimeType 
     * - MIME Type der Datei
     */
    public $MimeType;

    /**
     * @var string $Extension 
     * - Dateierweiterung
     */
    public $Extension;

    /**
     * Instanziiert das FileInfo Objekt, um die Infos einer Datei zu listen.
     * 
     * @param JacksonJeans\File $file
     * @return JacksonJeans\FileInfo
     */
    public function __construct($file = null)
    {
        if ($file !== null) {
            if ($file instanceof JacksonJeans\File) {
                $path = $file->getFilepath();
                $this->Name = $file->getName();
                $this->Filename = basename($path);
                $this->Filepath = $path;
                # als int
                $this->LastChange = $file->getLastChange(false);
                $this->OwnerId = $file->getOwnerId();
                $this->GroupId = $file->getGroupId();
                $this->MimeType = $file->getMimeContentType();
                $this->Extension = $file->getSuffix();
            }
        }

        return $this;
    }

    /**
     * Prüft ob der angegebene Pfad eine Datei ist.
     * @param string $filepath
     * - Dateipfad zur Datei
     * @return bool is_file
     */
    public static function isFile(string $filepath)
    {
        return is_file($filepath);
    }

    /**
     * Prüft ob der angegebene Pfad ein Verzeichnis ist.
     * @param string $path 
     * - Pfad
     * @return bool is_dir
     */
    public static function isDir(string $path)
    {
        return is_dir($path);
    }

    /**
     * Prüft ob der angegebene Pfad ein gültiger Verzeichnisname ist und keinen Dateinamen enthält.
     * @param string $path 
     * - Pfad
     * @return bool $result 
     * - Wenn $path ein gültiger Verzeichnisname ist und keinen Dateinamen mit Suffix enthält, dann wird true zurückgegeben.
     * - Wenn $path kein gültiger Verzeichnisname ist oder einen Dateinamen mit Suffix enthält, dann wird false zurückgegeben.
     */
    public static function isDirName(string $path)
    {
        if (strpbrk($path, "\\?%*:|\"<>") === FALSE) {
            $arr = explode('\\', $path);
            if ($arr !== false) {
                $end = end($arr);
                return (strpos($end, '.') === FALSE) ? true : false;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Prüft ob der Name ein gültiger Datei,- oder Verzeichnisname ist.
     * @param string $name 
     * - Der Name
     * @return bool preg_match
     */
    public static function isValidName(string $name)
    {
        return preg_match('/^([-\.\w]+)$/', $name) > 0;
    }

    /**
     * Ermittel anhand des binären Dateiobjektes, ob die Datei ein Archiv ist, und wenn ja, welches.
     * Implementiert: zip, rar[in mache] 
     * 
     * @link https://en.wikipedia.org/wiki/List_of_file_signatures
     * @return string|false
     */
    public static function isArchiv(string $filepath)
    {
        $blob = self::getFileSignature($filepath);
        if (strpos($blob, 'Rar') !== false) {
            return 'rar';
        } else
            if (strpos($blob, 'PK') !== false) {
            return 'zip';
        } else {
            return false;
        }
    }

    /**
     * Holt die Dateiobjekt Signatur über den angegebenen Dateipfad.
     * @param string $filepath
     * - Dateipfad 
     * @return string $blob 
     * - Blob mit entsprechender Signatur
     */
    public static function getFileSignature(string $filepath)
    {
        if (self::isFile($filepath)) {
            $handle = @fopen($filepath, 'r');
            $blob = fgets($handle, 5);
            fclose($handle);
            return $blob;
        } else {
        }
    }

    /**
     * Ermittelt den Datei MIME-Typ (Multipurpose Internet Mail Extensions)
     * @param string $filepath 
     * - Der Pfad zur Datei
     * @param  string $mimepath 
     * - Der Pfad zur Magic-Datei von finfo [default: /usr/share/misc/magic]
     * @return string $filetype
     */
    public static function getMimeContentType(string $filepath, $mimepath = '/usr/share/misc/magic')
    {
        $mime = finfo_open(FILEINFO_MIME, $mimepath);
        if ($mime === FALSE) {
            throw new \Exception('Konnte finfo nicht öffnen');
        }

        $filetype = finfo_file($mime, $filepath);

        if ($filetype === FALSE) {
            throw new JacksonJeans\FileException(FileException::CODE_MIME_ERROR);
        }

        return $filetype;
    }

    /**
     * Liefert die Datei Informationen
     * 
     * @param JacksonJeans\File $file 
     * @return JacksonJeans\FileInfo
     */
    public static function getFileInfo(JacksonJeans\File $file)
    {
        return new FileInfo($file);
    }

    public function __destruct()
    {
        $vars = array_keys(get_class_vars('JacksonJeans\FileInfo'));
        foreach ($vars as $var) {
            unset($this->$var);
        }
    }
}
