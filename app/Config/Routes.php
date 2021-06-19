<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('HomeController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->get('/', 'HomeController::index');

$routes->get('register', 'AuthController::register');
$routes->post('register', 'AuthController::register');

$routes->get('login', 'AuthController::login', ['as' => 'login']);
$routes->post('login', 'AuthController::login');
$routes->get('logout', 'AuthController::logout');

$routes->get('activate-account', 'AuthController::activateAccount');

$routes->get('forgot-password', 'AuthController::forgotPassword');
$routes->post('forgot-password', 'AuthController::forgotPassword');
$routes->get('reset-password', 'AuthController::resetPassword');
$routes->post('reset-password', 'AuthController::resetPassword');

$routes->post('change-email', 'AccountController::changeEmail');
$routes->get('confirm-email', 'AccountController::confirmNewEmail');
$routes->post('change-password', 'AccountController::changePassword');

$routes->post('upload-image', 'AccountController::uploadImage');

$routes->get('account', 'AccountController::account');
$routes->get('profile/(:segment)', 'AccountController::profile/$1');

$routes->get('crossword', 'CrosswordController::view');
$routes->get('crossword/(:num)', 'CrosswordController::view/$1');
$routes->get('crosswords', 'CrosswordController::listAll');
$routes->get('crosswords/all', 'CrosswordController::listAll');
$routes->get('crosswords/u/(:segment)', 'CrosswordController::listCreated/$1');
$routes->get('crosswords/u/(:segment)/created', 'CrosswordController::listCreated/$1');
$routes->get('crosswords/u/(:segment)/favorited', 'CrosswordController::listFavorited/$1');
$routes->get('crosswords/tag/(:segment)', 'CrosswordController::listByTag/$1');
$routes->get('crosswords/search/(:segment)', 'CrosswordController::search/$1');
$routes->get('crosswords/private', 'CrosswordController::listPrivates');

$routes->get('crossword/edit', 'CrosswordController::edit');
$routes->get('crossword/edit/(:num)', 'CrosswordController::edit/$1');
$routes->post('crossword/save', 'CrosswordController::save');
$routes->post('crossword/delete/(:num)', 'CrosswordController::delete/$1');

$routes->get('tags', 'TagController::listAll');

$routes->post('favorite', 'FavoriteController::favorite');
$routes->post('comment', 'CommentController::post');
$routes->get('comments', 'CommentController::get');
$routes->post('comment/edit', 'CommentController::edit');
$routes->post('comment/delete', 'CommentController::delete');
$routes->post('saves/save', 'SaveController::save');
$routes->post('saves/delete', 'SaveController::deleteSave');
$routes->get('saves', 'SaveController::savesList');

$routes->get('moderation', 'ModerationController::viewReports');
$routes->post('moderation/action', 'ModerationController::action');
$routes->post('moderation/free', 'ModerationController::free');
$routes->post('moderation/report', 'ModerationController::sendReport');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
