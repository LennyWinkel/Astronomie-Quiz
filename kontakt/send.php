<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['subject'] == "ag") {
        $mail = "support@astronomie-kerpen.eu";
        $subject = "Frage zur Astronomie-AG";
    } else if ($_POST['subject'] == "quiz") {
        $mail = "support@astronomie-kerpen.eu";
        $subject = "Frage zum Quiz";
    } else if ($_POST['subject'] == "account") {
        $mail = "support@astronomie-kerpen.eu";
        $subject = "Frage zum Benutzerkonto";
    } else if ($_POST['subject'] == "feedback") {
        $mail = "support@astronomie-kerpen.eu";
        $subject = "Anregung/Feedback";
    } else if ($_POST['subject'] == "legal") {
        $mail = "legal@astronomie-kerpen.eu";
        $subject = "Rechtliche Anfrage";
    } else if ($_POST['subject'] == "other") {
        $mail = "support@astronomie-kerpen.eu";
        $subject = "Allgemeine Anfrage";
    }

    $message = "Name: " . $_POST['name'] . "
                E-Mail: " . $_POST['email'] . "
                Nachricht: " . $_POST['message'];

    $from = "From: Kontakt <kontakt@astronomie-kerpen.eu>";

    if (mail($mail, $subject, $message, $from)) {
        echo "<p>Die Nachricht wurde erfolgreich versendet. Die Bearbeitungszeit beträgt 1-3 Tage.</p>";
    } else {
        echo "<p>Es gab ein Problem beim Versenden der Nachricht. Bitte versuchen Sie es später erneut oder melden Sie sich bei <a href='mailto:support@astronomie-kerpen.eu'>support@astronomie-kerpen.eu</a></p>";
    }
}
?>