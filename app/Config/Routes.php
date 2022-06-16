<?php namespace Config;

use App\Controllers\ModerationController;

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

$routes->post('settings', 'AccountController::changeSettings');

$routes->get('account', 'AccountController::account');
$routes->get('profile/(:segment)', 'AccountController::profile/$1');

$routes->get('history', 'AccountController::crosswordHistory');

$routes->get('language', 'LanguageController::languagePage');
$routes->post('language', 'LanguageController::changeLanguage');

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

$routes->get('moderation/user', 'ModerationController::viewUsers');
$routes->post('moderation/user/switch', 'ModerationController::switchRoleUser');
$routes->post('moderation/user/delete', 'ModerationController::deleteUser');

$routes->get('moderation/crossword', 'ModerationController::viewReportsCrossword');
$routes->post('moderation/crossword/action', 'ModerationController::actionCrossword');
$routes->post('moderation/crossword/free', 'ModerationController::freeCrossword');
$routes->post('moderation/crossword/report', 'ModerationController::sendReportCrossword');

$routes->get('moderation/comment', 'ModerationController::viewReportsComment');
$routes->post('moderation/comment/action', 'ModerationController::actionComment');
$routes->post('moderation/comment/free', 'ModerationController::freeComment');
$routes->post('moderation/comment/report', 'ModerationController::sendReportComment');


/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
