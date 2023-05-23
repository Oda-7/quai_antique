<?php
$namePage = "Carte";
include './templates/header.php';
include './sys/dishes/db_dishes.php';
include './sys/food/db_categorie.php';
include './sys/food/db_sub_categorie.php';
$reqSelectDishesBySubcategorie = $pdo->prepare('SELECT * FROM dishes WHERE sub_categorie_id = ?');
?>
<div class="container mt-4 ">
   <div class="d-flex justify-content-center mt-5">
      <h1 class="pb-5">Voici nos Cartes</h1>
   </div>
   <div class="pb-4 row gap-5 justify-content-center mx-2">
      <?php
      $countDishes = 0;

      if ($dishesList) {
         foreach ($categorieList as $categorie => $value) {
            foreach ($listSubCategorie as $s => $subCategorie) {
               if ($value->categorie_id == $subCategorie->categorie_id) {

                  $reqSelectDishesBySubcategorie->execute([$subCategorie->sub_categorie_id]);
                  $selectDishesBySubcategorie = $reqSelectDishesBySubcategorie->fetch();

                  // echo '<br>';

                  if ($selectDishesBySubcategorie) {

                     $numberColMd = 4;
                     if ($countDishes % 2 == 0) {
                        $numberColMd = 3;
                        // echo $countDishes;
                     }
                     echo '<div class="col-md-' . $numberColMd . ' border border-3 border-dark py-3" style="background-color: #333533 ;color: #e8eddf;border-radius:0% 5%;">';
                     echo '<h5 class="d-flex justify-content-center pb-2 mb-2 border-bottom">' . $subCategorie->sub_categorie_name . '</h5>';
                  }
                  foreach ($dishesList as $dishes) {
                     if ($subCategorie->sub_categorie_id == $dishes->sub_categorie_id && !$dishes->dishes_temp) {
                        echo '<p>' . $dishes->dishes_name . '<br> - ' . $dishes->dishes_description . ' (' . $dishes->dishes_food . ')</p>';

                        // aliment du plat
                        $ReqselectFoodDishes = $pdo->prepare('SELECT * FROM have_food WHERE dishes_id =?');
                        $ReqselectFoodDishes->execute([$dishes->dishes_id]);
                        $selectFoodDishes = $ReqselectFoodDishes->fetchAll();
                        // id de l'allergies, id de l'aliment
                        $reqSelectAllergicFood = $pdo->prepare('SELECT * FROM food_allergic WHERE food_id = ?');

                        $ListAllergic = array();
                        foreach ($selectFoodDishes as $foodDishes) {
                           $reqSelectAllergicFood->execute([$foodDishes->food_id]);
                           $selectAllergicFood = $reqSelectAllergicFood->fetch();


                           if ($selectAllergicFood) {
                              // allergic de l'aliment s'il en a une
                              $reqSelectAllergic = $pdo->prepare('SELECT * FROM allergic WHERE allergic_id = ?');
                              $reqSelectAllergic->execute([$selectAllergicFood->allergic_id]);
                              $selectAllergic = $reqSelectAllergic->fetch();

                              if (!in_array($selectAllergic, $ListAllergic)) {
                                 array_push($ListAllergic, $selectAllergic);
                              }
                           }
                        }

                        if (!empty($ListAllergic)) {
                           echo '<small style="">Allergènes :</small>
               <a role="button" data-bs-html="true" class="btn " id="allergic" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="';
                           foreach ($ListAllergic as $a => $allergic) {
                              echo ' - ' . $allergic->allergic_food . '<br>';
                           }
                           echo '"><img src="./svg/circle-info.svg"></a>';
                        }
                     }
                  }
                  if ($selectDishesBySubcategorie) {
                     echo '</div>';
                     $countDishes++;
                  }
               }
            }
         }
      } else {
         echo 'Aucun plat';
      }
      ?>
   </div>
</div>

<?php include './templates/footer.php'; ?>