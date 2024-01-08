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

    public function moderationPage() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return view('not_found');
        }

        return view('moderation', ['role' => $this->session->get('userData.role')]);
    }

    public function viewReportsCrossword()
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
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $crosswordId = $this->request->getPost('crossword_id');
        $crossword = $this->crosswordModel->find($crosswordId);
        if (!$crossword || !$crossword['is_public']) {
            return $this->response->setJSON(['error' => lang('Crossword.notAvailable')]);
        }

        $reasonText = $this->request->getPost('reason_text');
        $reasonText = clean_text($reasonText);

        if (strlen($reasonText) > 65535) {
            return $this->response->setJSON(['error' => lang('Crossword.textTooLong')]);
        }

        if (!mb_strlen($reasonText)) {
            return $this->response->setJSON(['error' => lang('Crossword.textCantBeBlank')]);
        }

        $user = $this->userModel->find($crossword['user_id']);
        $userEmail = $user['email'];
        $crosswordName = $crossword['title'];

        $actionType = $this->request->getPost('moderation_action');
        switch ($actionType) {
            case 'hide':
                $crossword['is_public'] = false;
                $this->crosswordModel->save($crossword);
                send_mail($userEmail, lang('Moderation.hideNotice'), 'hide_reason', ['crosswordName' => $crosswordName, 'reason' => $reasonText]);
                return $this->response->setJSON(['success' => lang('Moderation.hidden')]);
            case 'delete':
                $this->crosswordModel->deleteById($crosswordId);
                send_mail($userEmail, lang('Moderation.deleteNotice'), 'delete_reason', ['crosswordName' => $crosswordName, 'reason' => $reasonText]);
                return $this->response->setJSON(['success' => lang('Moderation.deleted')]);
            default:
                break;
        }

        return $this->response->setJSON(['error' => lang('Moderation.noAction')]);
    }

    public function freeCrossword() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::BIG_MOD_ROLE) {
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $crosswordId = $this->request->getPost('crossword_id');
        $crossword = $this->crosswordModel->find($crosswordId);
        if (!$crossword) {
            return $this->response->setJSON(['error' => lang('Crossword.doesNotExist')]);
        }

        $this->crosswordReportModel->deleteReportsFor($crosswordId);
        return $this->response->setJSON(['success' => lang('Moderation.freed')]);
    }

    public function sendReportCrossword() {
        $reportText = $this->request->getPost('report_text');
        $reportText = clean_text($reportText);

        if (strlen($reportText) > 65535) {
            return $this->response->setJSON(['error' => lang('Crossword.textTooLong')]);
        }

        if (!mb_strlen($reportText)) {
            return $this->response->setJSON(['error' => lang('Crossword.textCantBeBlank')]);
        }

        $crossword = $this->crosswordModel->find($this->request->getPost('crossword_id'));
        if (!$crossword || !$crossword['is_public']) {
            return $this->response->setJSON(['error' => lang('Crossword.notAvailable')]);
        }

        $comment = [
            'crossword_id' => $this->request->getPost('crossword_id'),
            'report' => $reportText
        ];

        $this->crosswordReportModel->save($comment);

        return $this->response->setJSON(['success' => lang('Moderation.reported')]);
    }

    public function viewReportsComment()
    {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return view('not_found');
        }

        $reportList = $this->commentReportModel->getReportList();

        return view('comment_moderation', ['reports' => $reportList]);
    }

    public function sendReportComment() {
        $comment = $this->commentModel->find($this->request->getPost('comment_id'));
        if (!$comment) {
            return $this->response->setJSON(['error' => lang('Crossword.commentDoesntExist')]);
        }

        $comment = [
            'comment_id' => $this->request->getPost('comment_id')
        ];

        $this->commentReportModel->save($comment);

        return $this->response->setJSON(['success' => lang('Moderation.reported')]);
    }

    public function actionComment() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $commentId = $this->request->getPost('comment_id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->response->setJSON(['error' => lang('Crossword.commentDoesntExist')]);
        }

        $this->commentModel->delete($commentId);
        return $this->response->setJSON(['success' => lang('Moderation.deleted')]);
    }

    public function freeComment() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::SMALL_MOD_ROLE) {
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $commentId = $this->request->getPost('comment_id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment) {
            return $this->response->setJSON(['error' => lang('Crossword.commentDoesntExist')]);
        }

        $this->commentReportModel->deleteReportsFor($commentId);
        return $this->response->setJSON(['success' => lang('Moderation.freed')]);
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
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $userId = $this->request->getPost('user_id');
        $user = $this->userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON(['error' => lang('Moderation.userDoesntExist')]);
        }

        $roleId = $this->request->getPost('role_id');
        if (!is_int($roleId) && !($roleId > UserModel::GUEST_ROLE && $roleId <= UserModel::ADMIN_ROLE)) {
            return $this->response->setJSON(['error' => lang('Moderation.noRole')]);
        }

        $user['role_id'] = $roleId;
        $this->userModel->save($user);

        return $this->response->setJSON(['success' => UserModel::ROLE_NAMES[$roleId]]);
    }

    public function deleteUser() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') < UserModel::ADMIN_ROLE) {
            return $this->response->setJSON(['error' => lang('Moderation.noRights')]);
        }

        $userId = $this->request->getPost('user_id');
        $this->userModel->delete($userId);

        if ($userId == $this->session->get('userData.id')) {
            return redirect()->to('/logout');
        }

        return $this->response->setJSON(['success' => lang('Moderation.deletedUser')]);
    }
}
