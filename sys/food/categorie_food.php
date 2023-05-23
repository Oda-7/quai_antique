<?php
include './sys/food/db_categorie.php';
include './sys/food/db_sub_categorie.php';
?>
<div class="d-flex flex-column flex-wrap gap-2">
   <form class="d-flex flex-column flex-wrap gap-2 align-items-start" method="post" id="fist-form">
      <?php
      // usort($categorieList, "sortDessert");
      if (empty($categorieList)) {
         echo 'Aucune Catégorie';
      } else {
         foreach ($categorieList as $categorie) {
            echo '<div class="dropdown">
               <button style="background-color: #242423 ;color: #e8eddf;" class="btn dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
               ' . $categorie->categorie_name . '
               </button>
               <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">';
            foreach ($listSubCategorie as $subCategorie) {
               if ($categorie->categorie_id == $subCategorie->categorie_id) {
                  echo '
                        <li class="dropdown-item">' . $subCategorie->sub_categorie_name . '
                        <a type="button" href="./panel.php?categorie=' . $subCategorie->sub_categorie_id . '"><img  src="./svg/sticky.svg" alt="Modifier"></a>
                        <input type="checkbox" name="checkbox_categorie_delete[]" value="' . $subCategorie->sub_categorie_id . '">
                        </li>';
               }
            }
            echo '</ul>
            </div>';
         }
      }
      ?>
      <div class="d-flex flex-column flex-wrap gap-2 align-items-center py-4">
         <input class="btn" style="background-color: #242423 ;color: #e8eddf;" id="add_categorie" name="add_categorie" type="submit" value="Ajouter une categorie">
         <input class="btn" style="background-color: #242423 ;color: #e8eddf;" name="delete_categorie" type="submit" value="Supprimer">
      </div>
   </form>
</div>


<?php if (isset($_POST['add_categorie'])) :
   if (isset($_GET)) {
      unset($_GET['categorie']);
   }
?>
   <form method="post" class="d-flex flex-column flex-wrap gap-2 align-items-center">
      <input class="form-control" type="text" name="categorie_name" placeholder="Catégorie à ajouter">
      <select class="form-select" name="categorie_select">
         <?php
         foreach ($categorieList as $categorie) {
            echo '<option value="' . $categorie->categorie_id . '">' . $categorie->categorie_name . '</option>';
         }
         ?>
      </select>

      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" type="submit" name="categorie_add_validate" value="Ajouter">
   </form>';
<?php endif; ?>


<?php
// modify Categorie
if (isset($_GET['categorie'])) {
   $reqModifyCategorieRead = $pdo->prepare('SELECT * FROM sub_categorie WHERE sub_categorie_id = ?');
   $reqModifyCategorieRead->execute([$_GET['categorie']]);
   $modifyCategorieRead = $reqModifyCategorieRead->fetch();

   echo '<form id="form_update_categorie" method="post" class="d-flex flex-column flex-wrap gap-2">
      <input class="form-control" type="text" name="categorie_name" value="' . $modifyCategorieRead->sub_categorie_name . '">
      <select class="form-select" name="modify_categorie_id">';
   foreach ($categorieList as $categorie) {
      if ($categorie->categorie_id == $modifyCategorieRead->categorie_id) {
         echo '<option value="' . $modifyCategorieRead->categorie_id . '" selected>' . $categorie->categorie_name . '</option>';
      }
      echo '<option value="' . $categorie->categorie_id . '">' . $categorie->categorie_name . '</option>';
   }
   echo '</select>
      <input class="btn" style="background-color: #242423 ;color: #e8eddf;" name="modify_categorie" type="submit" value="Modifier">
   </form>';
}
?>