<?php

require_once('../resources/function.php');

$db = connexion($errors);

$numero = 1;
$nom = "nom1";
$experience = 5;
$vie = 15;
$defense = 20;
$attaque = 30;

$query = $db->prepare("insert into pokemon(numero, nom, experience, vie, defense, attaque)
                                   values (:numero, :nom, :experience, :vie, :defense, :attaque)");
$query->bindParam(':numero', $numero, PDO::PARAM_INT);
$query->bindParam(':nom', $nom, PDO::PARAM_STR);
$query->bindParam(':experience', $experience, PDO::PARAM_INT);
$query->bindParam(':vie', $vie, PDO::PARAM_INT);
$query->bindParam(':defense', $defense, PDO::PARAM_INT);
$query->bindParam(':attaque', $attaque, PDO::PARAM_INT);

for ($i = 0; $i < 2000; $i++) {
  $query->execute();
  $numero = rand(1, 151);
  $nom = "nom_" . rand(1, 2000);
  $experience = rand(1, 50);
  $vie = rand(1, 50);
  $defense = rand(1, 50);
  $attaque = rand(1, 50);
}
