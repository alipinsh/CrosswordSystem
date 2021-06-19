<?php
/*
 * Kontrollera klase, kura attēlo lapu ar visiem tagiem.
 */

namespace App\Controllers;

use App\Models\TagModel;

class TagController extends BaseController
{
    protected $tagModel;

    public function __construct() {
        $this->tagModel = new TagModel();
    }

    public function listAll()
    {
        return view('tags', ['tags' => $this->tagModel->getAllPublicTags()]);
    }
}
