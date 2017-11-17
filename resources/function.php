<?php

// Gestion de la base de donnée
require_once('database.php');

/*
 * formIsSubmit : test si un fomulaire a été soumis
 */
function formIsSubmit($form_name) {
  return (isset($_POST[$form_name]) ? $_POST[$form_name] : '0') === '1';
}

function getVal($value, $default = '') {
  return isset($value) ? $value : $default;
}

function showMessage($message, $type = 'alert-success') {
  $html = "
  <div class=\"alert $type alert-dismissible fade show\" role=\"alert\">
    <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">
      <span aria-hidden=\"true\">&times;</span>
    </button>
    " . htmlspecialchars($message) . "
  </div>
  ";
  echo $html;
}

function attaque($nom_pokemon1, &$pokemon1, $nom_pokemon2, &$pokemon2) {
  // $tour est initialisée à 0 et conservera sa dernière modification à chaque appel de la fonction grâçe au mot clé static
  static $tour = 0;

  echo "<h2> Tour : " . ++$tour . " à " . date('H:i:s') . "</h2>";

  // pokemon1 attaque pokemon2
  echo "<h3>$nom_pokemon1 attaque $nom_pokemon2</h3>";
  if ($pokemon1['attaque'] >= $pokemon2['defense']) {
    // L'attaque est supérieure à la défense : pokemon1 touche
    $coup = $pokemon1['attaque'] - $pokemon2['defense'] + 1; // La valeur du coup est la différence entre l'attaque et la défense
    $pokemon2['pv'] -= $coup;
    if ($pokemon2['pv'] < 0)
      $pokemon2['pv'] = 0;
    echo "<p>$nom_pokemon2 perd $coup PV, il lui reste " . $pokemon2['pv'] . " PV</p>";
  } else {
    // La défense est supérieure à l'attaque, pokemon1 prend la moitié du coup et la défense baisse un peu
    $coup = ($pokemon2['defense'] - $pokemon1['attaque']) / 2;
    $pokemon1['pv'] -= $coup;
    if ($pokemon1['pv'] < 0)
      $pokemon1['pv'] = 0;
    $pokemon2['defense'] -= 1;
    echo "<p>$nom_pokemon2 perd 1 Points de défense, il lui reste " . $pokemon2['defense'] . " Points de défense</p>";
    echo "<p>$nom_pokemon1 râte son attaque ! Il perd $coup Points de vie, il lui reste " . $pokemon1['pv'] . " Points de vie</p>";
  }

  if ($pokemon2['pv'] <= 0)
    echo "<p>$nom_pokemon2 est KO !</p>";
  if ($pokemon1['pv'] <= 0)
    echo "<p>$nom_pokemon1 est KO !</p>";
}

function genereTable($query) {
  $table = "";

  while ($result = $query->fetch()) {
    // Première ligne : affichage des titres de colonnes
    if ($table == "") {
      $table = "
  <table class=\"table table-hover table-responsive-sm\">
    <caption>Liste de tous les pokemons existants</caption>
    <thead>
      <tr>
        <th scope=\"col\">
        </th>
        <th scope=\"col\">
        " . implode('</th><th scope=\"col\">', array_keys($result)) . "
        </th>
      </tr>
    </thead>
    <tbody>
      ";
    }
    // Ajout d'une ligne dans la table
    $table .= "
      <tr>
        <td scope=\"row\">
          <a onclick=\"formSubmit('deletePokemon', 'id_delete', '" . $result['id'] . "');\"><i class=\"fa fa-trash-o fa-fw\" aria-hidden=\"true\"></i></a>
        </td>
        <td>
        " . implode('</td><td>', $result) . "
        </td>
      </tr>
    ";
  }

  if($table == "") {
    null;//$errors[] = "Aucune ligne trouvée";
  } else {
    $table .= "
    </tbody>
  </table>
    ";
  }

  return $table;
}


function afficheMesPokemons($id_dresseur) {
  // Connexion à la base
  if (!$db = connexion($msg))
    die("Erreur : " . implode($msg));

  // Récupération de mes pokemons
  $query = $db->prepare('
    SELECT pokemon.id, numero, nom, experience, vie, defense, attaque
      FROM pokemon
      WHERE id_dresseur = :id_dresseur
  ');
  $query->bindValue(':id_dresseur', $id_dresseur, PDO::PARAM_INT);
  $query->execute();

  // Parcours du résultat de la requête
  $html_table = genereTable($query);

  if(!$html_table) {
    echo "Aucune donnée trouvée";
  }

  echo $html_table;
}


function affichePokemon($limit = 20, $offset = 0, $search = '') {
  // Connexion à la base
  if (!$db = connexion($msg))
    die("Erreur : " . implode($msg));

  // Affichage des pokemons
  if (!$query = $db->query("
  SELECT pokemon.id, numero, nom, experience, vie, defense, attaque
    FROM pokemon
    WHERE 1 = 1
    $search
    LIMIT $limit OFFSET $offset
  ")) {
    $errors[] = "Erreur lors de la création de la requête";
  }

  echo genereTable($query);

}
