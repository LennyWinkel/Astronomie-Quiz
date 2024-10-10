<?php
function logInfo($info, $logFile = '/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/logs/latest.log') {
    $timestamp = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $requestedURL = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'N/A';
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

    $logMessage = "[$timestamp] [IP: $ipAddress] [User: $username] [Method: $requestMethod] [URL: $requestedURL] [Referer: $referer] [User-Agent: $userAgent] $info" . PHP_EOL;

    file_put_contents($logFile, $logMessage, FILE_APPEND);
    
}

?>