<?php
require './sys/db.php';
include './sys/function.php';

if (session_status() == PHP_SESSION_NONE) {
   session_start();
}

reconnect_from_cookie();
?>

<!DOCTYPE html>

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <title><?= $namePage ?></title>
   <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.css">
   <link rel="stylesheet" href="./style/font.css">
   <link rel="stylesheet" href="./style/panel.css">
</head>

<body class="d-flex flex-column" style="background-color: #f5cb5c; ">
   <header class="d-flex" style="background-color: #242423; ">
      <nav class="navbar navbar-expand-lg navbar container" style="z-index: 3; background-color: #242423;">
         <div class="container-fluid" style="z-index: 3;">
            <a id="button_header" type="button" class="navbar-brand" style="font-family: Charlotte; color: #e8eddf;" href="/">Quai Antique</a>
            <button id="button_span" style="background-color: #cfdbd5;" class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
               <span class="navbar-toggler-icon "></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between" style="z-index: 2;" id="navbarSupportedContent">
               <div class="navbar-nav" style="z-index: 2;">
                  <a id="button_header" href="./menu_show.php" class="nav-link" style="color: #e8eddf;">Menu</a>
                  <a id="button_header" href="./card_show.php" class="nav-link" style="color: #e8eddf;">Carte</a>
                  <?php
                  if (isset($_SESSION['auth']->user_email)) : ?>
                     <a id="button_header" href="./panel_user.php" class="nav-link" style="color: #e8eddf;">Panel</a>
                  <?php endif;
                  $reqSelectRole = $pdo->prepare('SELECT * FROM role WHERE role_id = ?');
                  $reqSelectRole->execute([$_SESSION['auth']->user_role_id]);
                  $selectRole = $reqSelectRole->fetch();


                  if ($selectRole->role_name == 'admin') : ?>
                     <a id="button_header" href="./panel.php" class="nav-link" style="color: #e8eddf;">Gestion</a>
                  <?php endif; ?>
               </div>

               <div class="d-flex navbar-nav">
                  <?php
                  if (isset($_SESSION['auth'])) : ?>
                     <button id="button_show_calendar" class="btn " data-bs-target="#modal_calendar" data-bs-toggle="modal" style="background-color: #333533 ;color: #e8eddf;">
                        Réservation
                     </button>
                     <a id="button_header" class="nav-link" href="/logout.php" style="color: #e8eddf;">Se déconnecter</a>
                  <?php else : ?>
                     <a id="button_header" class="nav-link" href="/register.php" style="color: #e8eddf;">S'inscrire</a>
                     <a id="button_header" class="nav-link" href="/login.php" style="color: #e8eddf;">Se connecter</a>
                  <?php endif; ?>
               </div>
            </div>
         </div>
      </nav>
      <section class="z-n1 position-absolute top-0 w-100 h-25 overflow-hidden">
         <img class="w-100 h-75" style="object-fit: cover;" src="./images/savoyarde.jpg" alt="savoyarde">
      </section>
   </header>


   <div class="mt-4 d-flex flex-column align-items-center justify-content-center">
      <div id="div_calendar" class="d-none ">
         <?php include './sys/planning/calendar.php'; ?>
      </div>
      <?php
      // var_dump($_SERVER);
      // var_dump($_SESSION['auth']->user_email);

      ?>
      <script>
         // button_show_calendar
         const buttonShowCalendar = document.getElementById('button_show_calendar')
         const divCalendar = document.getElementById('div_calendar')

         const cookieDivCalendar = sessionStorage.getItem('divCalendar')
         const listButton = document.querySelectorAll("#button_header")

         listButton.forEach((element) => {
            element.addEventListener('click', (event) => {
               sessionStorage.removeItem('divCalendar', 'd-block')
               divCalendar.className = "d-none"
            })
         })

         if (cookieDivCalendar == "d-block") {
            divCalendar.className = "d-block"
         }

         if (buttonShowCalendar) {
            buttonShowCalendar.addEventListener('click', (event) => {
               divCalendar.className = "d-block"
               sessionStorage.setItem('divCalendar', 'd-block')
            })
         }
      </script>