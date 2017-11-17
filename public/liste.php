<?php
session_start();   // récupération de la session

$id_dresseur = $_SESSION['id'] ?? null;

// Entête HTML ce require permet de charger toutes les balises d'en-tête de la page HTML
require_once('header.php');

// Fonctions de bases
require_once('../resources/function.php');

if (isset($id_dresseur))
  echo "<h1>Bienvenue dresseur numéro $id_dresseur</h1>";

// Affichage du lien d'insertion
echo '<a href="insert.php" class="btn btn-primary">Ajouter un pokemon</a>';

$search = '';

// Si on veut filtrer la liste
if (isset($_GET['snumero']) || isset($_GET['sname'])) {
  // Récupération des valeurs de filtre
  $snumero = intVal($_GET['snumero'] ?? null);
  $sname = $_GET['sname'] ?? null;

  if (!empty($snumero)) {
    $search .= " AND numero = $snumero";
  }
  if (!empty($sname)) {
    $search .= " AND nom LIKE '%$sname%'";
  }
}

$limit = 5;
// Récupération de la page demandée
$page = intVal($_GET['page'] ?? 1);

// Récupération du nombre de pokemon
$db = connexion($errors);
$query = $db->query("SELECT COUNT(*) nb_pokemon FROM pokemon WHERE 1 = 1 $search");

$count = intVal($query->fetch()['nb_pokemon']); // fetch + récupération de la colonne nb_pokemon + intVal pour obtenir une valeur en entier

// Page maximale
$max_page = intVal(ceil($count / $limit));
// Vérification de la page demandée
if ($page > $max_page)
  $page = $max_page;
elseif ($page < 0)
  $page = 1;

// Calcul de l'offset
$offset = ($page - 1) * $limit;

?>

<form>
  <div class="row">
    <div class="col">
      <input type="number" name="snumero" min="1" class="form-control" placeholder="numero" value="<?php echo $_GET['snumero'] ?? '' ?>">
    </div>
    <div class="col">
      <input type="text" name="sname" class="form-control" placeholder="nom" value="<?php echo $_GET['sname'] ?? '' ?>">
    </div>
    <div class="col">
      <button class="btn btn-outline-primary" type="submit">Filtrer</button>
    </div>
  </div>
</form>

<?php
// Affichage de la liste des pokemon
if (isset($id_dresseur)) {
  afficheMesPokemons($id_dresseur);
} else {
  affichePokemon($limit, $offset, $search);
}

?>

<nav aria-label="Page navigation pokemon">
  <ul class="pagination justify-content-center">
    <?php if ($page > 1) : ?>
    <li class="page-item">
      <a class="page-link" href="?<?php $_GET['page'] = $page - 1; echo http_build_query($_GET) ?>">Précédent</a>
    </li>
    <?php endif; ?>
    <?php if ($page < $max_page) : ?>
    <li class="page-item">
      <a class="page-link" href="?<?php $_GET['page'] = $page + 1; echo http_build_query($_GET) ?>">Suivant</a>
    </li>
    <?php endif; ?>
  </ul>
</nav>

<?php

require_once('footer.php');
