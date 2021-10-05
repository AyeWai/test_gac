<?php

// Vérification de la ligne de commande
if(count($argv) != 2) {
    echo "Usage: php index.php <string>";
    exit;
}

// Connection à la BDD
$host = '127.0.0.1';
$db   = 'test';
$user = 'root';
$pass = 'root';
$port = "3306";
$charset = 'utf8mb4';

$options = [
    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
    \PDO::ATTR_EMULATE_PREPARES   => false,
];
$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=$port";
try {
     $pdo = new \PDO($dsn, $user, $pass, $options);
} catch(\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Sauvegarde des données en BDD
$str_filename = "C:/ProgramData/MySQL/MySQL Server 5.7/Uploads/" . $argv[1];
$sql = "LOAD DATA INFILE '{$str_filename}' INTO TABLE test.tickets_appels
        FIELDS TERMINATED BY ';'
        ENCLOSED BY ''
        LINES TERMINATED BY '\r\n'
        IGNORE 3 LINES";
$pdo->exec($sql);

// Requête et affichage des résultats
$sql_dureeTotal = "SELECT SUM(ta.vol_reel) AS duree
        FROM test.tickets_appels ta
        WHERE ta.date > '15/02/2012'";
$dureeTotal = $pdo->query($sql_dureeTotal)->fetch();
echo "La durée totale réelle des appels effectués après le 15/02/2012 : {$dureeTotal['duree']} \n";

$sql_countSms = "SELECT COUNT(*) AS nombre_sms
        FROM test.tickets_appels ta
        WHERE ta.`type` = 'envoi de sms depuis le mobile'";
$countSms = $pdo->query($sql_countSms)->fetch();
echo "Quantité totale de SMS envoyés par l'ensemble des abonnés : {$countSms['nombre_sms']} \n";

$sql_topQtyDataAbo = "SELECT ta.num_abonne, SUM(ta.vol_fact) AS qty_conso
       FROM test.tickets_appels ta
       WHERE (ta.heure < '08:00:00' OR ta.heure > '18:00:00')
       GROUP BY ta.num_abonne
       ORDER BY qty_conso DESC LIMIT 10";
$arr_top10 = $pdo->query($sql_topQtyDataAbo);
while ($row = $arr_top10->fetch()) {
    echo "abonné numéro {$row['num_abonne']} a consommé {$row['qty_conso']} de data \n";
}

?>