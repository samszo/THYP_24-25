<?php
// Récupérer les paramètres POST
$gitRepo = $_GET['gitRepo'];//"https://github.com/Sidahmed-ben/recommendation-des-films.git";//
$gitFolderRoot = "/Users/hnparis8/Sites/THYP_24-25_clones/";
$gitFolder = $_GET['gitFolder'];//"projet1";//;
$dbName = "omk_thyp_24-25_".$gitFolder;//$_POST['dbName'];
$messages = [];
// Créer le répertoire
$directoryPath = $gitFolderRoot . $gitFolder;
if (!is_dir($directoryPath)) {
    if (mkdir($directoryPath, 0777, true)) {
        $messages[]="Répertoire créé avec succès !";
        // Cloner le dépôt GitHub
        $cloneCommand = "git clone $gitRepo $directoryPath";
        $output = shell_exec($cloneCommand);

        // Vérifier si le clonage a réussi
        if (is_dir($directoryPath)) {
            $messages[]="Clonage du dépôt GitHub réussi !";
        } else {
            $messages[]="Échec du clonage du dépôt GitHub.";
        }        
    } else {
        $messages[]="Erreur lors de la création du répertoire.";
    }
} else {
    $messages[]="Le répertoire existe déjà.";
    // Pull le dépôt GitHub
    $pullCommand = "cd $directoryPath && git pull";
    $output = shell_exec($pullCommand);
    $messages[]="GIT PULL : $output";
}

// Connexion à la base de données MySQL
$servername = "localhost";
$username = "omkTHYP2324";
$password = "omkTHYP2324";

// Création de la base de données
$connection = mysqli_connect($servername, $username, $password);

// Vérifier la connexion
if (!$connection) {
    $messages[]="Échec de la connexion à la base de données : " . mysqli_connect_error();
}

// Droper la base de données
$sql = "DROP DATABASE IF EXISTS `$dbName`";
if (mysqli_query($connection, $sql)) {
    $messages[]="Base de données supprimée avec succès";
} else {
    $messages[]="Erreur lors de la suppression de la base de données : " . mysqli_error($connection);
}

// Créer la base de données
$sql = "CREATE DATABASE `$dbName`";
if (mysqli_query($connection, $sql)) {
    $messages[]="Base de données créée avec succès";
} else {
    $messages[]="Erreur lors de la création de la base de données : " . mysqli_error($connection);
}
// Fermer la connexion
mysqli_close($connection);

// Création de la connexion
$connection = mysqli_connect($servername, $username, $password, $dbName);

// Vérifier la connexion
if (!$connection) {
    $messages[]="Échec de la connexion à la base de données : " . mysqli_connect_error();
}

// Chemin vers le fichier SQL à importer
$sqlFilePath = $directoryPath . "/data/bdd.sql";

// Lire le contenu du fichier SQL
$sqlContent = file_get_contents($sqlFilePath);

// Exécuter les requêtes SQL
if (mysqli_multi_query($connection, $sqlContent)) {
    $messages[]="Fichier SQL importé avec succès !";
} else {
    $messages[]="Échec de l'importation du fichier SQL : " . mysqli_error($connection);
}

// Fermer la connexion
mysqli_close($connection);

// Mettre à jour le fichier database.ini
$databaseIniPath = "/Users/hnparis8/Sites/omk_thyp_24-25_clone/config/database.ini";
$databaseIniContent = '
user     = "'.$username.'"
password = "'.$password.'"
dbname   = "'.$dbName.'"
host     = "localhost"
;port     = 
;unix_socket = 
;log_path = 
';

if (file_put_contents($databaseIniPath, $databaseIniContent)) {
    $messages[]="Fichier database.ini mis à jour avec succès !";
} else {
    $messages[]="Échec de la mise à jour du fichier database.ini.";
}

echo json_encode($messages); 





