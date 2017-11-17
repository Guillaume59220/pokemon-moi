<?php
session_start(); // démarrer la gestion de session PHP

// Fonctions de bases
require_once('../resources/function.php');

$errors = [];
$form_errors = [];


// Déconnexion
if (formIsSubmit('form_deconnexion')) {
  // Détruit toutes les variables de la session
  session_unset();
  // Détruit toutes les données associées à la session courante
  session_destroy();
}

// Si utilisateur connecté redirection vers liste.php
if (isset($_SESSION['id'])) {
  header("location: liste.php");
  return;
}

/*
// Envoi d'un mail :
// Dans php.ini configurer sendmail_path = "C:\xampp\mailtodisk\mailtodisk.exe"
// et sendmail_from="notification@xampp.com"
if (!mail($email, "test de mail", "Bienvenue sur XAMPP"))
  showMessage("Mail en erreur");
*/

// Connexion à la base de donnée
if (!$db = connexion($errors)) {
  die ("Erreur de connexion à la base : " . implode($errors) . "\n<br>Contactez un administrateur");
}

// Gestion des formulaires
if (formIsSubmit('signin_form')) {
  // Traitement du formulaire de connexion

  // Récupération des valeurs du formulaire
  $email = $_POST['email'];
  $password = $_POST['password'];
  $remember = intVal($_POST['remember-me'] ?? 0);

  // Vérification des saisies
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_errors['email'] = 'Adresse email invalide !';
  }
  if (empty($password)) {
    $form_errors['password'] = 'Mot de passe non renseigné !';
  }

  // S'il n'y a pas eu d'erreur dans le formulaire
  if (count($form_errors) == 0) {
    // Récupération du compte utilisateur
    $query = $db->prepare("SELECT id, email, nom, password FROM dresseur WHERE email = :email");
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $user = $query->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
      // Ne soyons pas trop précis sur l'errreur pour éviter de donner des indices aux attaquants
      $form_errors['email'] = "Email non trouvé ou mot de passe invalide";
    } else {
      // Ici l'email et le mot de passe sont validés
      $_SESSION["id"] = $user['id'];
      $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
      // Mise en place d'un cookie de session
      // Ce code est à faire avant l'affichage du HTML ou une redirection
      //$_SESSION['token'] = sha1(time() . rand() . $_SERVER['SERVER_NAME']);
      //setcookie('token', $_SESSION['token']);
      // In practice, you'd want to store this token in a database with the username so it's persistent.
      header("location: liste.php");
      return;
    }
  }

}

if (formIsSubmit('signup_form')) {
  // Traitement du formulaire d'inscription

  // Récupération des valeurs du formulaire
  $email = $_POST['email'];
  $password = $_POST['password'];
  $confirmPassword = $_POST['confirmPassword'];
  $hashPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $nom = $_POST['nom'];

  // Vérification des saisies
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $form_errors['email'] = 'Adresse email invalide !';
  }
  if (empty($password)) {
    $form_errors['password'] = 'Mot de passe non renseigné !';
  }
  if (empty($confirmPassword)) {
    $form_errors['confirmPassword'] = 'Mot de passe de confirmation non renseigné !';
  }
  if (empty($nom)) {
    $form_errors['nom'] = 'Nom non renseigné !';
  }

  // S'il n'y a pas eu d'erreur dans le formulaire
  if (count($form_errors) == 0) {
    // Vérification de l'email en base de donnée
    $query = $db->prepare("SELECT id, email, nom, password FROM dresseur WHERE email = :email");
    $query->bindValue(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $users = $query->fetchAll();
    if (count($users) > 0) {
      $form_errors['email'] = "Email déjà pris, connectez vous au lieu de vous inscrire";
    } else {
      // Ici tout est valide, l'insertion peut être faite
      $query = $db->prepare("
        INSERT INTO dresseur(email,  nom,  password)
          VALUES            (:email, :nom, :password)
      ");
      $query->bindValue(':email', $email, PDO::PARAM_STR);
      $query->bindValue(':nom', $nom, PDO::PARAM_STR);
      $query->bindValue(':password', $hashPassword, PDO::PARAM_STR);
      if (!$query->execute())
        showMessage("Erreurs lors de l'inscription : " . implode($query->errorInfo()), 'alert-danger');
      else {
        $_SESSION["id"] = $user['id'];
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        header("location: liste.php");
        return;
      }
    }
  }
}

// Entête HTML ce require permet de charger toutes les balises d'en-tête de la page HTML
require_once('header.php');

?>

<div class="container form-signin">
  <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
    <li class="nav-item">
      <a class="nav-link active" id="signin-tab" data-toggle="tab" href="#signin" role="tab" aria-controls="signin" aria-selected="true">Se connecter</a>
    </li>
    <li class="nav-item">
      <a class="nav-link" id="signup-tab" data-toggle="tab" href="#signup" role="tab" aria-controls="signup" aria-selected="false">S'inscrire</a>
    </li>
  </ul>

  <div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="signin" role="tabpanel" aria-labelledby="signin-tab">
      <form method="post">
        <input type="hidden" name="signin_form" value="1"/>
        <label for="email" class="sr-only">Adresse Email</label>
        <input type="email" id="email" name="email" class="form-control <?php echo isset($form_errors['email']) ? 'is-invalid' : '' ?>" placeholder="Adresse email" required autofocus>
        <?php echo isset($form_errors['email']) ? '<div class="invalid-feedback">' . $form_errors['email'] . '</div>' : '' ?>
        <label for="password" class="sr-only">Mot de passe</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Mot de passe" required>
        <?php echo isset($form_errors['password']) ? '<div class="invalid-feedback">' . $form_errors['password'] . '</div>' : '' ?>
        <div class="checkbox">
          <label>
            <input type="checkbox" name="remember-me" value="1">Se souvenir
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Connexion</button>
        <a href="forgot.php">Mot de passe oublié</a>
      </form>
    </div>

    <div class="tab-pane fade" id="signup" role="tabpanel" aria-labelledby="signup-tab">
      <form method="post">
        <input type="hidden" name="signup_form" value="1"/>
        <label for="email" class="sr-only">Adresse Email</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="Adresse email" required autofocus>
        <label for="password" class="sr-only">Mot de passe</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Mot de passe" required>
        <label for="confirmPassword" class="sr-only">Confirmez</label>
        <input type="password" id="confirmPassword" name="confirmPassword" class="form-control" placeholder="Confirmez le mot de passe" required>
        <label for="nom" class="sr-only">Nom</label>
        <input type="text" id="nom" name="nom" class="form-control" placeholder="Nom" required>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Inscription</button>
      </form>
    </div>
</div> <!-- /container -->

<?php
// Fin du HTML
require_once('footer.php');
