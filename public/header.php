<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pokemon APP</title>

    <link href="components/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="components/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet" type="text/css">

    <script src="components/jquery/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="components/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
  <?php if (isset($_SESSION['id'])) : ?>
  <div class="text-right">
    <form action="index.php" method="post">
      <input type="hidden" name="form_deconnexion" value="1"/>
      <button class="btn btn-secondary" type="submit">Deconnexion</button>
    </form>
  </div>
  <?php endif; ?>
  <div class="text-center">
    <img src="img/pokemon.png" alt="" style="width: 30%;">
  </div>
