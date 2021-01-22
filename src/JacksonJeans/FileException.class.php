<?php

namespace JacksonJeans;

use JacksonJeans;

/**
 * ExceptionHandler für File-Klasse
 * 
 * @category    Class
 * @package     JacksonJeans
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     1.0
 */
class FileException extends \InvalidArgumentException
{
    const CODE_FILE_EMPTY = 0x01;
    const CODE_FILE_NOT_FOUND = 0x02;
    const CODE_NO_RESOURCE = 0x03;
    const CODE_COMPRESSION_ERROR = 0x04;
    const CODE_DECOMPRESSION_ERROR = 0x05;
    const CODE_DECRYPT_ERROR = 0x06;
    const CODE_ENCRYPT_ERROR = 0x07;
    const CODE_POINTER_RESET = 0x08;
    const CODE_NOT_READABLE = 0x09;
    const CODE_BYTE_INT = 0x10;
    const CODE_PERMISSION_DENIED = 0x11;
    const CODE_WRITE_ZERO_BYTE = 0x12;
    const CODE_FILE_COPY_PERMISSION = 0x13;
    const CODE_FILE_DESTINATION_ERROR = 0x14;
    const CODE_SEARCH_ERROR = 0x15;
    const CODE_INVALID_FILENAME = 0x16;
    const CODE_MIME_ERROR = 0x17;
    const CODE_NO_CSV = 0x18;
    const CODE_UNKNOWN_ERROR = 0xFF;

    /**
     * @var array Vordefinierte Fehlermeldung
     */
    private static $errorMessages = array(
        self::CODE_FILE_EMPTY => 'Die Datei \'%s\'  ist leer.',
        self::CODE_FILE_NOT_FOUND => 'Der angegebene Pfad "\'%s\'" konnte nicht gefunden werden.',
        self::CODE_NO_RESOURCE => 'Die angegebene Resource ist keine vom Typ "resource". "\'%s\'" ist keine gültige Resource.',
        self::CODE_COMPRESSION_ERROR => 'Die betreffende Dateiresource "\'%s\'" konnte nicht in ein Archiv komprimiert werden.',
        self::CODE_DECOMPRESSION_ERROR => 'Die betreffende Dateiresource "\'%s\'" konnte nicht in ein Archiv dekomprimiert werden.',
        self::CODE_DECRYPT_ERROR => 'Die betreffende Dateiresource "\'%s\'" konnte nicht wie angegeben entschlüsselt werden.',
        self::CODE_ENCRYPT_ERROR => 'Die betreffende Dateiresource "\'%s\'" konnte nicht wie angegeben verschlüsselt werden.',
        self::CODE_NOT_READABLE => 'Die Datei "\'%s\'" konnte nicht gelesen werden.',
        self::CODE_POINTER_RESET => 'Zeiger konnte nicht zurückgesetzt werden',
        self::CODE_BYTE_INT => 'Startbyte muss eine ganze Zahl [int] sein',
        self::CODE_PERMISSION_DENIED => 'Daten konnten nicht in die Datei "\'%s\'" geschrieben werden, bitte überprüfen Sie die Berechtigungen',
        self::CODE_WRITE_ZERO_BYTE => 'Daten müssen mindestens ein Byte haben',
        self::CODE_FILE_COPY_PERMISSION => 'Datei "\'%s\'" konnte nicht ins Ziel kopiert werden, bitte überprüfen Sie die Berechtigungen',
        self::CODE_FILE_DESTINATION_ERROR => 'Ziel muss gültig und mindestens ein Zeichen haben. Übergeben: "\'%s\'".',
        self::CODE_SEARCH_ERROR => 'Der Suchstring muss mindestens 1 Zeichen lang sein',
        self::CODE_INVALID_FILENAME => 'Der Dateiname "\'%s\'" ist ungültig.',
        self::CODE_MIME_ERROR => 'Der MIME-Type konnte nicht erkannt werden.',
        self::CODE_NO_CSV => 'Die Datei ist keine CSV oder beschädigt.',
        self::CODE_UNKNOWN_ERROR => 'Unbekannter Error'
    );

    /**
     * Neue FileException erstellen, fügt automatisch eine aussagekräftige Fehlermeldung hinzu, wenn der Fehlercode bekannt ist.
     *
     * @param int $code
     * @param string $fileException Die Zeichenfolge als Zusatz zur Error Meldung.
     */
    public function __construct($code, $fileException = '')
    {
        if (!array_key_exists($code, self::$errorMessages)) {
            $code = self::CODE_UNKNOWN_ERROR;
        }
        $message = sprintf(self::$errorMessages[$code], $fileException);

        parent::__construct($message, $code);
    }
}
