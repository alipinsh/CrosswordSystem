<?php
/*
 * Galvenais kontroleris, uz kuras ir bazēti citi kontroleri.
 * Nodefinēti modeli, kuri izmantoti gandrīz visos kontroleros.
 */

namespace App\Controllers;

use App\Models\CrosswordModel;
use App\Models\UserModel;
use CodeIgniter\Controller;
use Config\Services;

class BaseController extends Controller
{
    protected $session;
	protected $helpers = [];

	protected $userModel;
	protected $crosswordModel;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);
        $this->session = Services::session();
        $this->userModel = new UserModel();
        $this->crosswordModel = new CrosswordModel();
	}

}
