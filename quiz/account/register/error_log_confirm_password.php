<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Registrierung</title>
</head>

<body>
    <div class="container">
        <form action="index.php" method="post">
            <h2>Registrierung</h2>

            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">E-Mail:</label>
            <input type="email" id="email" name="email" required>

            <label for="firstname">Vorname:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="lastname">Nachname:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>

            <div class="error_container"><label for="confirm_password">Passwort bestätigen:</label>
                <div class="error">Passwort überprüfen</div>
            </div>
            <input type="password" id="confirm_password" name="confirm_password" required>

            <button type="submit" name="submit">Registrieren</button>
        </form>
        <p>&copy; 2024 Astronomie-AG | <a href="https://gymnasiumkerpen.eu/impressum" class="link">Impressum</a> | <a href="https://gymnasiumkerpen.eu/datenschutz" class="link">Datenschutz</a></p>

        <a href="https://astronomie-kerpen.eu/quiz/account/passwordreset" class="link">Passwort vergessen?</a><br>
        <a href="https://astronomie-kerpen.eu/quiz/account/login" class="link">Anmelden</a>
    </div>

    <?php

    include '/var/www/vhosts/astronomie-kerpen.eu/httpdocs/includes/logging.php';

    //Ueberpruefe, ob Registrierung abgesendet wurde
    if (isset($_POST['submit'])) {
        //Ueberpruefe, ob Passwort bestaetigung korrekt
        if ($_POST['password'] == $_POST['confirm_password']) {

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

            //Ueberpruefe, ob es Benutzernamen oder E-Mail schon gibt
            $name_error = false;
            $mail_error = false;
            foreach ($user as $control_usr) {
                if ($_POST['username'] == $control_usr['username']) {
                    $name_error = true;
                }

                if ($_POST['email'] == $control_usr['email']) {
                    $mail_error = true;
                }
            }

            foreach ($confirm as $control_conf) {
                if ($_POST['username'] == $control_conf['username']) {
                    $name_error = true;
                }

                if ($_POST['email'] == $control_conf['email']) {
                    $mail_error = true;
                }
            }

            if ($name_error == false) {
                if ($mail_error == false) {
                    //Token für Mail-Identifizierung
                    $conf_token = bin2hex(random_bytes(32));

                    //Passwort hashen
                    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

                    //Timestamp speichern
                    $timestamp = time();

                    //Vorläufige Benutzerdaten, Token und Timestamp in confirm.json speichern
                    $new_confirm = [
                        'username' => $_POST['username'],
                        'email' => $_POST['email'],
                        'password' => $hashed_password,
                        'firstname' => $_POST['firstname'],
                        'lastname' => $_POST['lastname'],
                        'token' => $conf_token,
                        'timestamp' => $timestamp
                    ];
                    array_push($confirm, $new_confirm);
                    file_put_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/confirm.json', json_encode($confirm));

                    //Confirm-Mail an Benutzer versenden
                    $mail = $_POST['email'];
                    $subject = "E-Mail identifizieren";
                    $message = "Hallo " . $_POST['firstname'] . "!
                            Um deine E-Mail-Adresse zu bestätigen, klicke auf den folgenden Link: 
                            https://astronomie-kerpen.eu/quiz/account/emailconfirm?user=" . $_POST['username'] . "&token=" . $conf_token . "
                            Viel Spaß beim Rätseln!
                            
                            ----------------------------------------------------------------------------------------------
                            Diese E-Mail wurde automatisiert verschickt.
                            Bitte Antowrten Sie nicht an diese Adresse.
                            Falls Sie Fragen zur Registrierung, dem Quiz oder der AG haben, wenden Sie sich bitte an support@astronomie-kerpen.eu.
                            Die Bearbeitungszeit beträgt 1-3 Tage.";
                    $from = "From: Konto-Service <account@astronomie-kerpen.eu>";

                    mail($mail, $subject, $message, $from);

                    logInfo("User " . $_POST['username'] . " successfully registered.");

                    header("Location: https://astronomie-kerpen.eu/quiz/account/register/processing.html");
                    exit;
                } else {
                    logInfo("User " . $_POST['username'] . " could not be registered: E-Mail already exists");

                    header("Location: https://astronomie-kerpen.eu/quiz/account/register/error_log_email.php");
                    exit;
                }
            } else {
                logInfo("User " . $_POST['username'] . " could not be registered: username already exists");

                header("Location: https://astronomie-kerpen.eu/quiz/account/register/error_log_username.php");
                exit;
            }
        } else {
            logInfo("User " . $_POST['username'] . " could not be registered: password confirmation failed");

            header("Location: https://astronomie-kerpen.eu/quiz/account/register/error_log_confirm_password.php");
            exit;
        }
    }
    ?>
</body>

</html>