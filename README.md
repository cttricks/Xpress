# Xpress
Xpress is a versatile PHP library designed to streamline the creation of API endpoints, drawing inspiration from the simplicity and elegance of `Express.js`, a popular framework in the `Node.js` ecosystem. This library, currently in its beta stage, empowers developers to handle HTTP GET and POST requests with ease.

### Requirements
- PHP >= 7.1

### Routing

```php
<?php
/*This is index.php file*/
include __DIR__ .'/src/xpress.php';

APP::get('/', function($req, $res){
    $res->send('Xpress is live! 🥳🥳');
});

APP::end();
```
### 1. Virtual prefix

```php
APP::get('/user/:id', function($req, $res){
    $res->status(200)->json(array('userId'=> $req['id']));
});
```

### 2. Using external file
In order to use external file to route request and manage from another php file, you have to create a rout in `index.php` file. In this example it'll be in `data` dir i.e `data/vehicle.php`.
```php
<?php
/*This is index.php file*/
include __DIR__ .'/src/xpress.php';

/*Including external file to routs request of vehicle endpoits*/
APP::use('/vehicle', 'data/vehicle');

APP::end();
```

```php
<?php
/*This is vehicle.php file*/

$vehicleList = array('Tata Nexon', 'Kia Seltos', 'Hyundai Creta', 'Hyundai Exter', 'Mahindra Thar');

/*Exmaple 1 | With user defined status code | URL: http://localhost/vehicle*/
APP::get('/', function($req, $res){
    $res->status(200)->send('Hello! from vehicle.php file.');
});

/*Example 2 | With app defined status code | URL: http://localhost/vehicle/list*/
APP::get('/list', function($req, $res) use ($vehicleList){
    $res->json($vehicleList);
});

/*Exmaple 3 | With virtual prefix | URL: http://localhost/vehicle/name/1*/
APP::get('/name/:index', function($req, $res) use ($vehicleList){
    $res->send($vehicleList[$req['index']]);
});

APP::end();
```
### 3. Redirect
You can redirect a virtual path to any other endpoint, with status code & params. Here `true` is to include all params else you can pass an array of keys to be include in redirect URI. 

```php
APP::redirect("/github", "https://github.com/cttricks", 302, true);
```
Or you can also include virtual prefix & use that as params in target URL
```php
APP::redirect("/github/:tab", "https://github.com/cttricks", 302, ['tab']);
```
Or keep it simeple & redirect without params
```php
APP::redirect("/github", "https://github.com/cttricks");
```

Updating Soon...
