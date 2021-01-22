<?php

namespace JacksonJeans;

use JacksonJeans;

/**
 * Interface von File 
 * 
 * @category    Class
 * @package     GIDUTEX
 * @author      Julian Tietz <julian.tietz@gidutex.de>
 * @license     Julian Tietz <julian.tietz@gidutex.de>
 * @version     1.0
 */
interface FileInterface
{
    /**
     * Liefert den Dateinamen.
     * @method getName
     * @return string
     */
    public function getName();

    /**
     * Setzt den Dateiname
     * @method setName 
     * @param string $name
     * - Zeichenkette Name
     */
    public function setName(string $name);

    /**
     * Liefert den Dateipfad
     * @method getFilepath
     * @return string
     */
    public function getFilepath();

    /**
     * Liefert die aktuelle Größe
     * @method getSize
     * @return int|string
     */
    public function getSize($formatted = false);

    /**
     * Sucht anhand des $searchstr
     * @method search
     * @param string $searchstr
     * @return bool|array
     */
    public function search(string $searchstr);

    /**
     * Schreibt Daten in die Datei
     * @param string $data 
     * - Die Daten, die geschrieben werden sollen
     * @return boolean $overwrite 
     * - Gibt TRUE zurück, wenn die Daten geschrieben werden konnten, FALSE wenn nicht
     */
    public function write(string $searchstr);

    /**
     * Liest die Datei
     * @method read
     * @param int $bytes [optional|default:-1]
     * - Anzahl an bytes die gelesen werden solln
     * - Hinweis: Bei einem Aufruf von null oder read() wird standardweise alles zurückgegeben
     * @param int $offset 
     * - Die Position des Bytes ab dem gelesen werden soll. 
     * - Hinweis: Bei einem Aufruf von null oder read() wird standardweise ab 0 gelesen.
     * @return bool|string
     */
    public function read($bytes = -1, $offset = 0);

    /**
     * Kopiert eine Datei in das angegebene Ziel.
     * @param string $destination 
     * - Das neue Dateiziel
     * @return boolean $copied 
     * - Gibt TRUE zurück, wenn die Datei kopiert werden konnte, FALSE wenn nicht
     */
    public function copy(string $destination);

    /**
     * Verschiebt eine Datei in das angegebene Ziel.
     * @param string $destination 
     * - Das neue Dateiziel
     * @return boolean $moved 
     * - Gibt TRUE zurück, wenn die Datei kopiert werden konnte, FALSE wenn nicht
     */
    public function move(string $destination);

    /**
     * @method decompress 
     * @param string $destination
     * - Pfad wohin die Dateiresource dekomprimiert werden möchte.
     * @return string $path 
     * - Gibt den Pfad zurück bei Erfolg.
     */
    public function decompress(string $destination);

    /**
     * @method decrypt 
     * @param string $key 
     * - Schlüssel
     * @param string $iv 
     * - Vektor
     * @param bool $overwrite 
     * - Gibt an, ob die Datei mit der Entschlüsselung überschrieben werden soll oder nicht.
     * @return string 
     * - Gibt string zurück wenn die Entschlüsselung erfolgreich war
     */
    public function decrypt(string $key, string $iv, $overwrite = false);

    /**
     * @method encrypt 
     * @param string $key 
     * - Schlüssel
     * @param string $iv 
     * - Vektor
     * @param bool $overwrite 
     * - Gibt an, ob die Datei mit der Verschlüsselung überschrieben werden soll oder nicht.
     * @return string 
     * - Gibt string zurück, wenn die Verschlüsselung erfolgreich war
     */
    public function encrypt(string $key, string $iv, $overwrite = false);

    /**
     * @method delete
     * @return void
     */
    public function delete();
}
