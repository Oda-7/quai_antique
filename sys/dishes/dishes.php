<form method="post">
   <div class="d-flex flex-wrap flex-column align-items-center gap-2 p">
      <?php
      if ($dishesList) {
         foreach ($categorieList as $categorie => $value) {

            foreach ($listSubCategorie as $subCategorie) {
               if ($value->categorie_id == $subCategorie->categorie_id) {
                  echo '<div class="dropdown">
                  <button style="background-color: #242423 ;color: #e8eddf;" class="btn dropdown-toggle" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="true">
                  ' . $subCategorie->sub_categorie_name . '</button>
                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2" class="d-flex flex-wrap justify-content-center">';
                  $reqSelectDishesWhereSubCategorie = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
                  $reqSelectDishesWhereSubCategorie->execute([$subCategorie->sub_categorie_id]);
                  $selectDishesWhereSubCategorie = $reqSelectDishesWhereSubCategorie->fetch();
                  if (!$selectDishesWhereSubCategorie) {
                     echo '<li class="dropdown-item">Aucun plat</li>';
                  }
                  foreach ($dishesList as $dishes) {
                     if ($subCategorie->sub_categorie_id == $dishes->sub_categorie_id) {
                        echo '<li class="dropdown-item d-flex flex-wrap flex-column ">
                        <div><b>' . $dishes->dishes_name . '</b></div> - (' . $dishes->dishes_food . ') <p>' . $dishes->dishes_description . ' </p>
                        <div><a type="button" href="./panel.php?dishes=' . $dishes->dishes_id . '"><img src="./svg/sticky.svg" alt="Modifier"></a>
                        <input type="checkbox" name="checkbox_delete_dishes[]" value="' . $dishes->dishes_id . '">
                        </div>
                        </li>';
                     }
                  }
                  echo '</ul>
                  </div>';
               }
            }
         }
      } else {
         echo 'Aucun plat';
      }
      ?>
   </div>

   <div class="d-flex flex-wrap flex-column align-items-center py-4 gap-2">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="add_dishes" value="Ajouter un plat">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="delete_dishes" value="Supprimer">
   </div>
</form>

<?php if (isset($_POST['add_dishes'])) :
   if (isset($_GET)) {
      unset($_GET['dishes']);
   }
   $displayPostDishes = 'd-flex'; ?>

   <form method="post" id="form_add_dishes" class="<?= $displayPostDishes ?> flex-column flex-wrap align-items-center justify-content-center gap-2">
      <input class="form-control" type="text" name="dishes_name" placeholder="Nom du plat">
      <textarea class="form-control" type="text" rows="4" name="dishes_description" placeholder="Description du plat"></textarea>
      <textarea class="form-control" type="text" rows="4" name="dishes_food" placeholder="Ingrédients (séparé les aliments par des virgules)"></textarea>
      <!-- Voir pour demander a l'utilisateur s'il souhaite choisir des aliments dans la liste d'aliments -->
      <select class="form-select" name="select_categorie">
         <option value="null"> Aucune catégorie </option>
         <?php
         foreach ($listSubCategorie as $categorie) {
            echo '<option value="' . $categorie->sub_categorie_id . '">' . $categorie->sub_categorie_name . "</option>";
         }
         ?>
      </select>
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="validate_add_dishes" value="Ajouter">
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" id="close_post_dishes" value="Annuler">
   </form>

<?php endif; ?>



<?php if (isset($_GET['dishes'])) {
   $reqModifySelectDishes = $pdo->prepare('SELECT * FROM dishes WHERE dishes_id = ?');
   $reqModifySelectDishes->execute([$_GET["dishes"]]);
   $modifySelectDishes = $reqModifySelectDishes->fetch();

   echo '<form method="post" class="d-flex flex-wrap flex-column align-items-center gap-2" id="form_update_dishes">';
   echo '<input class="form-control" type="text" name="modify_dishes_name" placeholder="Nom du plat" value="' . $modifySelectDishes->dishes_name . '">
      <textarea class="form-control" name="modify_dishes_description" rows="4" placeholder="Description du plat">' . $modifySelectDishes->dishes_description . '</textarea>
      <textarea class="form-control" name="modify_dishes_food" rows="4" placeholder="Ingrédients (séparé les aliments par des virgules)">' . $modifySelectDishes->dishes_food . '</textarea>
      <select class="form-select" name="modify_sub_categorie_dishes">';
   foreach ($listSubCategorie as $categorie) {
      if ($categorie->sub_categorie_id == $modifySelectDishes->sub_categorie_id) {
         echo '<option value="' . $categorie->sub_categorie_id . '" selected>' . $categorie->sub_categorie_name . '</option>';
      } else {
         echo '<option value="' . $categorie->sub_categorie_id . '">' . $categorie->sub_categorie_name . "</option>";
      }
   }
   echo '</select>
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" value="Modifier" name="modify_dishes">';
   echo '<form>';
}
?>

<script>
   const addCategorie = document.getElementById('add_categorie')
   const formUpdateDishes = document.getElementById('form_update_dishes')

   addCategorie.addEventListener('click', (event) => {
      document.location.href('./panel.php')
   })
</script>