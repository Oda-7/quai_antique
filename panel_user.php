<?php
$namePage = "Panel";

include './templates/header.php';
include './sys/db.php';

$reqSelectUser = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$reqSelectUser->execute([$_SESSION['auth']->user_id]);
$selectUser = $reqSelectUser->fetch();

$reqSelectProfil = $pdo->prepare('SELECT * FROM profil WHERE profil_id = ?');
$reqSelectProfil->execute([$_SESSION['auth']->user_id]);
$selectProfil = $reqSelectProfil->fetch();


if (isset($_POST['validate_info'])) {
   // si la variable du boutton existe
   if (empty($_POST['profil_firstname']) || empty($_POST['profil_lastname']) || empty($_POST['profil_age'])) {
      $errors['empty_input'] = "Les champs du formulaire ne peuvent pas être vide";
   } else {
      $reqUpdateProfil = $pdo->prepare('UPDATE profil SET profil_firstname = ?, profil_lastname = ?, profil_age = ?, profil_civility = ? WHERE profil_id = ?');
      $reqUpdateProfil->execute([$_POST['profil_firstname'], $_POST['profil_lastname'], $_POST['profil_age'], $_POST['profil_civility'], $selectProfil->profil_id]);
   }

   if (empty($_POST['profil_email']) || !filter_var($_POST['profil_email'], FILTER_VALIDATE_EMAIL)) {
      // verification sur l'email
      $errors['profil_email'] = "Votre adresse email n'est pas valide donc elle n'à pas était modifiée";
   } else {
      $req = $pdo->prepare('SELECT user_id FROM users WHERE user_email = ?');
      $req->execute([$_POST['profil_email']]);
      $user = $req->fetch();
      if ($user) {
         $errors['error_profil_email'] = "Cette adresse email est déja utilisé donc elle n'à pas était modifiée";
      } else {
         // requete pour update les email
         $reqUpdateUser = $pdo->prepare('UPDATE users SET user_email = ? WHERE user_id = ?');
         $reqUpdateUser->execute([$_POST['profil_email'], $selectProfil->profil_id]);
      }
   }

   if (!password_verify($_POST['old_password'], $selectUser->user_password)) {
      // si l'ancien mot de passe n'est pas identique a celui rentré dans le champs
      $errors['old_password'] = "L'ancien mot de passe rentré n'est pas identique a l'ancien, il n'y a pas eu de modification de mot de passe !";
   }

   if (!preg_match('/^[a-zA-Z]{6,}[0-9]{1,}/', $_POST['new_password'])) {
      // si le nouveau mot de passe répond bien au exigence
      $errors['new_password'] = "Le nouveau mot de passe ne comporte pas au minimum 1 caractères numérique, le mot de passe n'a pas était enregistré";
   }

   if (!isset($errors['new_password']) || !isset($errors['old_password'])) {
      // si les valeur n'existe pas on update le mot de passe
      $reqUpdateUser = $pdo->prepare('UPDATE users SET  user_password = ? WHERE user_id = ?');
      $newPassWord = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
      $reqUpdateUser->execute([$newPassWord, $selectProfil->profil_id]);
   }
   header('location: panel_user.php');
}

if (isset($_SESSION['flash'])) :
   foreach ($_SESSION['flash'] as $type => $message) : ?>
      <div class="alert alert-<?= $type; ?>">
         <?= $message; ?>
      </div>
<?php endforeach;
   unset($_SESSION['flash']);
endif; ?>

<?php if (!empty($errors)) : ?>
   <div class="alert alert-danger">
      <p>Les champs du formulaire sont pas rempli correctement</p>
      <ul>
         <?php foreach ($errors as $error) : ?>
            <li><?= $error; ?></li>
         <?php endforeach; ?>
      </ul>
   </div>
<?php endif; ?>


<div>
   <div id="profil_user" class=" container d-flex flex-column align-items-center  py-5 mt-5">

      <div id="info_user" class=" d-flex flex-column align-items-center border border-dark border-2 rounded-3 my-3 py-4  px-3" style="background-color: #333533 ;color: #e8eddf;">
         <h3>Bonjour <?= $selectProfil->profil_civility . ' ' . $selectProfil->profil_firstname ?></h3>
         <h4>Vos informations</h4>
         <div class="d-flex gap-2">
            <label>Civilité :</label>
            <p><?= $selectProfil->profil_civility ?></p>
         </div>
         <div class="d-flex gap-2">
            <label>Prénom :</label>
            <p><?= $selectProfil->profil_firstname ?></p>
         </div>
         <div class="d-flex gap-2">
            <label>Nom :</label>
            <p><?= $selectProfil->profil_lastname ?></p>
         </div>
         <div class="d-flex gap-2">
            <label>Âge :</label>
            <p><?= $selectProfil->profil_age ?></p>
         </div>
         <div class="d-flex gap-2">
            <h6>Email :</h6>
            <p><?= $selectProfil->profil_email ?></p>
         </div>
         <button class="btn" style="background-color: #cfdbd5;" id="button_modify_user_info">Modifier vos information</button>
         <br>

         <?php if (!$selectProfil->profil_default_reservation) : ?>
            <button id="add_number_reservation_default">Ajouter un nombre de réservation par default</button>
         <?php else : ?>
            <label>Nombre de réservation par defaut que vous avez configuré pour réserver</label>
            <p id="number_p_reservation"><?= $selectProfil->profil_default_reservation ?></p>
            <button class="btn" style="background-color: #cfdbd5; " id="modify_number_reservation_default">Modifier le nombre de réservation default</button>
         <?php endif; ?>

         <div id="reservation_default" class="d-none pt-4 gap-2">
            <input id="input_number_reservation" class="form-control text-center" type="number" max="25">
            <button class="btn" style="background-color: #cfdbd5; " id="button_validate_input_reservation">Valider</button>
            <button class="btn" style="background-color: #cfdbd5; " id="button_close_input_reservation">Annuler</button>
         </div>
      </div>

      <div id="modify_user" class="d-none border border-2 border-dark rounded-3 p-3" style="background-color: #333533 ;color: #e8eddf;">
         <button id="button_close_modify_user" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
         <form method="post" class="d-flex flex-wrap flex-column justify-content-center align-items-center">
            <h6>Civilité :</h6>
            <select class="form-select text-center" name="profil_civility" id="civility">
               <option value="<?= ucfirst($selectProfil->profil_civility) ?>" selected><?= ucfirst($selectProfil->profil_civility) ?></option>
               <option value="Mr">Mr</option>
               <option value="Mme">Mme</option>
            </select>
            <label>Prénom :</label>
            <input type="text" class="form-control text-center" name="profil_firstname" id="profil_firstname" value="<?= $selectProfil->profil_firstname ?>">
            <label>Nom :</label>
            <input type="text" class="form-control text-center" name="profil_lastname" id="profil_lastname" value="<?= $selectProfil->profil_lastname ?>">
            <label>Âge :</label>
            <input type="text" class="form-control text-center" name="profil_age" id="profil_age" value="<?= $selectProfil->profil_age ?>">
            <label>Email :</label>
            <input type="text" class="form-control text-center" name="profil_email" id="profil_email" value="<?= $selectProfil->profil_email ?>">
            <label for="">Ancien mot de passe :</label>
            <input type="password" class="form-control text-center" id="old_password" name="old_password">
            <label>Nouveau mot de passe (Le mot de passe doit contenir<br>6 lettres et 1 chiffres)</label>
            <input type="password" class="form-control text-center" id="new_password" name="new_password">
            <input type="submit" class="btn mt-3" style="background-color: #cfdbd5; " name="validate_info" value="Valider">
         </form>
      </div>
   </div>
</div>

<script>
   const profilUser = document.getElementById('profil_user')
   const infoUser = document.getElementById('info_user')
   const modifyUser = document.getElementById('modify_user')
   const buttonModifyInfo = document.getElementById('button_modify_user_info')
   const buttonClose = document.getElementById('button_close_modify_user')

   buttonModifyInfo.addEventListener('click', (event) => {
      infoUser.classList.replace("d-flex", "d-none")
      modifyUser.classList.replace("d-none", "d-block")
   })

   buttonClose.addEventListener('click', (event) => {
      infoUser.classList.replace('d-none', 'd-flex')
      modifyUser.classList.replace("d-block", "d-none")
   })



   const buttonAddNumberReservation = document.getElementById('add_number_reservation_default')
   const buttonModifyNumberReservation = document.getElementById('modify_number_reservation_default')
   const buttonInputNumberReservation = document.getElementById('button_validate_input_reservation')
   const buttonCloseInputDiv = document.getElementById('button_close_input_reservation')
   const divReservation = document.getElementById('reservation_default')
   const inputReservation = document.getElementById('input_number_reservation')
   // const pReservation = document.getElementById('number_p_reservation')


   if (buttonAddNumberReservation) {
      buttonAddNumberReservation.addEventListener('click', (event) => {
         divReservation.classList.replace('d-none', 'd-flex')
      })
   }

   if (buttonModifyNumberReservation) {
      buttonModifyNumberReservation.addEventListener('click', (event) => {
         divReservation.classList.replace('d-none', 'd-flex')
         numberBd = document.getElementById('number_p_reservation')
         console.log(numberBd)
         inputReservation.value = numberBd.textContent
      })
   }

   if (buttonCloseInputDiv) {
      buttonCloseInputDiv.addEventListener('click', (event) => {
         divReservation.classList.replace('d-flex', 'd-none')
      })
   }

   if (buttonInputNumberReservation) {
      buttonInputNumberReservation.addEventListener('click', (event) => {
         if (inputReservation.value > 25) {
            alert('Vous ne pouvez pas selectionner au dessus de 25 Réservation')
         } else {
            idProfil = <?= $selectProfil->profil_id ?>;
            InsertNumberReservation(inputReservation.value, idProfil)
         }
      })
   }


   function ModifyNumberReservation(number, id) {
      xhrUserNumber = new XMLHttpRequest()
      if (number != "") {
         xhrUserNumber.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
               let test = xhrUserNumber.responseText
               document.getElementById('number_p_reservation').textContent = test
            }
         }
         xhrUserNumber.open("POST", "./sys/user/user_modify_post.php", true)
         xhrUserNumber.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
         xhrUserNumber.send("number_reservation_default=" + number + "&id_profil=" + id)
      }
   }

   function InsertNumberReservation(number, id) {
      xhrUserNumber = new XMLHttpRequest()
      if (number != "") {
         xhrUserNumber.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
               let test = xhrUserNumber.responseText
               document.getElementById('number_p_reservation').textContent = test
            }
         }
         xhrUserNumber.open("POST", "./sys/user/user_post.php", true)
         xhrUserNumber.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
         xhrUserNumber.send("number_reservation_default=" + number + "&id_profil=" + id)
      }
   }
</script>

<?php include './templates/footer.php'; ?>