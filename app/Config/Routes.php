<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Home Controller
$routes->get('/', 'HomeController::index');

// Auth Controller
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

// Account Controller
$routes->post('change-email', 'AccountController::changeEmail');
$routes->get('confirm-email', 'AccountController::confirmNewEmail');
$routes->post('change-password', 'AccountController::changePassword');
$routes->post('upload-image', 'AccountController::uploadImage');
$routes->post('change-preferences', 'AccountController::changePreferences');
$routes->get('account', 'AccountController::account');
$routes->get('profile/(:segment)', 'AccountController::profile/$1');

// Language Controller
$routes->get('language', 'LanguageController::languagePage');
$routes->post('language', 'LanguageController::changeLanguage');

// Crossword Controller
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

// Tag Controller
$routes->get('tags', 'TagController::listAll');

// Favorite Controller
$routes->post('favorite', 'FavoriteController::favorite');

// Comment Controller
$routes->post('comment', 'CommentController::post');
$routes->get('comments', 'CommentController::get');
$routes->post('comment/edit', 'CommentController::edit');
$routes->post('comment/delete', 'CommentController::delete');

// Save Controller
$routes->post('saves/save', 'SaveController::save');
$routes->post('saves/delete', 'SaveController::deleteSave');
$routes->get('saves', 'SaveController::savesList');

// Moderation Controller
$routes->get('moderation', 'ModerationController::moderationPage');
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
