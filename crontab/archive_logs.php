<?php
//Pfad zur Log-Datei
$logFilePath = '/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/logs/latest.log';

//Pfad zum Archiv
$archiveDir = '/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/logs/archive';

//Archiv-Verzeichnis erstellen, falls notwendig
if (!is_dir($archiveDir)) {
    mkdir($archiveDir, 0755, true);
}

//ZIP-Datei entsprechend nach Datum benennen
$zipFileName = $archiveDir . '/log_' . date('Y-m-d') . '.zip';

//Ueberpruefe, ob latest.log existiert
if (file_exists($logFilePath)) {
    //ZIP-Datei erstellen
    $zip = new ZipArchive();
    if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
        //Log-Datei ins ZIP-Archiv hinzufuegen
        $zip->addFile($logFilePath, basename($logFilePath));
        $zip->close();

        //latest-log loeschen
        unlink($logFilePath);
    }
}
?>