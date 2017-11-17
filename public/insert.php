<?php
session_start();   // récupération de la session

$id_dresseur = $_SESSION['id'] ?? null;

// Entête HTML ce require permet de charger toutes les balises d'en-tête de la page HTML
require_once('header.php');

// Fonctions de bases
require_once('../resources/function.php');

$errors = [];

$form_errors = [];

if (!$db = connexion($errors))
  die("Erreur(s) lors de la connexion : " . implode($errors));

// Validation du formulaire d'insertion
if (formIsSubmit('insertPokemon')) {
  // Récupération des variables
  $numero = $_POST['numero'];
  $nom = $_POST['nom'];
  $experience = $_POST['experience'];
  $vie = $_POST['vie'];
  $defense = $_POST['defense'];
  $attaque = $_POST['attaque'];
  $pokedex = $_POST['pokedex'];

  // Fichier image
  if (isset($_FILES['image'])) { // Si une image a été fournie par l'utilisateur
    var_dump($_FILES);
    //rename($_FILES['image']['tmp_name'], $_FILES['image']['tmp_name'].'.save');
    // nom temporaire de l'image
    $tmp_name = $_FILES['image']['tmp_name'];

    // Récupération de l'extension du fichier en fonction de son type : http://php.net/manual/en/function.pathinfo.php
    $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $img_name = $_POST['nom'] . "." . $extension;

    var_dump($extension);
  }
  // Validation
  if (!filter_var($numero, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
    $form_errors['numero'] = "Le numéro doit être un nombre strictement supérieur à 0";
  }

  if (empty($nom)) {
    $form_errors['nom'] = "Le nom doit être renseigné";
  } elseif (strlen($nom) > 50) {
    $form_errors['nom'] = "Le nom doit faire 50 caractères maximum";
  }

  if (!filter_var($experience, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0)))) {
    $form_errors['experience'] = "L'expérience doit être un nombre supérieur ou égal à 0";
  }

  if (!filter_var($vie, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
    $form_errors['vie'] = "La vie doit être un nombre strictement supérieur à 0";
  }

  if (!filter_var($defense, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
    $form_errors['defense'] = "La défense doit être un nombre strictement supérieur à 0";
  }

  if (!filter_var($attaque, FILTER_VALIDATE_INT, array("options" => array("min_range" => 1)))) {
    $form_errors['attaque'] = "L'attaque doit être un nombre strictement supérieur à 0";
  }

  if (!filter_var($pokedex, FILTER_VALIDATE_INT, array("options" => array("min_range" => 0))) && $pokedex != 0) {
    $form_errors['pokedex'] = "La valeur du pokedex n'est pas valide";
  } elseif (empty($pokedex))
    $pokedex = null;

  // S'il n'y a pas eu d'erreur ET que la connexion existe
  if (count($form_errors) == 0 && isset($db)) {
    $query = $db->prepare("
      INSERT INTO pokemon(numero,  nom,  experience,  vie,  defense,  attaque,  id_pokedex)
        VALUES           (:numero, :nom, :experience, :vie, :defense, :attaque, :id_pokedex)
    ");
    $query->bindParam(':numero', $numero, PDO::PARAM_INT);
    $query->bindParam(':nom', $nom, PDO::PARAM_STR);
    $query->bindParam(':experience', $experience, PDO::PARAM_INT);
    $query->bindParam(':vie', $vie, PDO::PARAM_INT);
    $query->bindParam(':defense', $defense, PDO::PARAM_INT);
    $query->bindParam(':attaque', $attaque, PDO::PARAM_INT);
    $query->bindParam(':id_pokedex', $pokedex, PDO::PARAM_INT);

    // exécution de la requête préparée
    try {
      null;
      //$query->execute();

      // Commande exécutée avec succès : redirection vers l'acceuil
      //header('Location: ./');
    } catch(PDOException $e) {
      // Il y a eu une erreur
      var_dump($e);
    }
  }
}

// Liste des dresseurs
$dresseur_options = "";

// Selectionne tous les dresseurs
$str_query = "SELECT id, nom FROM dresseur";
// Si l'id dresseur est donné on ne récupère que ce dresseur là
if (isset($id_dresseur))
  $str_query .= " WHERE id = :id_dresseur";

$query = $db->prepare($str_query);

if (isset($id_dresseur))
  $query->bindValue(":id_dresseur", $id_dresseur, PDO::PARAM_INT);

$query->execute();
$dresseurs = $query->fetchAll();

foreach($dresseurs as $dresseur) {
  $dresseur_options .= '<option value="' . $dresseur['id'] . '" ' . (isset($id_dresseur) && $dresseur['id'] == $id_dresseur ? 'selected' : '') . '>' . $dresseur['nom'] . '</option>';
}


// Image du pokemon
$image = "img/pokeball.png";
?>

<div class="container">
  <div class="row align-items-center">
    <div class="col-sm-4 d-none d-sm-block">
      <img class="img-fluid mx-auto" src="<?php echo $image; ?>" alt="" />
    </div> <!-- Col -->
    <div class="col-xs-12 col-sm-8">
      <form method="post" id="insertPokemon" enctype="multipart/form-data">
        <input type="hidden" name="insertPokemon" value="1"/>
        <div class="form-control">
          <div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="numero">Numéro</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['numero']) ? 'is-invalid' : '' ?>" id="numero" name="numero" value="<?php echo isset($_POST['numero']) ? $_POST['numero'] : '' ?>">
                <?php echo isset($form_errors['numero']) ? '<div class="invalid-feedback">' . $form_errors['numero'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="nom">Nom</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['nom']) ? 'is-invalid' : '' ?>" id="nom" name="nom" value="<?php echo isset($_POST['nom']) ? $_POST['nom'] : '' ?>">
                <?php echo isset($form_errors['nom']) ? '<div class="invalid-feedback">' . $form_errors['nom'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="experience">Expérience</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['experience']) ? 'is-invalid' : '' ?>" id="experience" name="experience" value="<?php echo isset($_POST['experience']) ? $_POST['experience'] : '' ?>">
                <?php echo isset($form_errors['experience']) ? '<div class="invalid-feedback">' . $form_errors['experience'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="vie">Vie</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['vie']) ? 'is-invalid' : '' ?>" id="vie" name="vie" value="<?php echo isset($_POST['vie']) ? $_POST['vie'] : '' ?>">
                <?php echo isset($form_errors['vie']) ? '<div class="invalid-feedback">' . $form_errors['vie'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="defense">Défense</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['defense']) ? 'is-invalid' : '' ?>" id="defense" name="defense" value="<?php echo isset($_POST['defense']) ? $_POST['defense'] : '' ?>">
                <?php echo isset($form_errors['defense']) ? '<div class="invalid-feedback">' . $form_errors['defense'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="attaque">Attaque</label>
              <div class="col-sm-9">
                <input type="text" class="form-control <?php echo isset($form_errors['attaque']) ? 'is-invalid' : '' ?>" id="attaque" name="attaque" value="<?php echo isset($_POST['attaque']) ? $_POST['attaque'] : '' ?>">
                <?php echo isset($form_errors['attaque']) ? '<div class="invalid-feedback">' . $form_errors['attaque'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="dresseur">Dresseur</label>
              <div class="col-sm-9">
                <select class="form-control <?php echo isset($form_errors['dresseur']) ? 'is-invalid' : '' ?>" id="dresseur" name="dresseur" value="<?php echo isset($_POST['dresseur']) ? $_POST['dresseur'] : '' ?>">
                  <option value="">- Aucun -</option>
                  <?php echo $dresseur_options; ?>
                </select>
                <?php echo isset($form_errors['dresseur']) ? '<div class="invalid-feedback">' . $form_errors['dresseur'] . '</div>' : '' ?>
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-3 col-form-label" for="image">Nouvelle image</label>
              <div class="col-sm-9">
              <input type="file" id="image" name="image" accept="image/*"/>
            </div>
          </div>
          <div class="text-center">
            <button type="submit" class="btn btn-primary">Valider</button>
            <button onclick="window.location.href = './'; return false;" class="btn btn-secondary">Annuler</button>
          </div>
        </div>
      </form>
    </div><!-- Col -->
  </div> <!-- Row -->

  <div class="text-center">
    <?php
      if (count($errors) > 0)
        echo "<p>" . implode("</p><p>", $errors) . "</p>";
    ?>
  </div>
</div> <!-- Container -->

<?php
// Fin du HTML
require_once('footer.php');
