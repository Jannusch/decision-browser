<?php
// redirect user if no search term was specified
if (!isset($_GET['query'])) {
    header('Location: index.php');
}

//include main config file
include("config.php");
?>

<html>
    <head>
        <title>Search | iFSR Decision Database</title>
    </head>

    <body>
        <header>
            <form action="search.php" method="get">
                <p><input type="text" name="query" value="<?php echo htmlspecialchars($_GET['query']); ?>" /></p>
                <input type="submit" />
            </form>
        </header>

        <hr />

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
                    <p class="heading"><?php 
                    if ($row['money_limit'] != NULL) { 
                        print("Finanzrahmen ". $row['decision_id'] ." (". $row['money_limit'] ."€)"); 
                    } else {
                        print("Beschluss ". $row['decision_id']);
                    } ?></p>
                    <p class="outcome"><?php if ($row['accepted'] == 1) {print("Angenommen.");} else {print("Abgelehnt.");}?></p>
                    <p class="text"><?php print($row['text']); ?></p>
                    <p class="comment"><?php print($row['comment']); ?></p>
                    <p class="votes">[ <?php print($row['v_yes'] ." | ". $row['v_no'] ." | ". $row['v_neutral']); ?> ]</p>
                    <p class="date">Beschlossen am <?php if (strlen($row['link']) != 0) { print("<a href='". $row['link'] ."'>". $row['date'] ."</a>"); } else { print($row['date']); } ?>.
                </article>
                

            <?php
            }

            if (!$returned_something) {
            ?>

            <article>
                <p class="heading">
                    Uh oh! Seems like no decision matched your criteria.
                </p>
            </article>

            <?php
            }
            ?>

        </div>
    </body>
</html>
