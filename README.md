# Xpress
Xpress is a versatile PHP library designed to streamline the creation of API endpoints, drawing inspiration from the simplicity and elegance of `Express.js`, a popular framework in the `Node.js` ecosystem. This library, currently in its beta stage, empowers developers to handle HTTP GET and POST requests with ease.

### Requirements
- PHP >= 7.1

### Installation
Currently you can simply download this repository and start using it. No additional configuration required.

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
### Contribution

Contributions are welcome! If you think you can improve the performance, add more animations, or enhance the effects of xpress, I encourage you to contribute to the project.

To contribute, please follow these steps:

1. Fork the repository on GitHub.

2. Create a new branch with a descriptive name for your contribution.

3. Make your modifications, improvements, or additions to the codebase.

4. Test your changes to ensure they work as expected.

5. Commit your changes and push them to your forked repository.

6. Submit a pull request from your branch to the main repository's `master` branch.

I appreciate your contributions and will review your pull request as soon as possible. Feel free to provide any additional context, explanations, or documentation related to your contribution in the pull request description.

💡 Please note that by contributing to this repo, you agree to release your contributions under the Apache-2.0 License.

### License
Xpress is released under the Apache-2.0 License. See the LICENSE file for more details.

### Disclaimer

This project is a fun experiment and should not be used in a production environment without proper testing and security measures. Use it responsibly and at your own risk.

Feel free to reach out if you have any questions or encounter issues during setup. Happy coding!
