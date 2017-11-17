<?php
session_start(); // démarrer la gestion de session PHP

// Fonctions de bases
require_once('../resources/function.php');

$errors = [];
$form_errors = [];

// Connexion à la base
if (!$db = connexion($errors)) {
  header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
  die("Impossible de se connecter à la base de donnée : " . implode(', ', $errors));
}

// Vérification du token
$token = $_GET['token'] ?? '';
if (!empty($token)) {
  // Vérification du token en base
  $query = $db->prepare("SELECT id_dresseur, last_connection FROM auth_dresseur WHERE token = :token");
  $query->bindValue(':token', $token, PDO::PARAM_STR);
  $query->execute();
  $dresseur = $query->fetch();
  // Si le token n'est pas en base
  if (!$dresseur) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden !!', true, 403);
    die("Adresse invalide");
  }

  // Calcul de la validité de la date d'insertion (plus de 24h alors erreur)
  $diff_date = abs(strtotime($dresseur['last_connection']) - time()) / (60 * 60 * 24);
  // Si la date d'insertion est trop ancienne
  if ($diff_date >= 1.0) {
    // Suppression des lignes de auth_dresseur concernant ce dresseur
    $query = $db->prepare("DELETE FROM auth_dresseur WHERE id_dresseur = :id_dresseur");
    $query->bindValue(':id_dresseur', $dresseur['id_dresseur'], PDO::PARAM_INT);
    $query->execute();
    // Puis on sort en erreur
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden !!', true, 403);
    die("Adresse invalide");
  }

  // Si le nouveau mot de passe est fourni
  if (formIsSubmit('modif_form')) {
    // Vérifications des emails fournis
    // Récupération des valeurs du formulaire
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $hashPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Vérification des saisies
    if (empty($password)) {
      $form_errors['password'] = 'Mot de passe non renseigné !';
    }
    if (empty($confirmPassword)) {
      $form_errors['confirmPassword'] = 'Mot de passe de confirmation non renseigné !';
    }
    if ($password != $confirmPassword) {
      $form_errors['password'] = 'Mot de passe différent !';
    }

    // Si les champs sont valides
    if (count($form_errors) == 0) {
      // Mise à jour du mot de passe
      $query = $db->prepare("UPDATE dresseur SET password = :password WHERE id = :id_dresseur");
      $query->bindValue(':id_dresseur', $dresseur['id_dresseur'], PDO::PARAM_INT);
      $query->bindValue(':password', $hashPassword, PDO::PARAM_STR);
      $query->execute();

      // Suppression des lignes de auth_dresseur concernant ce dresseur
      $query = $db->prepare("DELETE FROM auth_dresseur WHERE id_dresseur = :id_dresseur");
      $query->bindValue(':id_dresseur', $dresseur['id_dresseur'], PDO::PARAM_INT);
      $query->execute();

      // Connexion de l'utilisateur
      $_SESSION["id"] = $dresseur['id_dresseur'];
      $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
      header("location: liste.php");
      return;
    }

  }

}

// Soummission du formulaire d'oubli du mot de passe
if (formIsSubmit('forgot_form')) {
  // Vérification de l'email fourni
  $email = $_POST['email'] ?? '';  // Si 'email' n'est pas une clé de $_POST alors on renvoie la chaîne vide : ''

  if ($email == '') {
    $form_errors['email'] = "Veuillez saisir un email";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_errors['email'] = 'Adresse email invalide !';
  }

  // S'il n'y a pas d'erreurs de formulaire
  if (count($form_errors) == 0) {
    // Vérification de l'email et envoi du mail
    $query = $db->prepare("SELECT id FROM dresseur WHERE email = :email");
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $dresseur = $query->fetch();

    if (!$dresseur) {
      $form_errors['email'] = "Cet email n'existe pas en base !";
    } else {
      // Le dresseur existe bien en base
      // Génération d'un token et envoi du mail
      $token = md5(uniqid(rand(), true));

      // Inscription du token en base
      $query = $db->prepare("
        INSERT INTO auth_dresseur (id_dresseur, token)
          VALUES                  (:id_dresseur, :token)
      ");
      $query->bindValue(':id_dresseur', $dresseur['id'], PDO::PARAM_INT);
      $query->bindValue(':token', $token, PDO::PARAM_STR);
      $query->execute();


      // Préparation du mail
      $url = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[SCRIPT_NAME]";
      $_GET['token'] = $token;
      $url .= "?" . http_build_query($_GET);
      $text = "
Bonjour,

Pour réinitialiser votre mot de passe utilisez cette adresse :
$url

Si vous n'êtes pas à l'origine de cette demande, merci d'ignorer ce message.
";
      $headers = "content-type: text/html; charset=\"UTF-8\"" . "\r\n";

      // Envoi du mail
      if (!mail($email, 'Modifier votre mot de passe', $text, $headers)) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die ("Impossible d'envoyer l'email");
      }
    }
  }
}

// Entête HTML ce require permet de charger toutes les balises d'en-tête de la page HTML
require_once('header.php');
?>

<div class="container">
  <?php if (empty($token)) : ?>
  <span>Veuillez saisir votre adresse email</span>
  <form method="post">
    <input type="hidden" name="forgot_form" value="1"/>
    <label for="email" class="sr-only">Adresse Email</label>
    <input type="email" id="email" name="email" class="form-control <?php echo isset($form_errors['email']) ? 'is-invalid' : '' ?>" placeholder="Adresse email" required autofocus>
    <?php echo isset($form_errors['email']) ? '<div class="invalid-feedback">' . $form_errors['email'] . '</div>' : '' ?>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Valider</button>
  </form>

  <?php elseif (isset($url)) : ?>
  <span>Vous allez recevoir un email avec un lien pour pouvoir modifier votre mot de passe</span>

  <?php elseif (!empty($token)) : ?>
  <form method="post">
    <span>Veuillez saisir votre nouveau mot de passe</span>
    <input type="hidden" name="modif_form" value="1"/>
    <label for="password" class="sr-only">Mot de passe</label>
    <input type="password" id="password" name="password" class="form-control  <?php echo isset($form_errors['password']) ? 'is-invalid' : '' ?>" placeholder="Mot de passe" required>
    <?php echo isset($form_errors['password']) ? '<div class="invalid-feedback">' . $form_errors['password'] . '</div>' : '' ?>
    <label for="confirmPassword" class="sr-only">Confirmez</label>
    <input type="password" id="confirmPassword" name="confirmPassword" class="form-control <?php echo isset($form_errors['confirmPassword']) ? 'is-invalid' : '' ?>" placeholder="Confirmez le mot de passe" required>
    <?php echo isset($form_errors['confirmPassword']) ? '<div class="invalid-feedback">' . $form_errors['confirmPassword'] . '</div>' : '' ?>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Valider</button>
  </form>
  <?php endif; ?>
</div>
