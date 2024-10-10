<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Anmeldung</title>
</head>

<body>
    <div class="container">
        <form action="index.php" method="post">
            <h2>Anmeldung</h2>
            <p class="register">Noch kein Konto? <a href="https://astronomie-kerpen.eu/quiz/account/register" class="link">Jetzt registrieren</a></p>

            <label for="username">Benutzername:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Passwort:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit" name="submit">Anmelden</button>
        </form>
        <p>&copy; 2024 Astronomie-AG | <a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
        
        <a href="https://astronomie-kerpen.eu/quiz/account/passwordreset" class="link">Passwort vergessen?</a><br>
    </div>

    <?php
    session_start();

    include '/var/www/vhosts/astronomie-kerpen.eu/httpdocs/includes/logging.php';

    //Ueberpruefe, ob Anmeldung abgesendet wurde
    if (isset($_POST['submit'])) {

        //Inhalt von user.json laden
        $user = [];
        if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json')) {
            $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', true);
            $user = json_decode($get_file, true);
        }

        //name_exist(_control) auf 0 setzen (Wert geben)
        $name_exist = 0;
        $name_exist_control = 0;

        foreach ($user as $login) {
            //Addiere 1, um zu ueberpruefen, ob Benutzername vorhanden
            $name_exist ++;

            //Ueberpruefe, ob richtiger user
            if ($_POST['username'] == $login['username']) {
                if (password_verify($_POST['password'], $login['password'])) {
                    //Login in Session
                    $_SESSION['username'] = $_POST['username'];

                    logInfo("User " . $_POST['username'] . " successfully logged in");

                    header("Location: https://astronomie-kerpen.eu/quiz/personal");
                    exit;
                } else {
                    logInfo("User " . $_POST['username'] . " could not be logged in: wrong password");

                    header("Location: https://astronomie-kerpen.eu/quiz/account/login/error_log_password.php");
                    exit;
                }
            } else {
                $name_exist_control ++;
            }
        }
        
        //Ueberpuefe, ob Benutzername nicht vorhanden
        if ($name_exist <= $name_exist_control) {
            logInfo("User " . $_POST['username'] . " could not be logged in: Username does not exist");

            header("Location: https://astronomie-kerpen.eu/quiz/account/login/error_log_username.php");
            exit;
        }
    }
    ?>
</body>

</html>