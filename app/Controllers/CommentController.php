<?php
/*
 * Kontrollera klase, kurš atbild par darbības ar komentāriem.
 */

namespace App\Controllers;

use Config\Services;
use App\Models\CommentModel;

class CommentController extends BaseController {
    private const ITEMS_PER_PAGE = 50;

    protected $commentModel;

    public function __construct() {
        $this->commentModel = new CommentModel();
        helper(['text']);
    }

    public function post() {
        if (!$this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => lang('Account.userUnauthorized')]);
        }

        if (!$this->crosswordModel->where('is_public', true)->find($this->request->getPost('crossword_id'))) {
            return $this->response->setJSON(['error' => lang('Crossword.notAvailable')]);
        }

        $commentText = $this->request->getPost('comment_text');

        if (strlen($commentText) > 65535) {
            return $this->response->setJSON(['error' => lang('Crossword.textTooLong')]);
        }

        $commentText = clean_text($commentText);

        if (!mb_strlen($commentText)) {
            return $this->response->setJSON(['error' => lang('Crossword.textCantBeBlank')]);
        }

        $comment = [
            'user_id' => $this->session->get('userData.id'),
            'crossword_id' => $this->request->getPost('crossword_id'),
            'text' => $commentText
        ];
        if (!$this->commentModel->save($comment)) {
            return $this->response->setJSON(['error' => $this->commentModel->errors()]);
        }

        $comment = $this->commentModel->find($this->commentModel->getInsertID());

        return $this->response->setJSON([
            'id' => $this->commentModel->getInsertID(),
            'user_id' => $this->session->get('userData.id'),
            'image' => $this->session->get('userData.image'),
            'username' => $this->session->get('userData.username'),
            'text' => $comment['text'],
            'posted_at' => $comment['posted_at'],
            'edited_at' => $comment['edited_at'],
            'editable' => true
        ]);
    }

    public function get() {
        $cid = $this->request->getGet('cid');
        if (!$this->crosswordModel->where('is_public', true)->find($cid)) {
            return $this->response->setJSON(['error' => lang('Crossword.notAvailable')]);
        }

        $itemsCount = $this->commentModel->getCommentsForCount($cid);
        $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
        $currentPage = intval($this->request->getGet('p'));
        if ($currentPage > $pages) {
            $currentPage = $pages;
        } else if ($currentPage < 1) {
            $currentPage = 1;
        }

        $comments = $this->commentModel->getCommentsFor($cid, self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1));
        $userId = $this->session->get('userData.id');
        foreach ($comments as $key => $comment) {
            if ($comment['user_id'] == $userId) {
                $comments[$key]['editable'] = true;
            }
        }
        return $this->response->setJSON(['comments' => $comments, 'totalPages' => $pages]);
    }

    public function delete() {
        $commentId = $this->request->getPost('id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => lang('Crossword.commentUnavailable')]);
        }
        $crossword = $this->crosswordModel->find($comment['crossword_id']);
        if ($crossword['is_public']) {
            $this->commentModel->delete($commentId);
            return $this->response->setJSON(['deleted_id' => $commentId]);
        }
        return $this->response->setJSON(['error' => lang('Crossword.commentUnavailable')]);
    }

    public function edit() {
        $commentId = $this->request->getPost('id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => lang('Crossword.commentUnavailable')]);
        }

        if (!$this->crosswordModel->where('is_public', true)->find($comment['crossword_id'])) {
            return $this->response->setJSON(['error' => lang('Crossword.notAvailable')]);
        }

        $commentText = $this->request->getPost('edited_text');

        if (strlen($commentText) > 65535) {
            return $this->response->setJSON(['error' => lang('Crossword.textTooLong')]);
        }

        $commentText = clean_text($commentText);

        if (!mb_strlen($commentText)) {
            return $this->response->setJSON(['error' => lang('Crossword.textCantBeBlank')]);
        }

        $comment['text'] = $commentText;
        if (!$this->commentModel->save($comment)) {
            return $this->response->setJSON(['error' => $this->commentModel->errors()]);
        }

        return $this->response->setJSON($comment);
    }
}
