**Kecik Database**
================

## Cara Installasi
file composer.json
```json
{
	"require": {
		"kecik/kecik": "1.0.2-alpha",
		"kecik/database": "dev-master"
	}
}
```

Jalankan perintah
```shell
composer install
```

## Contoh penggunaan
```php
<?php
/**
 * driver	: mysqli, oci8, mongo, pgsql
 * mysqli	: untuk Database MySQL
 * oci8		: untuk Database Oracle
 * mongo	: untuk Database MongoDB
 * pgsql	: untuk Database PostgreSQL
 */
require "vendor/autoload.php";

$app = new Kecik\Kecik();
$app->config->set('database.driver', 'oci8');
$app->config->set('database.hostname', 'localhost');
$app->config->set('database.username', 'kecik');
$app->config->set('database.password', '1234567890');

$app->config->set('database.dbname', 'xe');

$db = new Kecik\Database($app);
$con = $db->connect();

$res = $db->exec("SELECT * FROM data", 'data');
print_r($db->fetch($res));

$id = array('id'=>'2');
$data = array(
	'nama'=>'dnaextrim',
	'email'=>'dna.extrim@gmail.com'
);

$ret = $db->data->insert($data);
$ret = $db->data->update($id, $data);
$ret = $db->data->delete($id);

$app->get('/', function() use ($db){
	$rows = $db->data->find();
	
	foreach ($rows as $row) {
		echo 'Nama: '.$row->NAMA.'<br />';
		echo 'Email: '.$row->EMAIL.'<hr />';
	}
});

$app->run();
?>
```