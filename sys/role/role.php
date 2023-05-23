<?php
$reqRoles = $pdo->prepare('SELECT * FROM role ORDER BY role_name');
$reqRoles->execute();
$listRoles = $reqRoles->fetchAll();
?>

<form method="post">

   <?php
   foreach ($listRoles as $roles) {
      echo '<label>' . $roles->role_name . '</label>';
      if ($roles->role_name != 'admin') {
         echo '<a type="button" href="./panel.php?role=' . $roles->role_id . '"><img src="./svg/sticky.svg"></a>
      <input type="checkbox" name="checkbox_delete_roles[]" value="' . $roles->role_id . '">';
      }
      echo '<br>';
   }
   ?>

   <input type="submit" name="button_add_role" value="Ajouter un role">
   <input type="submit" name="delete_role" value="Supprimer">
</form>

<?php
if (isset($_POST['button_add_role'])) {
   echo '<form method="post">
      <input type="text" name="role_name">
      <input type="submit" name="add_role" value="Ajouter">
   </form>';
}

if (isset($_GET['role'])) {
   $reqSelectUpdateRole = $pdo->prepare('SELECT role_name FROM role WHERE role_id = ?');
   $reqSelectUpdateRole->execute([$_GET['role']]);
   $role = $reqSelectUpdateRole->fetch();

   echo '<form method="post">
      <input type="text" name="role_name_modify" value="' . $role->role_name . '">
      <input type="submit" name="modify_role" value="Modifer">
   </form>';
}
