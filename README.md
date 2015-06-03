**Kecik Database**
================
> **PayPal**: [![](https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=dony_cavalera_md%40yahoo%2ecom&lc=US&item_name=Dony%20Wahyu%20Isp&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest)
> 
> **Rekening Mandiri**: 113-000-6944-858, **Atas Nama**: Dony Wahyu Isprananda

Merupakan pustaka/library yang dibuat khusus Framework Kecik, pustaka/library ini dibuat untuk mempermudah anda dalam menggunakan database kedalam project yang anda buat. Pustaka/Library ini, saat ini support untuk database mysql, oracle, postgresql, mongodb dan PDO.

## **Cara Installasi**
file composer.json
```json
{
	"require": {
		"kecik/kecik": "1.0.*@dev",
		"kecik/database": "1.0.*@dev"
	}
}
```

Jalankan perintah
```shell
composer install
```

## **Settingan Untuk MySQL**
Nilai settingan darive untuk database ini adalah `mysqli` jadi pustaka/library ini tidak menggunakan dari mysql, tapi menggunakan driver mysqli.

**Contoh:**
```php
$app->config->set('database.driver', 'mysqli');
$app->config->set('database.hostname', 'localhost');
$app->config->set('database.username', 'root');
$app->config->set('database.password', '1234567890');
$app->config->set('database.dbname', 'kecik');
```
## **Settingan Untuk PostgreSQL**
Nilai settingan driver untuk database ini adalah `pgsql`. Untuk PostgreSQL ada cara settingan yaitu:
### Tanpa DSN
**Contoh:**
```php
$app->config->set('database.driver', 'pgsql');
$app->config->set('database.hostname', 'localhost');
$app->config->set('database.username', 'postgres');
$app->config->set('database.password', '1234567890');
```
### Dengan DSN
**Contoh:**
```php
$app->config->set('database.driver', 'pgsql');
$app->config->set('database.dsn', "host=localhost port=5432 dbname=kecik user=postgres password=1234567890 options='--client_encoding=UTF8'");
```

## **Settingan Untuk Oracle**
Nilai settingan driver untuk database ini adalah `oci8`, settingan menggunakan dsn.

**Contoh:**
```php
$app->config->set('database.driver', 'oci8');
$app->config->set('database.dsn', 'localhost/xe');
$app->config->set('database.username', 'kecik');
$app->config->set('database.password', '1234567890');
```
## **Settingan Untuk MongoDB**
Nilai settingan driver untuk database ini adalah `mongo`.
 
**Contoh:**
```php
$app->config->set('database.driver', 'mongo');
$app->config->set('database.dsn', 'mongodb://localhost');
$app->config->set('database.dbname', 'kecik');
```

## **Settingan Menggunakan PDO**
Pustaka/library ini juga mendukung penggunaan driver PDO. Semua settingan menggunakan driver PDO semuanya menggunakan dsn.
**Contoh PDO MySQL:**
```php
$app->config->set('database.driver', 'pdo');
$app->config->set('database.dsn', 'mysql:host=localhost;dbname=kecik;');
$app->config->set('database.username', 'root');
$app->config->set('database.password', '1234567890');
```

**Contoh PDO PostgreSQL:**
```php
$app->config->set('database.driver', 'pdo');
$app->config->set('database.dsn', 'pgsql:host=localhost;dbname=kecik;');
$app->config->set('database.username', 'postgres');
$app->config->set('database.password', '1234567890');
```
**Contoh PDO Oracle:**
```php
$app->config->set('database.driver', 'pdo');
$app->config->set('database.dsn', 'oci:host=localhost;dbname=xe;');
$app->config->set('database.username', 'kecik');
$app->config->set('database.password', '1234567890');
```

### **INSERT**
Format dari fungsi insert.
```php
$app->db->$table->insert($data);
```

Struktur datanya adalah:
```php
$data = [
	'field_nama' => 'Dony Wahyu Isp',
	'field_email' => 'dna.extrim@gmail.com'
];
```

### **UPDATE**
Format dari fungsi update.
```php
$app->db->$table->update($key, $data);
```

Struktur key dan datanya adalah:
```php
//** $key
$key = ['id' => 2];

//** $data
$data = [
	'field_nama' => 'dnaextrim',
	'field_email' => 'dna.extrim@gmail.com'
];
```

### **DELETE**
Format fungsi delete.
```php
$app->db->$table->delete($key);
```
Struktur key nya adalah:
```php
$key = ['id' => 3];
```

### **SELECT**
Format untuk fungsi find/select
```php
$app->db->$table->find($filter, $limit, $order_by);
```
**SELECT Field**
```php
$app->db->$table->find([
	'select' => [
		['nama, email'], //** Cara Pertama
		['nama', 'email'], //** Cara Kedua
		['max'=>'nilai'], //** Cara Ketiga
		['max'=>'nilai', 'as'=>'nilai_maksimum'] //** Cara Keempat
	]
]);
```

> **Catatan:** Cara keempat hanya berlaku untuk database dengan SQL bukan untuk database NoSQL

**LIMIT**
```php
$app->db->$table->find([],[10]); //** Cara Pertama limit 10 baris
$app->db->$table->find([], [5, 10]); //** Cara Kedua limit dari posisi index ke 5 sebanyak 10 baris
```

**ORDER BY**
```php
$app->db->$table->find([],[],[
	'asc' => ['nama', 'email'], //** Pengurutan menaik/Ascending untuk field nama dan email
	'desc' => ['nama', 'email'] //** Pengurutan menurun/Descending untuk field nama dan email
]);
```

**WHERE**
Where tanpa pengelompokan
```php
$app->db->$table->find([
	'where'=> [
		["nama = 'Dony Wahyu Isp'"], //** Cara Pertama
		["nama", "='Dony Wahyu Isp'"], //** Cara Kedua
		["nama", "=", "'Dony Wahyu Isp'"], //** Cara Ketiga
		["nama = '?' AND email='?'" => [$nama, $email]], //** Cara Keempat
		["nama", "='?' AND email='?'" => [$nama, $email]], //** Cara Kelima
	]
]);
```

Where dengan pengelompokan
```php
$app->db->$table->find([
	'where' => [
		'and' => [
			'and' => [
				["nama", "=", "'Dony Wahyu Isp'"],
				["email", "=", "'dna.extrim@gmail.com'"]
			],
			'or' => [
				["nama", "=", "'Dony Wahyu Isp'"],
				["email", "=", "'dna.extrim@gmail.com'"]
			]
		]
	]
]);
```

**BETWEEN**
```php
$app->db->$table->find([
	'where' => [
		["nilai", "between", [50, 100]],
		["nilai", "not between", [50, 100]], //** Dengan NOT
	]
]);
```

**IN**
```php
$app->db->$table->find([
	'where' => [
		["nilai", "in", [50, 60, 70, 80]],
		["nilai", "not in", [50, 60, 70, 80]], //** Dengan NOT
	]
]);
```

**JOIN (Natural/Left/Right)**
```php
$app->db->$table->find([
	'join' => [
		['natural', 'table_profil'], //** Natural JOIN
		['left', 'table_profil', 'field_nama'], //** Left/Righ Join Cara Pertama
		['left', 'table_profil', [field_nama_profile, $field_nama_user]] //** Left/Right Join Cara Kedua
	]
]);
```
> **Catatan**: Untuk sementara ini join belum support untuk penggunaan database NoSQL seperti MongoDB


## Contoh penggunaan Pada Kecik Framework Versi 1.0.3
```php
<?php
require "vendor/autoload.php";

$config = [
	'libraries' => [
		'database' => [
			'enable' => TRUE,
			'config' => [
				'driver' => 'mysqli',
				'hostname' => 'localhost',
				'username' => 'root',
				'password' => '1234567890',
				'dbname' => 'kecik'
			]
		]
	]
];

$app = new Kecik\Kecik($config);
$con = $app->db->connect();

$res = $app->db->exec("SELECT * FROM data", 'data');
print_r($app->db->fetch($res));

$id = ['id'=>'2'];
$data = [
	'nama'=>'Dony Wahyu Isp',
	'email'=>'dna.extrim@gmail.com'
];

$db = $app->db;
$ret = $db->data->insert($data);
$ret = $db->data->update($id, $data);
$ret = $db->data->delete($id);

$app->get('/', function() use ($db){
	$rows = $db->data->find([
		'where' => [
			['nama', '=', "'Dony Wahyu Isp'"]
		]
	]);
	
	foreach ($rows as $row) {
		echo 'Nama: '.$row->nama.'<br />';
		echo 'Email: '.$row->email.'<hr />';
	}
});

$app->run();
?>
```
## Contoh Tanpa Menggunakan Autoload Library
```php
<?php
$app = new Kecik\Kecik();

$app->config->set('database.driver', 'mysqli');
$app->config->set('database.hostname', 'localhost');
$app->config->set('database.username', 'root');
$app->config->set('database.password', '1234567890');
$app->config->set('database.dbname', 'kecik');

$db = new Kecik\Database($app);
$con = $db->connect();

$res = $db->exec("SELECT * FROM data", 'data');
print_r($db->fetch($res));

$id = ['id'=>'2'];
$data = [
	'nama'=>'Dony Wahyu Isp',
	'email'=>'dna.extrim@gmail.com'
];

$ret = $db->data->insert($data);
$ret = $db->data->update($id, $data);
$ret = $db->data->delete($id);

$app->get('/', function() use ($db){
	$rows = $db->data->find([
		'where' => [
			['nama', '=', "'Dony Wahyu Isp'"]
		]
	]);
	
	foreach ($rows as $row) {
		echo 'Nama: '.$row->nama.'<br />';
		echo 'Email: '.$row->email.'<hr />';
	}
});

$app->run();
?>
```
