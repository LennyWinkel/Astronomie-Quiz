<?php
session_start();

//Überprüfe, ob angemeldet
if (!isset($_SESSION['username'])) {
    // Auf Login-Seite umleiten
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

//Ueberpruefe, ob ID gesetzt
if (isset($_GET['id'])) {
    $get_id = $_GET['id'];
} else {
    header("Location: https://astronomie-kerpen.eu/quiz/personal");
    exit;
}

//Inhalt von quiz.json laden
$quiz = [];
if (file_exists('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/quiz.json')) {
    $get_file = file_get_contents('/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/quiz.json', true);
    $quiz = json_decode($get_file, true);
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

//Inhalt von joker/[user].json laden
$joker = [];
//File definieren
$joker_file_path = "/var/www/vhosts/astronomie-kerpen.eu/data.astronomie-kerpen.eu/_data/quiz/joker/" . $_SESSION['username'] . ".json";
if (file_exists($joker_file_path)) {
    $get_file = file_get_contents($joker_file_path, true);
    $joker = json_decode($get_file, true);
}

//Vor- und Nachname laden
foreach ($user as $find_name) {
    if ($_SESSION['username'] == $find_name['username']) {
        $name = htmlspecialchars($find_name['firstname'] . " " . $find_name['lastname'], ENT_QUOTES, 'UTF-8');
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
        //SucheQuizfrage mit entsprechender ID
        foreach ($quiz as $question) {
            if ($question['id'] == (int) $id) {
                //Vergleiche die abgegebene Antwort mit der richtigen Antwort
                if ((int) $submitted_answer == $question['right_answer']) {
                    $rightQuestions += 1;
                }
            }
        }
    }

    return $rightQuestions;
}

$score = rightQuestions() . "/" . answeredQuestions();

//Ueberpruefe, ob abgerufene Quiz-ID schon abgegeben
foreach ($results as $key => $submitted_answer) {
    if ($key == $get_id) {
        //Antwort schon abgegeben

        //Abgerufene Fragen laden
        foreach ($quiz as $question) {
            //Ueberpruefe, ob abgerufene Quiz-ID gleich aktuelle Stelle in Array
            if ($get_id == $question['id']) {
                //Abgegebene + richtige Antwort als Text bestimmen
                $answers = $question["answers"];
                $submitted_answer_txt = $answers[$submitted_answer];

                //Richtige Antwort als Nummer
                $right_answer = $question["right_answer"];
                $right_answer_txt = $answers[$right_answer];

                //Ueberpruefe, ob abgegebene Antwort richtig
                if ($submitted_answer == $question["right_answer"]) {
                    //Abgegebene Antwort richtig
                    $answer_color = "green";
                } else {
                    //Abgegebene Antwort falsch
                    $answer_color = "red";
                }

                //Fragetyp herausfinden und entsprechene Seite anzeigen
                if ($question['type'] == "select") {
                    //Ergebnis-Seite ausgeben
                    echo '
                <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ergebnis | Astronomie-Quiz</title>
            <link rel="stylesheet" href="style.css">
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    
        </head>
        <body>
            <header>
                <div><p class="userline"> ' . $name . ' </p><p class="score"> ' . $score . ' Fragen richtig beantwortet</p></div>
                <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span
                    class="material-symbols-outlined">logout</span>Abmelden</button></a>
            </header>
        
            <div class="back">
                <a href="https://astronomie-kerpen.eu/quiz/personal" class="link"><button class="button"><span class="material-symbols-outlined">arrow_back</span>Zurück</button></a>
            </div>
        
            <main>
                <div class="result">
                    <div class="headline">
                        <h1>Ergebnis von Frage ' . $get_id . '</h1>
                    </div>
        
                    <div class="question">
                        <p> ' . $question['question'] . '</p>
                    </div>
        
                    <div class="answer ' . $answer_color . ' ">
                        <p class="submitted_answer">Deine Antwort: ' . $submitted_answer_txt . ' </p>
                        <p class="right_answer">Richtige Antwort: ' . $right_answer_txt . ' </p>
                    </div>
                </div>
            </main>
        
            <footer>
                <p>&copy; 2024 &bull; Astronomie-AG</p>
        
                <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>
        
                <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
            </footer>
        </body>
        </html>';
                } else if ($question['type'] == "picture_question") {
                    //Ergebnis-Seite ausgeben
                    echo '
                <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Ergebnis | Astronomie-Quiz</title>
            <link rel="stylesheet" href="style.css">
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
            <link rel="stylesheet"
                href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    
        </head>
        <body>
            <header>
                <div><p class="userline"> ' . $name . ' </p><p class="score"> ' . $score . ' Fragen richtig beantwortet</p></div>
                <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span
                    class="material-symbols-outlined">logout</span>Abmelden</button></a>
            </header>
        
            <div class="back">
                <a href="https://astronomie-kerpen.eu/quiz/personal" class="link"><button class="button"><span class="material-symbols-outlined">arrow_back</span>Zurück</button></a>
            </div>
        
            <main>
                <div class="result">
                    <div class="headline">
                        <h1>Ergebnis von Frage ' . $get_id . '</h1>
                    </div>
        
                    <div class="question">
                        <p> ' . $question['question'] . '</p>
                        <img src=" ' . $question['picture'] . ' ">
                    </div>
        
                    <div class="answer ' . $answer_color . ' ">
                        <p class="submitted_answer">Deine Antwort: ' . $submitted_answer_txt . ' </p>
                        <p class="right_answer">Richtige Antwort: ' . $right_answer_txt . ' </p>
                    </div>
                </div>
            </main>
        
            <footer>
                <p>&copy; 2024 &bull; Astronomie-AG</p>
        
                <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>
        
                <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
            </footer>
        </body>
        </html>';
                }
            }
        }
        exit;
    }
}

//Ueberpruefe, ob abgerufene Quiz-ID gültig ist
if (!in_array($get_id, $questionIDs)) {
    if ($get_id < $questionIDs[0]) {
        //Frage abgelaufen
        echo '
        <!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnis | Astronomie-Quiz</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    
</head>
<body>
    <header>
        <div><p class="userline"> ' . $name . ' </p><p class="score"> ' . $score . ' Fragen richtig beantwortet</p></div>
        <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span
            class="material-symbols-outlined">logout</span>Abmelden</button></a>
    </header>

    <div class="back">
        <a href="https://astronomie-kerpen.eu/quiz/personal" class="link"><button class="button"><span class="material-symbols-outlined">arrow_back</span>Zurück</button></a>
    </div>

    <main>

        <p>Du kannst diese Frage leider nicht mehr beantworten.</p>
        
    </main>

    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>

        <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>
</html>';
    } else if ($get_id > $questionIDs[2]) {
        //ID erst in Zukunft gültig
        echo '
        <!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ergebnis | Astronomie-Quiz</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />    
</head>
<body>
    <header>
        <div><p class="userline"> ' . $name . ' </p><p class="score"> ' . $score . ' Fragen richtig beantwortet</p></div>
        <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span
            class="material-symbols-outlined">logout</span>Abmelden</button></a>
    </header>

    <div class="back">
        <a href="https://astronomie-kerpen.eu/quiz/personal" class="link"><button class="button"><span class="material-symbols-outlined">arrow_back</span>Zurück</button></a>
    </div>

    <main>

        <p>Habe noch etwas Geduld: Du kannst diese Frage jetzt noch nicht beantworten.</p>
        
    </main>

    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>

        <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>
</html>';
    }
    exit;
}



//Abgerufene Frage laden
foreach ($quiz as $question) {
    //Header definieren
    $header_content = '
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Astronomie-Quiz</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="script.js"></script>
</head>
<body>
    <header>
        <div><p class="userline"> ' . $name . ' </p><p class="score"> ' . $score . ' Fragen richtig beantwortet</p></div>
        <a href="https://astronomie-kerpen.eu/quiz/personal/logout.php" class="logout-link"><button class="logout"><span class="material-symbols-outlined">logout</span>Abmelden</button></a>
    </header>

    <div class="back">
        <a href="https://astronomie-kerpen.eu/quiz/personal" class="link"><button class="button"><span class="material-symbols-outlined">arrow_back</span>Zurück</button></a>
    </div>

    <main>
        <div class="form_container">
            <form action="" method="post" class="quiz_form">';

    //Footer definieren
    $footer_standard = '
                </div>
                <button type="submit" name="submit">Antwort abgeben</button>
            </form>
        </div>
        <div class="joker">
                <p class="jokerline">Joker</p>
                        <div class="50-50">
                            <button onclick="openConfirm()">Joker verwenden</button>
                        </div>
        </div>
        <div id="jokerConfirm" class="joker_confirm">
                <div class="joker_conf_content">
                        <p>Du hast insgesamt 6 Joker, jeden davon kannst du beliebig einsetzen.<br> Es werden sofort 2 falsche Antworten aussortiert, sodass du nur noch zwei Antworten übrig hast.<br> Willst du den Joker wirklich verwenden?</p>
                        <form action="" method="POST">
                                <button type="submit" name="submit_joker" class="joker_button joker_button_confirm" onclick="useJoker()">Joker verwenden</button>
                                <button class="joker_button joker_button_cancel" onclick="closeJoker()">Joker nicht verwenden</button>
                        </form>
                </div>
         </div>
    </main>
    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>

        <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>
</html>';

    //Joker definieren
    $footer_without_joker = '
                </div>
                <button type="submit" name="submit">Antwort abgeben</button>
            </form>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p  class="footer-line">Technische Umsetzung durch Leonard Winkel</p>

        <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>
</html>';

    //Joker error definieren
    $footer_joker_error = '
                </div>
                <button type="submit" name="submit">Antwort abgeben</button>
            </form>
        </div>
        <div class="joker">
                <p class="jokerline">Joker</p>
                <div class="50-50">
                    <p>Du hast bereits alle 6 Joker verwendet.</p>
                </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 &bull; Astronomie-AG</p>

        <p  class="footer-line">Technische Umsetzung durch <a href="https://lenny-winkel.de" class="link">Leonard Winkel</a></p>

        <p  class="footer-line"><a href="https://legal.astronomie-kerpen.eu/imprint.html" class="link">Impressum</a> | <a href="https://legal.astronomie-kerpen.eu/privacy-policy.html" class="link">Datenschutz</a></p>
    </footer>
</body>
</html>';

    //Ueberpruefe, ob abgerufene Quiz-ID gleich Stelle in Array
    if ($get_id == $question['id']) {

        //Ueberpruefe, ob Joker verwendet
        foreach ($joker as $joker_isset) {
            //Ueberpruefe, ob abgerufene Frage-ID = aktuelle Stelle in Array
            if ($get_id == $joker_isset[0]) {
                $joker_used = true;
            }
        }
        //Fragetyp herausfinden und entsprechende Seite anzeigen
        if ($question['type'] == "select") {
            echo $header_content;

            //Frage ausgeben
            echo '<div class="question">
                      <h1> ' . $question['question'] . ' </h1>
                  </div>

                  <div class="answers">';

            //Antwortmoeglichkeiten ausgeben, Joker beachten
            if ($joker_used == true) {
                //Joker verwendet
                $joker_ids = $question["joker"];
                foreach ($question['answers'] as $index => $print_answer) {
                    if ($index == $joker_ids[0] or $index == $joker_ids[1]) {
                        //Antwortmoeglichkeit durchstreichen
                        echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '" disabled><label for="option-' . $index . '" class="line-trough"> ' . $print_answer . ' </label><br>';
                    } else {
                        //Antwortmoeglichkeit auswaehlbar
                        echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '"><label for="option-' . $index . '"> ' . $print_answer . ' </label><br>';
                    }
                }

                //Footer ohne Joker ausgeben
                echo $footer_without_joker;
            } else {
                //Joker nicht verwendet
                foreach ($question['answers'] as $index => $print_answer) {
                    echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '"><label for="option-' . $index . '"> ' . $print_answer . ' </label><br>';
                }

                //Ueberpruefe, ob 6 Joker verwendet
                foreach ($joker as $joker_control) {
                    $joker_number += 1;
                }

                if ($joker_number >= 6) {
                    //Alle Joker verwendet
                    echo $footer_joker_error;
                } else {
                    //Footer ausgeben
                    echo $footer_standard;
                }
            }

            //Ueberpruefe, ob Frage abgesendet wurde
            if (isset($_POST['submit'])) {
                //Antwort in Array speichern
                $new_result = [
                    $get_id => $_POST['option']
                ];
                $results[$get_id] = $_POST['option'];
                file_put_contents($file_path, json_encode($results));

                //Auf Personal-Seite umleiten
                header("Location: https://astronomie-kerpen.eu/quiz/personal");
                exit;
            }
        } else if ($question['type'] == "picture_question") {
            echo $header_content;

            //Frage ausgeben
            echo '<div class="question">
                      <h1> ' . $question['question'] . ' </h1>
                      <img src=" ' . $question['picture'] . ' ">
                  </div>

                  <div class="answers">';

            //Antwortmoeglichkeiten ausgeben, Joker beachten
            if ($joker_used == true) {
                //Joker verwendet
                $joker_ids = $question["joker"];
                foreach ($question['answers'] as $index => $print_answer) {
                    if ($index == $joker_ids[0] or $index == $joker_ids[1]) {
                        //Antwortmoeglichkeit durchstreichen
                        echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '" disabled><label for="option-' . $index . '" class="line-trough"> ' . $print_answer . ' </label><br>';
                    } else {
                        //Antwortmoeglichkeit auswaehlbar
                        echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '"><label for="option-' . $index . '"> ' . $print_answer . ' </label><br>';
                    }
                }

                //Footer ohne Joker ausgeben
                echo $footer_without_joker;
            } else {
                //Joker nicht verwendet
                foreach ($question['answers'] as $index => $print_answer) {
                    echo '<input type="radio" class="radio_button" name="option" value="' . $index . '" id="option-' . $index . '"><label for="option-' . $index . '"> ' . $print_answer . ' </label><br>';
                }

                //Ueberpruefe, ob 6 Joker verwendet
                foreach ($joker as $joker_control) {
                    $joker_number += 1;
                }

                if ($joker_number >= 6) {
                    //Alle Joker verwendet
                    echo $footer_joker_error;
                } else {
                    //Footer ausgeben
                    echo $footer_standard;
                }
            }

            //Ueberpruefe, ob Frage abgesendet wurde
            if (isset($_POST['submit'])) {
                //Antwort in Array speichern
                $new_result = [
                    $get_id => $_POST['option']
                ];
                $results[$get_id] = $_POST['option'];
                file_put_contents($file_path, json_encode($results));

                //Auf Personal-Seite umleiten
                header("Location: https://astronomie-kerpen.eu/quiz/personal");
                exit;
            }
        }

        //Ueberpruefe, ob Joker-Anfrage abgesendet wurde
        if (isset($_POST["submit_joker"])) {
            foreach ($joker as $joker_control) {
                $joker_number += 1;
            }

            if (!$joker_number >= 6) {
                //Joker in joker/[user].json vermerken
                $new_joker = [
                    $get_id
                ];
                array_push($joker, $new_joker);
                file_put_contents($joker_file_path, json_encode($joker));

                //Seite neuladen
                header("Location: " . $_SERVER["PHP_SELF"]);
                exit;
            }
        }
    }
}

?>