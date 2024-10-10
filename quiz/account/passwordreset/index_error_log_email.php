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
        <form action="index.php" method="post">
            <h2>Passwort-Zurücksetzung</h2>

            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">E-Mail:</label> <div class="error">Falsche E-Mail</div>
            <input type="email" id="email" name="email" required>

            <button type="submit" name="submit">Passwort zurücksetzen</button>
        </form>
        <p>&copy; 2024 Astronomie-AG | <a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
        
        <a href="https://astronomie-kerpen.eu/quiz/account/login" class="link">Anmelden</a><br>
        <a href="https://astronomie-kerpen.eu/quiz/account/register" class="link">Noch kein Konto?</a>
    </div>

    <?php
    include '/var/www/vhosts/astronomie-kerpen.eu/httpdocs/includes/logging.php';

    if(isset($_POST['submit'])) {
        //Inhalt von user.json laden
        $user = [];
        if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json')) {
            $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', true);
            $user = json_decode($get_file, true);
        }

        //$number_control, $mail_error und $name_error auf 0 setzen (Wert geben)
        $number_control = 0;
        $mail_error = 0;
        $name_error = 0;
        foreach ($user as $control) {
            $number_control ++;
            //Username finden
            if ($_POST['username'] == $control['username']) {
                //Ueberpruefe, ob E-Mail richtig
                if ($_POST['email'] == $control['email']) {
                    //Token erstellen
                    $token = bin2hex(random_bytes(64));

                    //Inhalt von reset.json laden
                    $reset = [];
                    if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json')) {
                        $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json', true);
                        $reset = json_decode($get_file, true);
                    }

                    foreach ($reset as $key => $link_vrf) {
                        //Ueberpruefe, ob user bereits Link gefordert hat
                        if ($link_vrf['username'] == $control['username']) {
                            //Alten Reset-Request löschen
                            unset($reset[$key]);
                        }
                    }

                    //Token in reset.json speichern
                    $new_reset = [
                        'username' => $control['username'],
                        'token' => $token,
                        'timestamp' => time()
                    ];
                    array_push($reset, $new_reset);
                    file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/reset.json', json_encode($reset));

                    logInfo("Token successfully created");

                    //Reset-Mail an Benutzer versenden
                    $mail = $_POST['email'];
                    $subject = "Passwort Zurücksetzen";
                    $message = "Hallo " . $control['firstname'] . "!
                            Um dein Passwort zurückzusetzen, klicke auf den folgenden Link: 
                            https://astronomie-kerpen.eu/quiz/account/passwordreset/reset.php?user=" . $control['username'] . "&token=" . $token . "
                            Viel Spaß beim Rätseln!
                            
                            ----------------------------------------------------------------------------------------------
                            Diese E-Mail wurde automatisiert verschickt.
                            Bitte Antowrten Sie nicht an diese Adresse.
                            Falls Sie Fragen zur Registrierung, dem Quiz oder der AG haben, wenden Sie sich bitte an support@astronomie-kerpen.eu.
                            Die Bearbeitungszeit beträgt 1-3 Tage.";
                    $from = "From: Konto-Serivce <account@astronomie-kerpen.eu>";

                    mail($mail, $subject, $message, $from);

                    header("Location: https://astronomie-kerpen.eu/quiz/account/passwordreset/processing.html");
                    exit;
                } else {
                    $mail_error ++;
                }
            } else {
                $name_error ++;
            }
        }

        if ($number_control <= $mail_error) {
            logInfo("Token could not be created: wrong E-Mail");
            
            header("Location: https://astronomie-kerpen.eu/quiz/account/passwordreset/index_error_log_email.php");
            exit;
        }

        if ($number_control <= $name_error) {
            logInfo("Token could not be created: wrong username");

            header("Location: https://astronomie-kerpen.eu/quiz/account/passwordreset/index_error_log_username.php");
            exit;
        }
    }
    ?>
</body>

</html>