<?php

use Noob\Route\Route;

require ("../vendor/autoload.php");
/**
 * Created by PhpStorm.
 * User: pxb
 * Date: 2018/8/9
 * Time: 下午3:27
 */

ini_set('display_errors', true);
ini_set('error_reporting', E_ALL);

Route::groupStart('Admin', ['user_login1','user_login2']);
Route::get('index/index', 'Index@index', ['middleware1','middleware2']);
Route::get('admin1/admin1/{one}', 'One@one');
Route::get('admin2/admin2/{}', 'Or@or');
Route::get('admin3/admin3/{all}', 'All@all');
Route::groupEnd();


Route::middlewareStart('home_middleware');
Route::get('hello/hello', 'Hello\Hello@hello');
Route::get('/', 'Home@home');
Route::middlewareEnd();

Route::post('index/index','PostIndex@index');

var_dump(Route::getRoute());
