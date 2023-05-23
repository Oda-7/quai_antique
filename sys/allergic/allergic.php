<form method="post">
   <div class="border border-3 border-dark rounded-3" style="background-color: white ;">
      <div class="d-flex flex-row  p-2 gap-3" style="background-color: #242423 ;color:white;">
         <p class="pt-2">Nom de l'allergènes</p>
         <p class="pt-2">Aliments</p>
      </div>

      <div class="p-3 border border-1 d-flex flex-row flex-wrap gap-2" tabindex="0" style="overflow-y: scroll; height: 500px;">

         <?php if (!empty($_POST)) {
            requestAllergic();
         } else {
            requestAllergic();
         }
         ?>

      </div>

      <div class="m-2">
         <input class="btn" style="background-color: #242423 ;color:white;" type="submit" name="submit_delete" value="Supprimer">
         <input class="btn" style="background-color: #242423 ;color:white;" type="submit" name="submit_add" value="Ajouter">
      </div>
   </div>
</form>


<div class="d-flex flex-column flex-wrap p-2 justify-content-center align-items-center">

   <?php
   if (isset($_POST['submit_add'])) {
      if (isset($_GET)) {
         unset($_GET);
      }
      echo '
      <form action="" method="post" class="d-flex flex-column flex-wrap p-2 align-items-center gap-2">
      <h3>Ajout d\'allergène</h3>
         <input class="form-control"type="text" name="allergic_name" placeholder="Nom de l\'allergène">
         <textarea class="form-control"name="allergic_food" id="" rows="5" placeholder="Aliment(s)"></textarea>
         <input class="btn" style="background-color: #242423 ;color:white;" name="submit_add_validate" type="submit" value="Ajouter">
      </form>';
   }

   if (isset($_GET['id'])) {
      $reqUpdateRead = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
      $reqUpdateRead->execute([$_GET['id']]);
      $allergicUpdate = $reqUpdateRead->fetch();
      echo '
      <h3>Modification d\'allergenes</h3>
      <form action="" method="post" class="d-flex flex-column flex-wrap p-2 align-items-center gap-2" >
         <input class="form-control" type="text" name="allergic_name" value="' . $allergicUpdate->allergic_name . '">
         <textarea class="form-control"name="allergic_food" id="" rows="5" >' . $allergicUpdate->allergic_food . '</textarea>
         <input class="btn" style="background-color: #242423 ;color:white;" name="submit_update" type="submit" value="Modifier">
         <input class="btn" style="background-color: #242423 ;color:white;" name="cancel_allergic" type="submit" type="button" value="Annuler">
      </form>';
   }
   ?>

</div>