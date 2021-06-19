<?php
/*
 * Kontrollera klase, kura veido galvenu lapu.
 */

namespace App\Controllers;

use App\Models\CrosswordModel;

class HomeController extends BaseController
{
	public function index()
	{
		return view('home', ['latestCrosswords' => $this->crosswordModel->getCrosswordList(5)]);
	}
}
