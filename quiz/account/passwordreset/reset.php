<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Passwort-Zurücksetzung</title>
</head>

<body>
    <div class="container">
        <form action="" method="post">
            <h2>Passwort-Zurücksetzung</h2>

            <label for="new_password">Neues Passwort:</label>
            <input type="password" id="new_password" name="new_password" required>

            <label for="confirm_password">Passwort bestätigen:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="submit">Passwort zurücksetzen</button>
        </form>
        <p>&copy; 2024 Astronomie-AG | <a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>

        <a href="https://astronomie-kerpen.eu/quiz/account/login" class="link">Anmelden</a><br>
        <a href="https://astronomie-kerpen.eu/quiz/account/register" class="link">Noch kein Konto?</a>
    </div>

    <?php
    session_start();

    include '/var/www/vhosts/astronomie-kerpen.eu/httpdocs/includes/logging.php';

    //Uberperpruefe, ob Formular abgesendet
    if (isset($_POST['submit'])) {
        //Ueberpruefe, ob Passwort korrekt bestätigt
        if ($_POST['new_password'] == $_POST['confirm_password']) {
            //Inhalt von reset.json laden
            $reset = [];
            if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json')) {
                $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json', true);
                $reset = json_decode($get_file, true);
            }

            foreach ($reset as $psw => $passwordreset) {
                //Ueberpruefe, ob token nicht abgelaufen
                if (time() < $passwordreset['timestamp'] + 3600) {
                    //Ueberpruefe, ob user korrekt
                    if ($_GET['user'] == $passwordreset['username']) {
                        //Ueberpruefe, ob token zum user passt
                        if ($_GET['token'] == $passwordreset['token']) {
                            //Inhalt von user.json laden
                            $user = [];
                            if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json')) {
                                $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', true);
                                $user = json_decode($get_file, true);
                            }

                            foreach ($user as $key => $put_user) {
                                //Ueberpruefe, ob user = aktuelle Stelle im Array
                                if ($put_user['username'] == $_GET['user']) {
                                    //User-Daten speichern
                                    $username = $put_user['username'];
                                    $email = $put_user['email'];
                                    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                                    $firstname = $put_user['firstname'];
                                    $lastname = $put_user['lastname'];

                                    //User löschen
                                    unset($user[$key]);

                                    //Benutzten Token löschen


                                    //Nutzer mit neuem Passwort speichern
                                    $new_user = [
                                        'username' => $username,
                                        'email' => $email,
                                        'password' => $password,
                                        'firstname' => $firstname,
                                        'lastname' => $lastname
                                    ];
                                    array_push($user, $new_user);
                                    file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', json_encode($user));

                                    logInfo("Password successfully reset by " . $username);

                                    //User anmelden und weiterleiten
                                    $_SESSION["username"] = $username;
                                    header("Location: https://astronomie-kerpen.eu/quiz/personal");
                                    exit;
                                }
                            }
                        }
                    }
                } else {
                    //Abgelaufenen Token loeschen
                    unset($reset[$psw]);

                    logInfo("Expired token [" . $passwordreset['token'] . "] successfully deleted");

                    //Eintrag mit geloeschtem Token in reset.json speichern
                    file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json', json_encode($reset));
                }
            }
        } else {
            logInfo("Password could not be reset: Passwort confirmation failed");

            echo "<p>Passwort Bestätigung fehlgeschlagen <br>
                     Benötigst du weitere Hilfe, wende dich bitte an den Kunden-Support: <br>
                     <a href='mailto:support@astronomie-kerpen.eu'>support@astronomie-kerpen.eu</a></p>";
            exit;
        }

        logInfo("Password could not be reset");

        //Ohne Weiterleitung, Error anzeigen (Zuruecksetzung fehlgeschlagen)
        echo "<p>Zurücksetzung Fehlgeschlagen: Ungültiger oder abgelaufener Link <br>
                 Benötigst du weitere Hilfe, wende dich bitte an den Kunden-Support: <br>
                 <a href='mailto:support@astronomie-kerpen.eu'>support@astronomie-kerpen.eu</a></p>";
    }
    ?>
</body>

</html>