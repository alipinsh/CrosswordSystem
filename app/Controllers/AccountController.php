<?php
/*
 * Kontrollera klase, lai apstrādātu darbības konta lapā.
 */

namespace App\Controllers;

use Config\Email;
use Config\Services;
use App\Models\UserModel;
use App\Models\CrosswordModel;

class AccountController extends BaseController {
    protected $image;

    public function __construct() {
        $this->image = service('image');
        helper(['text', 'mail']);
    }

    public function account() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }

        $userId = $this->session->get('userData.id');
        $user = $this->userModel->find($userId);

        $data = [
            'user' => [
                'id' => $userId,
                'username' => $user['username'],
                'image' => $user['image'],
                'favoritedCount' => $user['favorited_count'],
                'createdCount' => $user['created_count'],
                'registeredOn' => $user['registered_on'],
                'createdCrosswords' =>
                    $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::CREATED, 5),
                'favoritedCrosswords' =>
                    $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::FAVORITED, 5)
            ],
            'isMine' => true
        ];
        
        return view('account', $data);
    }

    public function profile($username = null) {
        $userId = $this->userModel->getIdByUsername($username);
        if (!$userId) {
            return view('not_found');
        }
        if ($this->session->get('userData.id') == $userId) {
            return redirect()->to('/account');
        }

        $user = $this->userModel->find($userId);
        $data = [
            'user' => [
                'id' => $userId,
                'username' => $user['username'],
                'image' => $user['image'],
                'favoritedCount' => $user['favorited_count'],
                'createdCount' => $user['created_count'],
                'registeredOn' => $user['registered_on'],
                'createdCrosswords' =>
                    $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::CREATED, 5),
                'favoritedCrosswords' =>
                    $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::FAVORITED, 5)
            ],
            'isMine' => false
        ];

        return view('account', $data);
    }
    
    public function changeEmail() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find($this->session->get('userData.id'));
        if (
            empty($this->request->getPost('password')) ||
            !password_verify($this->request->getPost('password'), $user['password_hash'])
        ) {
            return redirect()->to('/account')->withInput()->with('error', lang('Account.wrongCredentials'));
        }

        $rules = [
            'new_email' => 'required|valid_email|is_unique[users.email]',
            'auth_code'	=> 'required'
        ];
        $this->userModel->setValidationRules($rules);
        $updatedUser = [
            'id' => $this->session->get('userData.id'),
            'new_email' => $this->request->getPost('new_email'),
            'auth_code'	=> random_string('alnum', 16)
        ];
        if (!$this->userModel->save($updatedUser)) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        $this->session->push('userData', ['new_email' => $updatedUser['new_email']]);
        send_mail($updatedUser['new_email'], lang('Account.confirmEmail'), 'confirmation', $updatedUser['auth_code']);
        send_mail($user['email'], lang('Account.emailUpdateRequest'), 'notification', []);
        return redirect()->to('/account')->with('success', lang('Account.emailUpdateStarted'));
    }
    
    public function confirmNewEmail() {
        $user = $this->userModel->where('auth_code', $this->request->getGet('token'))
                                   ->where('new_email !=', null)
                                ->first();

        if (!$user) {
            return redirect()->to('/account')->with('error', lang('Account.activationNoUser'));
        }
        
        $new_mail = $user['new_email'];
        $this->userModel->update($user['id'], ['email' => $new_mail, 'new_email' => null, 'auth_code' => null]);
        
        if ($this->session->get('userData.id')) {
            $this->session->push('userData', [
                'email'		=> $new_mail,
                'new_email'	=> null
            ]);

            return redirect()->to('/account')->with('success', lang('Account.confirmEmailSuccess'));
        }

        return redirect()->to('/login')->with('success', lang('Account.confirmEmailSuccess'));
    }
    
    public function changePassword() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }

        $rules = [
            'password' 	=> 'required|min_length[8]|max_length[64]',
            'new_password' => 'required|min_length[8]|max_length[64]',
            'new_password_confirm' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/account')->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->find($this->session->get('userData.id'));

        if (!password_verify($this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->to('/account')->withInput()->with('error', lang('Account.wrongCredentials'));
        }
        
        $this->userModel->update($this->session->get('userData.id'), ['password' => $this->request->getPost('new_password')]);
        
        return redirect()->to('/account')->with('success', lang('Account.passwordUpdateSuccess'));
    }
    
    public function uploadImage() {
        $userId = $this->session->get('userData.id');
        if (!$userId) {
            return $this->response->setJSON(['error' => 'user unauthorized']);
        }

        $valid = $this->validate([
            'image' => 'uploaded[image]|mime_in[image,image/png,image/jpeg,image/gif]|ext_in[image,png,jpg,jpeg,jpe,gif]|max_size[image,256]'
        ]);

        if ($valid) {
            $file = $this->request->getFile('image');
            $newFilename = $userId . '.' . $file->guessExtension();
            $newFilepath = FCPATH . 'img' . DIRECTORY_SEPARATOR . 'avatar';

            $user = $this->userModel->find($userId);
            if ($user['image'] != 'default.png') {
                unlink($newFilepath . DIRECTORY_SEPARATOR . $user['image']);
                unlink($newFilepath . DIRECTORY_SEPARATOR . 'min' . DIRECTORY_SEPARATOR . $user['image']);
            }
            $file->move($newFilepath, $newFilename);

            $this->image->withFile($newFilepath . DIRECTORY_SEPARATOR . $newFilename)
                        ->fit(64, 64, 'center')
                        ->save($newFilepath . DIRECTORY_SEPARATOR . 'min' . DIRECTORY_SEPARATOR . $newFilename);

            $user['image'] = $newFilename;
            $this->userModel->save($user);
            $this->session->set('userData.image', $newFilename);

            return $this->response->setJSON(['new_image' => $user['image']]);
        }

        return $this->response->setJSON(['error' => 'file format error!']);
    }

    public function changePreferences() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }

        $showSaveOnHome = boolval($this->request->getPost('show_save_on_home'));
        $this->userModel->update($this->session->get('userData.id'), ['show_save_on_home' => $showSaveOnHome]);
        $this->session->set('userData.show_save_on_home', $showSaveOnHome);
        
        return redirect()->to('/account')->with('success', lang('Account.preferencesUpdateSuccess'));
    }
}
