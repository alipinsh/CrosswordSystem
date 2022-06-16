<?php
/*
 * Kontrollera klase, kura nodarbojas ar moderāciju ur problēmu paziņošanu.
 */

namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\CrosswordModel;
use App\Models\CrosswordReportModel;
use App\Models\CommentReportModel;
use App\Models\UserModel;

class ModerationController extends BaseController
{
    protected $crosswordReportModel;

    protected $commentReportModel;

    protected $commentModel;

    public function __construct() {
        $this->crosswordReportModel = new CrosswordReportModel();
        $this->commentReportModel = new CommentReportModel();
        $this->commentModel = new CommentModel();
        helper(['text', 'mail']);
    }

    public function viewReportsCrosswords()
    {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') < UserModel::BIG_MOD_ROLE) {
            return view('not_found');
        }

        $reportList = $this->crosswordReportModel->getReportList();
        $groupedReports = [];
        foreach ($reportList as $reportItem) {
            $groupedReports[$reportItem['crossword_id']]['title'] = $reportItem['title'];
            $groupedReports[$reportItem['crossword_id']]['reports'][] = $reportItem['report'];
        }

        return view('crossword_moderation', ['groupedReports' => $groupedReports]);
    }

    public function actionCrossword() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::BIG_MOD_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $crosswordId = $this->request->getPost('crossword_id');
        $crossword = $this->crosswordModel->find($crosswordId);
        if (!$crossword || !$crossword['is_public']) {
            return $this->response->setJSON(['error' => 'no such crossword!']);
        }

        $reasonText = $this->request->getPost('reason_text');
        $reasonText = clean_text($reasonText);

        if (!mb_strlen($reasonText)) {
            return $this->response->setJSON(['error' => 'text can not be blank']);
        }

        $user = $this->userModel->find($crossword['user_id']);
        $userEmail = $user['email'];
        $crosswordName = $crossword['title'];

        $actionType = $this->request->getPost('moderation_action');
        switch ($actionType) {
            case 'hide':
                $crossword['is_public'] = false;
                $this->crosswordModel->save($crossword);
                send_mail($userEmail, lang('Moderation.hideNotice'), 'email/hide_reason', ['crosswordName' => $crosswordName, 'reason' => $reasonText]);
                return $this->response->setJSON(['success' => 'crossword hidden']);
            case 'delete':
                $this->crosswordModel->deleteById($crosswordId);
                send_mail($userEmail, lang('Moderation.deleteNotice'), 'email/delete_reason', ['crosswordName' => $crosswordName, 'reason' => $reasonText]);
                return $this->response->setJSON(['success' => 'crossword deleted']);
            default:
                break;
        }

        return $this->response->setJSON(['error' => 'no action defined']);
    }

    public function freeCrossword() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::BIG_MOD_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $crosswordId = $this->request->getPost('crossword_id');
        $crossword = $this->crosswordModel->find($crosswordId);
        if (!$crossword) {
            return $this->response->setJSON(['error' => 'no such crossword!']);
        }

        $this->crosswordReportModel->deleteReportsFor($crosswordId);
        return $this->response->setJSON(['success' => 'freed from reports']);
    }

    public function sendReportCrossword() {
        $reportText = $this->request->getPost('report_text');
        $reportText = clean_text($reportText);

        if (!mb_strlen($reportText)) {
            return $this->response->setJSON(['error' => 'text can not be blank']);
        }

        $crossword = $this->crosswordModel->find($this->request->getPost('crossword_id'));
        if (!$crossword || !$crossword['is_public']) {
            return $this->response->setJSON(['error' => 'no such crossword!']);
        }

        $comment = [
            'crossword_id' => $this->request->getPost('crossword_id'),
            'report' => $reportText
        ];

        $this->crosswordReportModel->save($comment);

        return $this->response->setJSON(['success' => 'reported!']);
    }

    public function viewReportsComments()
    {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') < UserModel::BIG_MOD_ROLE) {
            return view('not_found');
        }

        $reportList = $this->commentReportModel->getReportList();

        return view('comment_moderation', ['reports' => $reportList]);
    }

    public function sendReportComment() {
        $comment = $this->commentModel->find($this->request->getPost('comment_id'));
        if (!$comment) {
            return $this->response->setJSON(['error' => 'no such comment!']);
        }

        $comment = [
            'comment_id' => $this->request->getPost('comment_id')
        ];

        $this->commentReportModel->save($comment);

        return $this->response->setJSON(['success' => 'reported!']);
    }

    public function actionComment() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $commentId = $this->request->getPost('comment_id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->response->setJSON(['error' => 'no such comment!']);
        }

        $user = $this->userModel->find($comment['user_id']);

        $this->commentModel->deleteById($commentId);
        return $this->response->setJSON(['success' => 'comment deleted']);
    }

    public function freeComment() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $commentId = $this->request->getPost('commentId');
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->response->setJSON(['error' => 'no such comment!']);
        }

        $this->commentReportModel->deleteReportsFor($commentId);
        return $this->response->setJSON(['success' => 'freed from reports']);
    }

    public function viewUsers() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') < UserModel::ADMIN_ROLE) {
            return view('not_found');
        }

        $users = $this->userModel->findAll();

        return view('user_moderation', ['users' => $users]);
    }

    public function switchRoleUser() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::ADMIN_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $userId = $this->request->getPost('user_id');
        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON(['error' => 'no such user!']);
        }

        $roleId = $this->request->getPost('role_id');
        if (!is_int($roleId) && !($roleId > 0 && $roleId <= UserModel::ADMIN_ROLE)) {
            return $this->response->setJSON(['error' => 'no such role']);
        }

        $user['role_id'] = $roleId;
        $this->userModel->save($user);

        return $this->response->setJSON(['success' => UserModel::ROLE_NAMES[$roleId]]);
    }

    public function deleteUser() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::ADMIN_ROLE) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $userId = $this->request->getPost('user_id');
        $this->userModel->delete($userId);

        return $this->response->setJSON(['success' => 'deleted user']);
    }
}
