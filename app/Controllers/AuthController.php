<?php
/*
 * Kontrollera klase, kura nodarbojas ar autentifikācijas darbībām.
 * Kods ir bazēts uz koda no šita repozitorija: https://github.com/divpusher/codeigniter4-auth
 */

namespace App\Controllers;

use App\Models\UserModel;
use Config\Services;

class AuthController extends BaseController
{

    public function __construct() {
        helper(['text', 'mail']);
    }

    public function register() {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'username' => 'required|min_length[2]|max_length[64]|is_unique[users.username]|alpha_dash',
                'email' => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[8]|max_length[64]',
                'password_confirm' => 'matches[password]'
            ];
            $this->userModel->setValidationRules($rules);
            $newUser = [
                'username' => mb_strtolower($this->request->getPost('username')),
                'email' => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'password_confirm' => $this->request->getPost('password_confirm'),
                'image' => 'default.png',
                'role' => 1,
                'registered_on' => date('Y-m-d H:i:s', time()),
                'auth_code' => random_string('alnum', 16)
            ];

            if (!$this->userModel->save($newUser)) {
                return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
            }

            send_mail($newUser['email'], lang('Account.registration'), 'activation', ['hash' => $newUser['auth_code']]);
            return redirect()->to('/login')->with('success', lang('Account.registrationSuccess'));
        }
        if ($this->session->get('userData.id')) {
            return redirect()->to('/account');
        }
        
        return view('auth/register');
    }

    public function activateAccount() {
        $user = $this->userModel->where('auth_code', $this->request->getGet('token'))
                                ->where('email_confirmed', false)->first();

        if (is_null($user)) {
            return redirect()->to('/login')->with('error', lang('Account.activationNoUser'));
        }
        $this->userModel->update($user['id'], ['email_confirmed' => true, 'auth_code' => null]);

        return redirect()->to('/login')->with('success', lang('Account.activationSuccess'));
    }

    public function login() {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|min_length[8]|max_length[64]',
            ];
            if (!$this->validate($rules)) {
                return redirect()->to('/login')->withInput()->with('errors', $this->validator->getErrors());
            }

            $user = $this->userModel->where('email', $this->request->getPost('email'))->first();
            if (is_null($user) || !password_verify($this->request->getPost('password'), $user['password_hash'])) {
                return redirect()->to('/login')->withInput()->with('error', lang('Account.wrongCredentials'));
            }
            
            if (!$user['email_confirmed']) {
                return redirect()->to('/login')->withInput()->with('error', lang('Account.notActivated'));
            }

            $this->session->set('userData', [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'image' => $user['image'],
                'role' => $user['role'],
                'show_save_on_home' => $user['show_save_on_home']
            ]);

            return redirect()->to('/account');
        }

        if ($this->session->get('userData.id')) {
            return redirect()->to('/account');
        }
        
        return view('auth/login');
    }

    public function logout() {
        $this->session->remove('userData');
        
        return redirect()->to('/login');
    }
    
    public function forgotPassword() {
        if ($this->request->getMethod() === 'post') {
            if (!$this->validate(['email' => 'required|valid_email'])) {
                return redirect()->back()->with('error', lang('Account.wrongEmail'));
            }

            $user = $this->userModel->where('email', $this->request->getPost('email'))->first();
            if (!$user) {
                return redirect()->back()->with('error', lang('Account.wrongEmail'));
            }

            if (!empty($user['code_expires']) && strtotime($user['code_expires']) >= time()) {
                return redirect()->back()->with('error', lang('Account.emailAlreadySent'));
            }

            $reset_code = random_string('alnum', 16);
            $this->userModel->update($user['id'], [
                'auth_code' => $reset_code, 
                'code_expires' => date('Y-m-d H:i:s', time() + HOUR)
            ]);
            send_mail($this->request->getPost('email'), lang('Account.passwordResetRequest'), 'reset', ['hash' => $reset_code]);
            return redirect()->back()->with('success', lang('Account.forgottenPasswordEmail'));
        }
        if ($this->session->get('userData.id')) {
            return redirect()->to('/account');
        }
        
        return view('auth/forgot_password');
    }

    public function resetPassword() {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'token'	=> 'required',
                'password' => 'required|min_length[8]|max_length[64]',
                'password_confirm' => 'matches[password]'
            ];
            if (!$this->validate($rules)) {
                return redirect()->back()->with('error', lang('Account.passwordMismatch'));
            }

            $user = $this->userModel->where('auth_code', $this->request->getPost('token'))
                                    ->where('code_expires >', time())
                                    ->first();

            if (!$user) {
                return redirect()->to('/login')->with('error', lang('Account.invalidRequest'));
            }

            $this->userModel->update($user['id'], [
                'password' => $this->request->getPost('password'), 
                'auth_code' => null, 
                'code_expires' => null
            ]);

            return redirect()->to('/login')->with('success', lang('Account.passwordUpdateSuccess'));
        }

        $user = $this->userModel->where('auth_code', $this->request->getGet('token'))
                                ->where('code_expires >', time())
                                ->first();

        if (!$user) {
            return redirect()->to('/login')->with('error', lang('Account.invalidRequest'));
        }

        return view('auth/reset_password', ['token' => $this->request->getGet('token')]);
    }

}
