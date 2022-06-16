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
            return $this->response->setJSON(['error' => 'please log in']);
        }

        if (!$this->crosswordModel->where('is_public', true)->find($this->request->getPost('crossword_id'))) {
            return $this->response->setJSON(['error' => 'no such crossword']);
        }

        $commentText = $this->request->getPost('comment_text');

        if (mb_strlen($commentText) > 65535) {
            return $this->response->setJSON(['error' => 'comment too long']);
        }

        $commentText = clean_text($commentText);

        if (!mb_strlen($commentText)) {
            return $this->response->setJSON(['error' => 'text can not be blank']);
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
        if ($cid) {
            $itemsCount = $this->commentModel->getCommentsForCount($cid);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
            if ($currentPage > $pages) {
                $currentPage = $pages;
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
        return null;
    }

    public function delete() {
        $commentId = $this->request->getPost('id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => 'comment unavailable']);
        }
        $crossword = $this->crosswordModel->find($comment['crossword_id']);
        if ($crossword['is_public']) {
            $this->commentModel->delete($commentId);
            return $this->response->setJSON(['deleted_id' => $commentId]);
        }
        return $this->response->setJSON(['error' => 'comment unavailable']);
    }

    public function edit() {
        $commentId = $this->request->getPost('id');
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => 'comment unavailable']);
        }

        if (!$this->crosswordModel->where('is_public', true)->find($comment['crossword_id'])) {
            return $this->response->setJSON(['error' => 'no such crossword']);
        }

        $commentText = $this->request->getPost('edited_text');

        if (mb_strlen($commentText) > 65535) {
            return $this->response->setJSON(['error' => 'comment too long']);
        }

        $commentText = clean_text($commentText);

        if (!mb_strlen($commentText)) {
            return $this->response->setJSON(['error' => 'text can not be blank']);
        }

        $comment['text'] = $commentText;
        if (!$this->commentModel->save($comment)) {
            return $this->response->setJSON(['error' => $this->commentModel->errors()]);
        }

        return $this->response->setJSON($comment);
    }
}
