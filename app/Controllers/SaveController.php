<?php
/*
 * Kontrollera klase, kura atbild par lietotāju mīkla progresa datiem.
 */

namespace App\Controllers;

use App\Models\SaveModel;
use CodeIgniter\Model;
use Config\Services;
use App\Models\CrosswordModel;
use App\Models\UserModel;

class SaveController extends BaseController {

    protected $saveModel;

    public function __construct() {
        $this->saveModel = new SaveModel();
    }

    public function save() {
        $userId = $this->session->get('userData.id');
        if (!$userId) {
            return $this->response->setJSON(['error' => lang('Account.notLoggedIn')]);
        }

        $crosswordId = $this->request->getPost('crosswordId');
        $crossword = $this->crosswordModel->find($crosswordId);
        $crosswordData = json_decode($crossword['data'], true);

        if (!$crossword || !$crossword['is_public']) {
            return $this->response->setJSON(['error' => lang('Crossword.doesNotExist')]);
        }

        $saveData = json_decode($this->request->getPost('progress'), true);

        $this->saveModel->validateSaveData($saveData, $crosswordData);

        $oldSave = $this->saveModel->where('crossword_id', $crosswordId)->where('user_id', $userId)->find();
        if ($oldSave) {
            $oldSave['save_data'] = $saveData;
            $this->saveModel->save($oldSave);
        } else {
            $this->saveModel->save([
                'crossword_id' => $crosswordId,
                'user_id' => $userId,
                'save_data' => json_encode($saveData)
            ]);
        }

        return $this->response->setJSON(['success']);
    }

    public function savesList() {
        $userId = $this->session->get('userData.id');
        if ($userId) {
            return view('saves', ['saves' => $this->saveModel->getSavesListByUser($userId)]);
        } else {
            return $this->response->redirect('/login');
        }
    }

    public function deleteSave() {
        $saveId = $this->request->getPost('saveId');
        $save = $this->saveModel->find($saveId);
        if ($save && $save['user_id'] == $this->session->get('userData.id')) {
            $this->saveModel->delete($saveId);
            return $this->response->setJSON(['deleted_id' => $saveId]);
        } else {
            return $this->response->setJSON(['error' => 'save is not yours']);
        }
    }
}
