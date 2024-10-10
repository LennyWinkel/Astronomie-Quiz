<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Mail bestätigen</title>
</head>

<body>
    <?php
    include '/var/www/vhosts/astronomie-kerpen.eu/httpdocs/includes/logging.php';

    //Inhalt von confirm.json laden
    $confirm = [];
    if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/confirm.json')) {
        $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/confirm.json', true);
        $confirm = json_decode($get_file, true);
    }

    //Inhalt von user.json laden
    $user = [];
    if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json')) {
        $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', true);
        $user = json_decode($get_file, true);
    }

    foreach ($confirm as $conf_key => $getconf) {
        //Ueberpruefe, ob timestamp noch nicht abgelaufen
        if (time() < $getconf['timestamp'] + 86400) {
            //Ueberpruefe, ob username und token korrekt
            if ($_GET['user'] == $getconf['username'] && $_GET['token'] == $getconf['token']) {

                //Confirm-Daten in user.json speichern
                $new_user = [
                    'username' => $getconf['username'],
                    'email' => $getconf['email'],
                    'password' => $getconf['password'],
                    'firstname' => $getconf['firstname'],
                    'lastname' => $getconf['lastname']
                ];
                array_push($user, $new_user);
                file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', json_encode($user));

                //Results-Datei erstellen
                $results = [];
                $file_path = "/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/results/" . $getconf['username'] . ".json";
                file_put_contents($file_path, json_encode($results));

                logInfo("E-Mail successfully confirmed by " . $getconf['username']);

                //User anmelden und weiterleiten
                $_SESSION["username"] = $getconf['username'];
                header("Location: https://astronomie-kerpen.eu/quiz/personal");
                exit;
            }
        } else {
            //Abgelaufenen Token loeschen
            unset($confirm[$conf_key]);

            //Eintrag mit geloeschtem Token in confirm.json speichern
            file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json', json_encode($confirm));

            header("Location: example.com/token");
        }
    }

    //Ohne Weiterleitung, Error anzeigen (Zuruecksetzung fehlgeschlagen)
    echo "<p>Du hast die Gültigkeit von 24 Stunden überschritten. Klicke <a href='https://astronomie-kerpen.eu/quiz/account/register'>hier</a>, um dich erneut zu registrieren. <br>
             Benötigst du weitere Hilfe, wende dich bitte an den Kunden-Support:<br>
             <a href='mailto:support@astronomie-kerpen.eu'>support@astronomie-kerpen.eu</a></p>";
    ?>
</body>

</html> 