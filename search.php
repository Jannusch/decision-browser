<?php
// redirect user if no search term was specified
if (!isset($_GET['query'])) {
    header('Location: index.php');
}

//include main config file
include("config.php");
?>

<!DOCTYPE html>
<html lang="de">
    <head>
        <title>Suche | iFSR Decision Database</title>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="https://fonts.googleapis.com/css?family=Fira+Sans:300,700" rel="stylesheet">
        <link href="style.css" rel="stylesheet" />
    </head>

    <body>
        <header>
            <h1><a href="index.php"><img src="logo_header.png" alt="iFSR Decision Database Browser" title="iFSR Decision Database Browser" /></a> Decision Database Browser</h1>
            <form action="search.php" method="get">
                <input type="text" name="query" placeholder="Suche" value="<?php echo htmlspecialchars($_GET['query']); ?>" />
            </form>
        </header>

        <div class="content">

            <?php
            // open a read-only connection to the database and receive all matching rows
            $db = new SQLite3($db_path, SQLITE3_OPEN_READONLY) or die('Unable to open database');
            $escaped_query = SQLite3::escapeString($_GET['query']);
            $smt = $db->prepare("SELECT * FROM decisions WHERE decision_id LIKE '%". $escaped_query ."%' OR text LIKE '%". $escaped_query ."%' OR comment LIKE '%". $escaped_query ."%' OR date LIKE '%". $escaped_query ."%' ORDER BY decision_id DESC");
            $result = $smt->execute();
            ?>

            <div class="decisionlist">
                <?php
                $returned_something = false;
                while ($row = $result->fetchArray(1)) {
                    $returned_something = true;
                    // TODO: Add Link to the Detail View, shorten the text, add "more" link.
                    ?>
                    <article>
                        <div class="heading"><h2><?php 
                        if ($row['money_limit'] != NULL) { 
                            print("Finanzrahmen ". $row['decision_id'] ." (". $row['money_limit'] ."€)"); 
                        } else {
                            print("Beschluss ". $row['decision_id']);
                        } ?></h2>
                        <?php if ($row['accepted'] == 1) {print('<div class="outcome decided">Angenommen</div>');} else {print('<div class="outcome rejected">Abgelehnt</div>');}?></div>
                        <p class="date">Beschlossen am <?php if (strlen($row['link']) != 0) { print("<a href='". $row['link'] ."'>". $row['date'] ."</a>"); } else { print($row['date']); } ?>.</p>
                        <p class="text"><?php print($row['text']); ?></p>
                        <?php if (strlen($row['rationale']) != 0) {print('<p class="rationale"><b>Begründung:</b> '. $row['rationale'] .'</p>');} ?>
                        <div class="meta">
                            <p class="comment"><?php print($row['comment']); ?></p>
                            <?php if ($row['accepted'] == 1 && $row['v_yes'] == 0) { ?>
                                <p class="votes" title="Der Beschluss wurde mehrheitlich gefasst.">[ <span class="decided">Mehrheit</span> ]</p>
                            <?php } else { ?>
                                <p class="votes" title="Stimmen: [dafür | dagegen | Enthaltungen]">[ <?php print('<span class="decided">'. $row['v_yes'] .'</span> | <span class="rejected">'. $row['v_no'] ."</span> | ". $row['v_neutral']); ?> ]</p>
                            <?php } ?>
                        </div>
                    </article>
                    

                <?php
                }

                if (!$returned_something) {
                ?>

                <article>
                    <h2>Keine Ergebnisse</h2>
                    <p class="heading">
                        Es wurden keine Beschlüsse gefunden, die auf deine Suchkriterien passen. :(
                    </p>

                    <a href="index.php">Zurück zur Startseite.</a>
                </article>

                <?php
                }
                ?>

            </div>
        </div>

        <footer>
            Diese Datenbank ist ein Projekt des <a href="https://www.ifsr.de">FSR Informatik</a>. – <a href="https://www.ifsr.de/fsr:kontakt">Impressum</a> – Dieses Projekt ist auf <a href="https://github.com/fsr/decision-browser">GitHub</a>.
        </footer>
    </body>
</html>
