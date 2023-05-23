<?php

include './sys/info_db.php';

// Table utilisateur

$createRoleTable =
   'CREATE TABLE IF NOT EXISTS role(
   role_id INT AUTO_INCREMENT,
   role_name VARCHAR(50) NOT NULL,
   PRIMARY KEY(role_id)
)';

$createUsersTable =
   'CREATE TABLE IF NOT EXISTS users(
   user_id INT AUTO_INCREMENT,
   user_email VARCHAR(50)  NOT NULL,
   user_password TEXT NOT NULL,
   user_confirmation_token TEXT,
   user_confirmed_at DATE,
   user_register_date DATETIME,
   user_remember_token TEXT,
   user_role_id INT NOT NULL,
   PRIMARY KEY(user_id),
   FOREIGN KEY(user_role_id) REFERENCES role(role_id)

)';

$createProfilTable =
   'CREATE TABLE IF NOT EXISTS profil(
   profil_id INT,
   profil_lastname VARCHAR(30),
   profil_firstname VARCHAR(30),
   profil_civility VARCHAR(20),
   profil_age INT(10),
   profil_email VARCHAR(50),
   profil_default_reservation INT,
   PRIMARY KEY(profil_id),
   FOREIGN KEY(profil_id) REFERENCES users(user_id)
)';

$createTriggerUserToProfil =
   'CREATE TRIGGER IF NOT EXISTS on_user_created
   AFTER INSERT ON users
   FOR EACH ROW
   BEGIN
      INSERT INTO profil (profil_id, profil_email)
      VALUES (NEW.user_id, NEW.user_email);
   END;';

// Table planning 

$createPlanning = 'CREATE TABLE IF NOT EXISTS planning(
   planning_id INT AUTO_INCREMENT,
   planning_name VARCHAR(50) NOT NULL,
   planning_hours_open TIME,
   planning_hours_close TIME,
   planning_second_hours_open TIME,
   planning_second_hours_close TIME,
   planning_close VARCHAR(30),
   PRIMARY KEY(planning_id)
);';

// table calendar 

$createTableCalendar = 'CREATE TABLE IF NOT EXISTS calendar(
   id_date INT AUTO_INCREMENT,
   date_calendar DATE NOT NULL,
   part_day INT NOT NULL,
   number_reservation INT NOT NULL,
   close_day VARCHAR(255),
   PRIMARY KEY(id_date)
);';

// table image

$createTableImage = "CREATE TABLE IF NOT EXISTS images(
   images_id INT,
   images_path TEXT,
   PRIMARY KEY(images_id)
);";

// Table nourriture

$createAllergicTable = 'CREATE TABLE IF NOT EXISTS allergic(
   allergic_id INT AUTO_INCREMENT,
   allergic_name VARCHAR(50) NOT NULL,
   allergic_food TEXT NOT NULL,
   PRIMARY KEY(allergic_id)
);';

$createFoodTable = 'CREATE TABLE IF NOT EXISTS food(
   food_id INT AUTO_INCREMENT ,
   food_name VARCHAR(50) NOT NULL,
   food_origin VARCHAR(255),
   food_breeding INT,
   PRIMARY KEY(food_id)
);';


$createFoodAllergicTable = 'CREATE TABLE IF NOT EXISTS food_allergic(
   food_id INT NOT NULL,
   allergic_id INT NOT NULL,
   PRIMARY KEY(food_id, allergic_id),
   FOREIGN KEY(food_id) REFERENCES food(food_id),
   FOREIGN KEY(allergic_id) REFERENCES allergic(allergic_id)
);';


$createDischesTable = 'CREATE TABLE IF NOT EXISTS dishes(
   dishes_id INT AUTO_INCREMENT,
   dishes_name VARCHAR(50)  NOT NULL,
   dishes_food TEXT NOT NULL,
   dishes_description TEXT,
   sub_categorie_id INT NOT NULL,
   dishes_temp DATE, 
   PRIMARY KEY(dishes_id),
   FOREIGN KEY(sub_categorie_id) REFERENCES sub_categorie(sub_categorie_id)
);';

$createHaveFoodTable = 'CREATE TABLE IF NOT EXISTS have_food(
   food_id INT,
   dishes_id INT,
   PRIMARY KEY(food_id, dishes_id),
   FOREIGN KEY(food_id) REFERENCES food(food_id),
   FOREIGN KEY(dishes_id) REFERENCES dishes(dishes_id)
);';

$createCategorieFoodTable = 'CREATE TABLE IF NOT EXISTS categorie(
   categorie_id INT AUTO_INCREMENT,
   categorie_name VARCHAR(50) NOT NULL,
   PRIMARY KEY(categorie_id)
);';

$createSubcategorieFoodTable = 'CREATE TABLE IF NOT EXISTS sub_categorie(
   sub_categorie_id INT AUTO_INCREMENT, 
   sub_categorie_name VARCHAR(50) NOT NULL,
   categorie_id INT NOT NULL,
   PRIMARY KEY(sub_categorie_id),
   FOREIGN KEY (categorie_id) REFERENCES categorie(categorie_id)
);';

// amuse bouche / entrée / plats / desserts / fromage / vin 

$createMenuTable = 'CREATE TABLE IF NOT EXISTS menu(
   menu_id INT AUTO_INCREMENT,
   menu_price DECIMAL(15,2) NOT NULL,
   menu_title VARCHAR(50) NOT NULL,
   menu_description TEXT,
   menu_name VARCHAR(50),
   menu_end_date DATE,
   PRIMARY KEY(menu_id)
);';

$createHaveMenuTable = 'CREATE TABLE IF NOT EXISTS have_menu(
   menu_id INT,
   dishes_id INT,
   menu_categorie INT,
   PRIMARY KEY(menu_id, dishes_id),
   FOREIGN KEY(menu_id) REFERENCES menu(menu_id),
   FOREIGN KEY(dishes_id) REFERENCES dishes(dishes_id)
);
';

$createRole = [
   'membre',
   'admin'
];


try {
   $pdo = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

   $pdo->exec($createAllergicTable);
   $pdo->exec($createFoodTable);
   $pdo->exec($createFoodAllergicTable);
   $pdo->exec($createCategorieFoodTable);
   $pdo->exec($createSubcategorieFoodTable);
   $pdo->exec($createDischesTable);
   $pdo->exec($createHaveFoodTable);
   $pdo->exec($createMenuTable);
   $pdo->exec($createHaveMenuTable);
   $pdo->exec($createTableImage);
   $pdo->exec($createTableCalendar);

   $pdo->exec($createRoleTable);
   $pdo->exec($createUsersTable);
   $pdo->exec($createProfilTable);
   $pdo->exec($createTriggerUserToProfil);

   $pdo->exec($createPlanning);
} catch (PDOException $e) {
   echo "Connection échoué: " . $e->getMessage();
}

foreach ($createRole as $role) {
   $reqInsertRole = $pdo->prepare('INSERT INTO role SET role_name = ?');

   $reqSelectRole = $pdo->prepare('SELECT * FROM role WHERE role_name = ?');
   $reqSelectRole->execute([$role]);
   $selectRole = $reqSelectRole->fetch();

   if (!$selectRole) {
      $reqInsertRole->execute([$role]);
   }
}
