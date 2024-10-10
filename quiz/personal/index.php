<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Startseite | Astronomie-Quiz | Astronomie Kerpen</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
</head>

<body>
    <?php
    session_start();

    //Ueberpruefe, ob nicht Nutzer eingeloggt
    if (!isset($_SESSION['username'])) {
        //Auf Login-Seite umleiten
        header("Location: https://astronomie-kerpen.eu/quiz/account/login");
        exit;
    }

    //Quiz-IDs berechnen
    function getQuestionID($weekNumber, $questionNumber)
    {
        return ($weekNumber - 1) * 5 + $questionNumber;
    }

    //Aktuelle Quiz-Woche basierend auf Startdatum berechnen
    function getCurrentWeek($quizStartDate)
    {
        $startDate = new DateTime($quizStartDate);
        $currentDate = new DateTime();
        $interval = $currentDate->diff($startDate);
        $weeks = floor($interval->days / 7);
        return ($weeks > 0) ? $weeks + 1 : 1;
    }

    //Quiz-Start-Datum definieren
    $quizStartDate = '2024-08-13';

    //getCurrentWeek ausführen, Ergebnis in $currentWeek speichern
    $currentWeek = getCurrentWeek($quizStartDate);

    //IDs für aktuelle Woche speichern
    $questionIDs = [];
    for ($i = 1; $i <= 5; $i++) {
        $questionID = getQuestionID($currentWeek, $i);
        $questionIDs[] = $questionID;
    }

    //Inhalt von user.json laden
    $user = [];
    if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json')) {
        $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/user.json', true);
        $user = json_decode($get_file, true);
    }

    //Inhalt von results/[user].json laden
    $results = [];
    //File definieren
    $file_path = "/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/results/" . $_SESSION['username'] . ".json";
    if (file_exists($file_path)) {
        $get_file = file_get_contents($file_path, true);
        $results = json_decode($get_file, true);
    }

    //Inhalt von quiz.json laden
    $quiz = [];
    if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/quiz.json')) {
        $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/quiz.json', true);
        $quiz = json_decode($get_file, true);
    }

    //Vor- und Nachname
    foreach ($user as $find_name) {
        if ($_SESSION['username'] == $find_name['username']) {
            //Vor- und Nachnamen speichern
            $name = htmlspecialchars($find_name['firstname'] . " " . $find_name['lastname'], ENT_QUOTES, 'UTF-8');
        }
    }

    //Button-Farbe herausfinden
    function buttonColor($id)
    {
        global $results, $questionIDs;

        foreach ($results as $key => $submitted_answer) {
            if ($key == $id or (!in_array($id, $questionIDs) && $id < $questionIDs[0])) {
                return "grey";
            }
        }
    }

    //Anzahl an beantworteten Fragen
    function answeredQuestions()
    {
        global $results;

        return count($results);
    }

    //Anzahl an richtig beantworteten Fragen
    function rightQuestions()
{
    global $results, $quiz;

    $rightQuestions = 0;

    foreach ($results as $id => $submitted_answer) {
        // Suche die Quizfrage mit der entsprechenden ID
        foreach ($quiz as $question) {
            if ($question['id'] == (int)$id) {
                // Vergleiche die abgegebene Antwort mit der richtigen Antwort
                if ((int)$submitted_answer == $question['right_answer']) {
                    $rightQuestions += 1;
                }
            }
        }
    }

    return $rightQuestions;
}

    ?>
    <header>
        <div><p class="userline"><?php echo $name ?></p><p class="score"><?php echo rightQuestions() . "/" . answeredQuestions(); ?> Fragen richtig beantwortet</p></div>
        <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span class="material-symbols-outlined">logout</span>Abmelden</button></a>
    </header>
    <main>
        <?php
        //Quiz-Buttons anzeigen
        for ($i = 1; $i <= 35; $i++)  {
            $button_color = buttonColor($i);
            echo '<a href="question?id=' . $i . '"><button class="quiz ' . $button_color . '">Frage  ' . $i . '</button></a>';
        }
        ?>


    </main>
    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p class="footer-line">Technische Umsetzung durch Leonard Winkel</p>

        <p class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>

</html>