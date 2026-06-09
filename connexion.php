<?php
$hote = 'localhost';
$base = 'tournoi';
$user = 'root';
$mdp = ''; // vide par defaut sur XAMPP/WAMP
$pdo = new PDO(
"mysql:host=$hote;dbname=$base;charset=utf8",
$user,
$mdp
);
// Affiche les erreurs SQL (pratique en developpement)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>