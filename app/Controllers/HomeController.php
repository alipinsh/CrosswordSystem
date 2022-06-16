<?php
/*
 * Kontrollera klase, kura veido galvenu lapu.
 */

namespace App\Controllers;

use App\Models\SaveModel;

class HomeController extends BaseController
{
    protected $saveModel;

    public function __construct() {
        $this->saveModel = new SaveModel();
    }

    public function index()
    {
        $vars = ['latestCrosswords' => $this->crosswordModel->getCrosswordList(5)];
        if ($this->session->get('userData.show_save_on_home')) {
            $vars['latestSaves'] = $this->saveModel->getSavesListByUser($this->session->get('userData.id'), 3, 0);
        }

        return view('home', $vars);
    }
}
