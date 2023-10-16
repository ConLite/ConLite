<?php
function myfile($url) {
    // URL zerlegen
    $parsedurl = @parse_url($url);
    // Host ermitteln, ungültigen Aufruf abfangen
    if (empty($parsedurl['host'])) {
        return null;
    }
    $host = $parsedurl['host'];
    // Pfadangabe ermitteln
    if (empty($parsedurl['path'])) {
        $documentpath = '/';
    } else {
        $documentpath = $parsedurl['path'];
    }
    // Parameter ermitteln
    if (!empty($parsedurl['query'])) {
        $documentpath .= '?' . $parsedurl['query'];
    }
    // Port ermitteln
    if (!empty($parsedurl['port'])) {
        $port = $parsedurl['port'];
    } else {
        $port = 80;
    }
    // Socket öffnen
    $fp = @fsockopen($host, $port, $errno, $errstr, 30);
    if (!$fp) {
        return null;
    }
    // Request senden
    fputs ($fp, "GET {$documentpath} HTTP/1.0\r\nHost: {$host}\r\n\r\n");
    // Header auslesen
    do {
        $line = chop(fgets($fp));
    } while ((!empty($line)) && (!feof($fp)));
    // Daten auslesen
    $result = Array();
    while (!feof($fp)) {
        $result[] = fgets($fp);
    }
    // Socket schliessen
    fclose($fp);
    // Ergebnis-Array zurückgeben
    return $result;
}
function prepareStringForOutput($sIn, $sCode = 'ISO-8859-1') {
    global $encoding, $lang;
    
    if ((strtoupper($sCode) == 'UTF-8') && (strtoupper($encoding[$lang]) != 'UTF-8')) {
        $sOut = utf8_decode($sIn);
    } elseif ((strtoupper($encoding[$lang]) == 'UTF-8') && (strtoupper($sCode) != 'UTF-8')) {
        $sOut = utf8_encode($sIn);
    } else {
        $sOut = $sIn;
    }
    return $sOut;
}
?>