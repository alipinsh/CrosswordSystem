<?php
/*
 * Kontrollera klase, kura atbild par mīklu patikam.
 */

namespace App\Controllers;

use Config\Services;
use App\Models\CrosswordModel;
use App\Models\UserModel;
use Config\Database;

class FavoriteController extends BaseController {

    public function favorite() {
        $userId = $this->session->get('userData.id');
        $crosswordId = $this->request->getPost('crossword_id');
        if (!$userId) {
            return $this->response->setJSON(['error' => lang('Account.notLoggedIn')]);
        }
        if (!$this->crosswordModel->where('is_public', true)->find($crosswordId)) {
            return $this->response->setJSON(['error' => lang('Crossword.doesNotExist')]);
        }

        $this->crosswordModel->getFavorited($crosswordId, $userId);
        $this->userModel->updateFavoritedCount($userId);
        return $this->response->setJSON(['success']);
    }
}
