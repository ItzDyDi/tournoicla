<?php
require 'connexion.php';
$stmt = $pdo->query('SELECT * FROM resultat_tournoi');
$résultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tournoi</title>
</head>
<body>
<h1>Résultats tournoi</h1>
<table border="1" cellpadding="6">
    <tr>
        <th>ID</th>
        <th>Equipe 1</th>
        <th>Equipe 2</th>
        <th>Score 1</th>
        <th>Score 2</th>
        <th>Vainqueur</th>
    </tr>
    <?php foreach ($résultats as $résultat): ?>
        <?php
        if ($résultat['score_1'] > $résultat['score_2']) {
            $vainqueur = $résultat['equipe_1'];
        } elseif ($résultat['score_1'] < $résultat['score_2']) {
            $vainqueur = $résultat['equipe_2'];
        } else {
            $vainqueur = "Égalité";
        }
?>
        <tr>
            <td><?= $résultat['id'] ?></td>
            <td><?= $résultat['equipe_1'] ?></td>
            <td><?= $résultat['equipe_2'] ?></td>
            <td><?= $résultat['score_1'] ?></td>
            <td><?= $résultat['score_2'] ?></td>
            <td><?= $vainqueur ?></td>
        </tr>
    <?php endforeach; ?>
</table>
<?php

$classement = [];

foreach ($résultats as $résultat) {
    $equipe1 = $résultat['equipe_1'];
    $equipe2 = $résultat['equipe_2'];

    if (!isset($classement[$equipe1])) $classement[$equipe1] = 0;
    if (!isset($classement[$equipe2])) $classement[$equipe2] = 0;

    if ($résultat['score_1'] > $résultat['score_2']) {
        $classement[$equipe1]++;
    } elseif ($résultat['score_1'] < $résultat['score_2']) {
        $classement[$equipe2]++;
    }
}

$classement_tab = [];
foreach ($classement as $equipe => $victoires) {
    $classement_tab[] = ['equipe' => $equipe, 'victoires' => $victoires];
}

for ($i = 0; $i < count($classement_tab); $i++) {
    for ($j = 0; $j < count($classement_tab) - 1; $j++) {
        if ($classement_tab[$j]['victoires'] < $classement_tab[$j + 1]['victoires']) {
            $temp = $classement_tab[$j];
            $classement_tab[$j] = $classement_tab[$j + 1];
            $classement_tab[$j + 1] = $temp;
        }
    }
}?>

<h2>Classement</h2>
<table border="1" cellpadding="6">
    <tr>
        <th>Position</th>
        <th>Equipe</th>
        <th>Victoires</th>
    </tr>
    <?php $position = 1; foreach ($classement_tab as $ligne): ?>
    <tr>
        <td><?= $position++ ?></td>
        <td><?= $ligne['equipe'] ?></td>
        <td><?= $ligne['victoires'] ?></td>
    </tr>
<?php endforeach; ?>
</table>
</body>
</html>