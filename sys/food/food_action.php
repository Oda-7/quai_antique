<?php

if (isset($_POST["validate_add_food"])) {
   if (!empty($_POST['add_food_name'])) {
      $reqVerifyFood = $pdo->prepare('SELECT * FROM food WHERE food_name = ?');
      $reqVerifyFood->execute([ucfirst($_POST['add_food_name'])]);
      $verifyFood = $reqVerifyFood->fetch();

      if (!$verifyFood) {
         if (!empty($_POST["add_food_origin"]) && $_POST["add_food_breeding"] != 'null') {
            $reqAddFood = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ?, food_breeding = ?');
            $reqAddFood->execute([ucfirst($_POST['add_food_name']), ucfirst($_POST["add_food_origin"]), $_POST["add_food_breeding"]]);
         } elseif (!empty($_POST["add_food_origin"])) {
            $reqAddFood = $pdo->prepare('INSERT INTO food SET food_name = ?, food_origin = ? ');
            $reqAddFood->execute([ucfirst($_POST['add_food_name']), ucfirst($_POST["add_food_origin"])]);
         } else {
            $reqAddFood = $pdo->prepare('INSERT INTO food SET food_name = ? ');
            $reqAddFood->execute([ucfirst($_POST['add_food_name'])]);
         }
         // header('location: panel.php');
         $urlLogin = "panel.php";
         echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
      } else {
         $errors['verify_food'] = "L'aliment existe déjà";
      }

      if ($_POST["select_food_allergic"] != 'null') {
         $idFood = $pdo->lastInsertId();
         $reqInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
         $reqInsertFoodAllergic->execute([$idFood, $_POST["select_food_allergic"]]);
      }
   } else {
      $errors['form_add_food'] = "Vous n'avez pas donner de nom d'aliment";
   }
}

if (isset($_POST['modify_food'])) {
   $reqVerifyFoodAllergic = $pdo->prepare('SELECT * FROM food_allergic WHERE food_id = ?');
   $reqVerifyFoodAllergic->execute([$_GET['food']]);
   $verifyFoodAllergic = $reqVerifyFoodAllergic->fetch();

   if ($_POST["modify_food_allergic"] != "null" && $verifyFoodAllergic) {
      //modify foodAllergic
      $reqUpdateFoodAllergic = $pdo->prepare('UPDATE food_allergic SET allergic_id = ? WHERE food_id = ?');
      $reqUpdateFoodAllergic->execute([$_POST["modify_food_allergic"], $_GET['food']]);
   } elseif ($_POST["modify_food_allergic"] != "null") {
      //insert foodAllergic
      $reqInsertFoodAllergic = $pdo->prepare('INSERT INTO food_allergic SET food_id = ?, allergic_id = ?');
      $reqInsertFoodAllergic->execute([$_GET['food'], $_POST["modify_food_allergic"]]);
   } else {
      $reqDeleteFoodAllergic = $pdo->prepare("DELETE FROM food_allergic WHERE food_id = ?")->execute([$_GET['food']]);
   }

   if (isset($_POST["modify_food_origin"]) && $_POST["modify_food_breeding"] != 'null') {
      $reqUpdateFood = $pdo->prepare('UPDATE food SET food_name = ?, food_origin = ?, food_breeding = ? WHERE food_id = ?');
      $reqUpdateFood->execute([ucfirst($_POST["modify_food_name"]), ucfirst($_POST["modify_food_origin"]), $_POST["modify_food_breeding"], $_GET['food']]);
   } elseif (isset($_POST["modify_food_origin"])) {
      $reqUpdateFood = $pdo->prepare('UPDATE food SET food_name = ?, food_origin = ? WHERE food_id = ?');
      $reqUpdateFood->execute([ucfirst($_POST["modify_food_name"]), ucfirst($_POST["modify_food_origin"]), $_GET['food']]);
   } else {
      $reqUpdateFood = $pdo->prepare('UPDATE food SET food_name = ? WHERE food_id = ?');
      $reqUpdateFood->execute([ucfirst($_POST["modify_food_name"]), $_GET['food']]);
   }
   if (isset($_GET)) {
      // unset($_GET['food']);
      // header('location: panel.php', true);
      $urlLogin = "panel.php";
      echo '<script type="text/javascript">window.location.href="' . $urlLogin . '";</script>';
   }
}

if (isset($_POST['delete_food'])) {
   if (!empty($_POST["checkbox_delete_food"])) {
      foreach ($_POST["checkbox_delete_food"] as $deleteFood) {
         $reqHaveFood = $pdo->prepare("SELECT * FROM have_food WHERE food_id = ?");
         $reqHaveFood->execute([$deleteFood]);
         $haveFood = $reqHaveFood->fetch();

         $reqNameDishes = $pdo->prepare("SELECT * FROM dishes WHERE dishes_id = ?");
         $reqNameDishes->execute([$haveFood->dishes_id]);
         $nameDishes = $reqNameDishes->fetch();
         $reqNameFood = $pdo->prepare('SELECT * FROM food WHERE food_id = ?');
         $reqNameFood->execute([$deleteFood]);
         $nameFood = $reqNameFood->fetch();
         if (!$haveFood) {
            $reqDeleteFoodAllergic = $pdo->prepare('DELETE FROM food_allergic WHERE food_id = ?')->execute([$deleteFood]);
            $reqDeleteFood = $pdo->prepare('DELETE FROM food WHERE food_id = ?')->execute([$deleteFood]);
            $_SESSION['flash']['danger'] .= "L'aliment " . $nameFood->food_name . " a été supprimé <br>";
         } else {
            $errors['no_delete_food'] .= "Vous ne pouvais pas supprimer l'aliment <b>" . ucfirst($nameFood->food_name) . "</b> car il fait partie de la liste d'ingrédients du plat <b>" . $nameDishes->dishes_name . "</b><br>";
         }
      }
   } else {
      $errors['delete_food'] = "Vous n'avez pas sélectionné d'aliment(s) a supprimer";
   }
   if (isset($_GET)) {
      unset($_GET['food']);
   }
}
