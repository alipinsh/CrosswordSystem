<?php
/*
 * Kontrollera klase, kura nodarbojas ar moderāciju ur problēmu paziņošanu.
 */

namespace App\Controllers;

use App\Models\CrosswordModel;
use App\Models\ReportModel;

class ModerationController extends BaseController
{
    protected $reportModel;

    public function __construct() {
        $this->reportModel = new ReportModel();
        helper(['text', 'mail']);
    }

    public function viewReportsCrosswords()
    {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($this->session->get('userData.role') != 2) {
            return view('not_found');
        }

        $reportList = $this->reportModel->getReportList();
        $groupedReports = [];
        foreach ($reportList as $reportItem) {
            $groupedReports[$reportItem['crossword_id']]['title'] = $reportItem['title'];
            $groupedReports[$reportItem['crossword_id']]['reports'][] = $reportItem['report'];
        }

        return view('moderation', ['groupedReports' => $groupedReports]);
    }

    public function actionCrossword() {
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') != 2) {
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
        if (!$this->session->get('userData.id') || $this->session->get('userData.role') != 2) {
            return $this->response->setJSON(['error' => 'not a mod!']);
        }

        $crosswordId = $this->request->getPost('crossword_id');
        $crossword = $this->crosswordModel->find($crosswordId);
        if (!$crossword) {
            return $this->response->setJSON(['error' => 'no such crossword!']);
        }

        $this->reportModel->deleteReportsFor($crosswordId);
        return $this->response->setJSON(['success' => 'freed from reports']);
    }

    public function sendReportCrossrod() {
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

        $this->reportModel->save($comment);

        return $this->response->setJSON(['success' => 'reported!']);
    }
}
