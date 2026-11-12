<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	https://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There are three reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router which controller/method to use if those
  | provided in the URL cannot be matched to a valid route.
  |
  |	$route['translate_uri_dashes'] = FALSE;
  |
  | This is not exactly a route, but allows you to automatically route
  | controller and method names that contain dashes. '-' isn't a valid
  | class or method name character, so it requires translation.
  | When you set this option to TRUE, it will replace ALL dashes in the
  | controller and method URI segments.
  |
  | Examples:	my-controller/index	-> my_controller/index
  |		my-controller/my-method	-> my_controller/my_method
 */
$route['default_controller'] = 'welcome';
$route['404_override'] = 'handler/show_404';
$route['translate_uri_dashes'] = FALSE;
$route['v1/register-student'] = 'api/students/register';
$route['api/health/ping'] = 'api/health/ping';
$route['api/health/status'] = 'api/health/status';
$route['api/users'] = 'api/users/list_users';
$route['api/users/(:num)'] = 'api/users/show_user/$1';
$route['api/users/create'] = 'api/users/create_user';
$route['swagger'] = 'swagger';
$route['swagger/json'] = 'swagger/json';
$route['docs'] = 'swagger';
$route['docs/json'] = 'swagger/json';
$route['dashboard'] = 'dashboard/index';
$route['api/dashboard'] = 'dashboard/index';

// Auth routes — use existing top-level Auth controller
$route['auth/login'] = 'auth/login';
$route['auth/keys'] = 'auth/keys';
$route['apiauth/login'] = 'auth/login';
$route['apiauth/keys'] = 'auth/keys';
$route['api/auth/login'] = 'auth/login';
$route['api/auth/keys'] = 'auth/keys';
$route['api/apiauth/login'] = 'auth/login';
$route['api/apiauth/keys'] = 'auth/keys';

// Drivers routes
$route['drivers'] = 'drivers/index';
$route['drivers/details'] = 'drivers/details';
$route['drivers/add'] = 'drivers/add';
$route['drivers/edit'] = 'drivers/edit';
$route['drivers/delete'] = 'drivers/delete';

// Attenders routes
$route['attenders'] = 'attenders/index';
$route['attenders/details'] = 'attenders/details';
$route['attenders/add'] = 'attenders/add';
$route['attenders/edit'] = 'attenders/edit';
$route['attenders/delete'] = 'attenders/delete';

// Studentdetails routes
$route['studentdetails'] = 'studentdetails/index';
$route['studentdetails/details'] = 'studentdetails/details';
$route['studentdetails/latest_boarding_point'] = 'studentdetails/latest_boarding_point';
$route['studentdetails/search'] = 'studentdetails/search';
$route['studentdetails/register'] = 'studentdetails/register';
$route['studentdetails/status'] = 'studentdetails/status';

$route['api/studentdetails'] = 'studentdetails/index';
$route['api/studentdetails/details'] = 'studentdetails/details';
$route['api/studentdetails/latest_boarding_point'] = 'studentdetails/latest_boarding_point';
$route['api/studentdetails/search'] = 'studentdetails/search';
$route['api/studentdetails/register'] = 'studentdetails/register';
$route['api/studentdetails/status'] = 'studentdetails/status';
$route['api/students/register'] = 'api/students/register';

// Boardingpoint routes
$route['boardingpoint'] = 'boardingpoint/index';
$route['boardingpoint/details'] = 'boardingpoint/details';
$route['boardingpoint/add'] = 'boardingpoint/add';
$route['boardingpoint/update_status'] = 'boardingpoint/update_status';
$route['boardingpoint/delete'] = 'boardingpoint/delete';

$route['api/boardingpoint'] = 'boardingpoint/index';
$route['api/boardingpoint/details'] = 'boardingpoint/details';
$route['api/boardingpoint/add'] = 'boardingpoint/add';
$route['api/boardingpoint/update_status'] = 'boardingpoint/update_status';
$route['api/boardingpoint/delete'] = 'boardingpoint/delete';

// Routes API (Get routes corresponding to bus ID)
$route['routes'] = 'api/routes/index';
$route['routes/by_bus/(:num)'] = 'api/routes/by_bus/$1';
$route['routes/details'] = 'api/routes/details';

$route['api/routes'] = 'api/routes/index';
$route['api/routes/by_bus/(:num)'] = 'api/routes/by_bus/$1';
$route['api/routes/details'] = 'api/routes/details';

$route['route'] = 'api/routes/index';
$route['route/by_bus/(:num)'] = 'api/routes/by_bus/$1';
$route['route/details'] = 'api/routes/details';

$route['api/route'] = 'api/routes/index';
$route['api/route/by_bus/(:num)'] = 'api/routes/by_bus/$1';
$route['api/route/details'] = 'api/routes/details';




