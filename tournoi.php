<?php
// --- CONNEXION PDO ---
$pdo = new PDO(
    "mysql:host=localhost;dbname=tournoi;charset=utf8",
    "root",
    ""
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// --- CREATION DES TABLES ---
$pdo->query("CREATE TABLE IF NOT EXISTS equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
)");

$pdo->query("CREATE TABLE IF NOT EXISTS matchs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipe1 INT NOT NULL,
    equipe2 INT NOT NULL,
    score1 INT NOT NULL,
    score2 INT NOT NULL
)");

// --- AJOUT D'UNE EQUIPE ---
if (isset($_POST["ajouter_equipe"])) {
    $nom = htmlspecialchars($_POST["nom"]);
    $pdo->query("INSERT INTO equipes (nom) VALUES ('$nom')");
}

// --- SAISIE D'UN MATCH ---
$erreur_match = "";
if (isset($_POST["ajouter_match"])) {
    $eq1    = (int) $_POST["equipe1"];
    $eq2    = (int) $_POST["equipe2"];
    $score1 = (int) $_POST["score1"];
    $score2 = (int) $_POST["score2"];

    if ($eq1 == $eq2) {
        $erreur_match = "Une équipe ne peut pas jouer contre elle-même.";
    } else {
        $pdo->query("INSERT INTO matchs (equipe1, equipe2, score1, score2) VALUES ('$eq1', '$eq2', '$score1', '$score2')");
    }
}

// --- SUPPRESSION D'UN MATCH ---
if (isset($_POST["supprimer_match"])) {
    $id = (int) $_POST["id_match"];
    $pdo->query("DELETE FROM matchs WHERE id = $id");
}

// --- RECUPERATION DES EQUIPES ---
$equipes = [];
$stmt = $pdo->query("SELECT * FROM equipes");
$liste = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($liste as $ligne) {
    $equipes[$ligne["id"]] = $ligne["nom"];
}

// --- RECUPERATION DES MATCHS ---
$stmt = $pdo->query("SELECT * FROM matchs");
$matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- CALCUL DU CLASSEMENT ---
$classement = [];

foreach ($equipes as $id => $nom) {
    $classement[$id] = [
        "nom"    => $nom,
        "pts"    => 0,
        "joues"  => 0,
        "gagnes" => 0,
        "nuls"   => 0,
        "perdus" => 0,
        "bp"     => 0,
        "bc"     => 0
    ];
}

foreach ($matchs as $match) {
    $id1 = $match["equipe1"];
    $id2 = $match["equipe2"];
    $s1  = $match["score1"];
    $s2  = $match["score2"];

    $classement[$id1]["joues"]++;
    $classement[$id2]["joues"]++;
    $classement[$id1]["bp"] += $s1;
    $classement[$id1]["bc"] += $s2;
    $classement[$id2]["bp"] += $s2;
    $classement[$id2]["bc"] += $s1;

    if ($s1 > $s2) {
        $classement[$id1]["pts"]    += 3;
        $classement[$id1]["gagnes"]++;
        $classement[$id2]["perdus"]++;
    } else if ($s2 > $s1) {
        $classement[$id2]["pts"]    += 3;
        $classement[$id2]["gagnes"]++;
        $classement[$id1]["perdus"]++;
    } else {
        $classement[$id1]["pts"]  += 1;
        $classement[$id2]["pts"]  += 1;
        $classement[$id1]["nuls"]++;
        $classement[$id2]["nuls"]++;
    }
}

// TRI DU CLASSEMENT PAR POINTS
$tri = [];
$dejaPris = [];

for ($i = 0; $i < count($classement); $i++) {
    $maxPts = -1;
    $maxId  = -1;

    foreach ($classement as $id => $equipe) {
        if (!in_array($id, $dejaPris) && $equipe["pts"] > $maxPts) {
            $maxPts = $equipe["pts"];
            $maxId  = $id;
        }
    }

    $tri[]      = $classement[$maxId];
    $dejaPris[] = $maxId;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tournoi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        td, th { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f0f0f0; }
        form { margin-bottom: 20px; }
        input[type=text], input[type=number], select { padding: 5px; margin: 4px; }
        input[type=submit] { padding: 6px 12px; cursor: pointer; }
        .erreur { color: red; }
    </style>
</head>
<body>

<h1>Tournoi de football</h1>

<!-- === AJOUTER UNE EQUIPE-->
<h2>Ajouter une équipe</h2>
<form method="post">
    Nom de l'équipe : <input type="text" name="nom" required>
    <input type="submit" name="ajouter_equipe" value="Ajouter">
</form>

<!--SAISIR UN MATCH-->
<h2>Saisir un match</h2>
<?php if (count($equipes) < 2): ?>
    <p style="color:orange;">Ajoutez au moins 2 équipes pour saisir un match.</p>
<?php else: ?>
    <?php if ($erreur_match != ""): ?>
        <p class="erreur"><?php echo $erreur_match; ?></p>
    <?php endif; ?>
    <form method="post">
        Équipe 1 :
        <select name="equipe1">
            <?php foreach ($equipes as $id => $nom): ?>
                <option value="<?php echo $id; ?>"><?php echo $nom; ?></option>
            <?php endforeach; ?>
        </select>

        Score : <input type="number" name="score1" min="0" value="0" style="width:50px;">
        &nbsp;-&nbsp;
        <input type="number" name="score2" min="0" value="0" style="width:50px;"> : Score

        Équipe 2 :
        <select name="equipe2">
            <?php foreach ($equipes as $id => $nom): ?>
                <option value="<?php echo $id; ?>"><?php echo $nom; ?></option>
            <?php endforeach; ?>
        </select>

        <input type="submit" name="ajouter_match" value="Enregistrer le match">
    </form>
<?php endif; ?>

<!--RESULTATS DES MATCHS-->
<h2>Résultats des matchs</h2>
<?php if (count($matchs) == 0): ?>
    <p>Aucun match enregistré.</p>
<?php else: ?>
    <table>
        <tr>
            <th>Équipe 1</th>
            <th>Score</th>
            <th>Équipe 2</th>
            <th>Résultat</th>
            <th>Action</th>
        </tr>
        <?php foreach ($matchs as $match): ?>
            <?php
                $nom1 = $equipes[$match["equipe1"]];
                $nom2 = $equipes[$match["equipe2"]];
                $s1   = $match["score1"];
                $s2   = $match["score2"];

                if ($s1 > $s2) {
                    $resultat = $nom1 . " gagne";
                } else if ($s2 > $s1) {
                    $resultat = $nom2 . " gagne";
                } else {
                    $resultat = "Match nul";
                }
            ?>
            <tr>
                <td><?php echo $nom1; ?></td>
                <td><?php echo $s1 . " - " . $s2; ?></td>
                <td><?php echo $nom2; ?></td>
                <td><?php echo $resultat; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="id_match" value="<?php echo $match["id"]; ?>">
                        <input type="submit" name="supprimer_match" value="Supprimer">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<!--CLASSEMENT-->
<h2>Classement</h2>
<?php if (count($tri) == 0): ?>
    <p>Aucune équipe enregistrée.</p>
<?php else: ?>
    <table>
        <tr>
            <th>#</th>
            <th>Équipe</th>
            <th>Pts</th>
            <th>MJ</th>
            <th>G</th>
            <th>N</th>
            <th>P</th>
            <th>BP</th>
            <th>BC</th>
            <th>Diff</th>
        </tr>
        <?php
        $position = 1;
        foreach ($tri as $equipe):
            $diff = $equipe["bp"] - $equipe["bc"];
        ?>
            <tr>
                <td><?php echo $position; ?></td>
                <td><?php echo $equipe["nom"]; ?></td>
                <td><strong><?php echo $equipe["pts"]; ?></strong></td>
                <td><?php echo $equipe["joues"]; ?></td>
                <td><?php echo $equipe["gagnes"]; ?></td>
                <td><?php echo $equipe["nuls"]; ?></td>
                <td><?php echo $equipe["perdus"]; ?></td>
                <td><?php echo $equipe["bp"]; ?></td>
                <td><?php echo $equipe["bc"]; ?></td>
                <td><?php echo ($diff > 0 ? "+" . $diff : $diff); ?></td>
            </tr>
        <?php
            $position++;
        endforeach;
        ?>
    </table>
    <p><small>MJ = Matchs joués | G = Gagné | N = Nul | P = Perdu | BP = Buts pour | BC = Buts contre | Diff = Différence de buts</small></p>
<?php endif; ?>

</body>
</html>
