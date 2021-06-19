<?php
/*
 * Kontrollera klase, kura nodarbojas ar krustvārdu mīklu ņemšanu, veidošanu, rediģēšanu, dzēšanu.
 */

namespace App\Controllers;

use App\Models\ReportModel;
use Config\Services;
use App\Models\CrosswordModel;
use App\Models\UserModel;
use App\Models\CommentModel;
use App\Models\TagModel;
use App\Models\SaveModel;

class CrosswordController extends BaseController {
    private const ITEMS_PER_PAGE = 50;

    protected $tagModel;
    protected $reportModel;
    protected $saveModel;

    public function __construct() {
        $this->tagModel = new TagModel();
        $this->reportModel = new ReportModel();
        $this->saveModel = new SaveModel();
    }

    public function view($id = null) {
        if ($id == null) {
            return redirect()->to('/crosswords');
        }

        $crossword = $this->crosswordModel->find($id);
        if (!$crossword) {
            return redirect()->to('/crosswords');
        }

        $currentUserId = $this->session->get('userData.id');
        $isMine = $crossword['user_id'] == $currentUserId;
        if (!$crossword['is_public'] && $isMine) {
            return redirect()->to('/crossword/edit/' . $crossword['id']);
        } else if (!$crossword['is_public']) {
            return view('not_found');
        }

        $favorited = false;
        $saveData = null;
        if ($currentUserId) {
            $favorited = $this->crosswordModel->checkIfUserFavorited($currentUserId, $id);
            $save = $this->saveModel->where('user_id', $currentUserId)->where('crossword_id', $crossword['id'])->first();
            if ($save) {
                $saveData = json_decode($save['save_data'], true);
                if ($save['needs_update']) {
                    $this->saveModel->validateSaveData($saveData, $crossword['data']);
                }
            }
        }

        $isModerator = $this->session->get('userData.role') == 2;
        $hasReports = $isModerator ? boolval(count($this->reportModel->getReportList())) : false;

        $crossword['user'] = $this->userModel->find($crossword['user_id'])['username'];

		return view('crossword/page', [
                'crossword' => $crossword,
                'favorited' => $favorited,
                'isMine' => $isMine,
                'crosswordSaveData' => $saveData,
                'isModerator' => $isModerator,
                'hasReports' => $hasReports
            ]
        );
    }

    public function edit($id = null) {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($id) {
            $crossword = $this->crosswordModel->find($id);
            if ($crossword && $crossword['user_id'] == $this->session->get('userData.id')) {
                return view('crossword/editor', ['crossword' => $crossword]);
            }
            return redirect()->to('/crossword/edit');
        }
        return view('crossword/editor');
    }

    public function save() {
        if (!$this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => 'please log in']);
        }

        $crosswordData = json_decode($this->request->getPost('crossword_data'), true);
        if (!$this->crosswordModel->validateCrosswordData($crosswordData)) {
            return $this->response->setJSON(['error' => 'problem with crossword data']);
        }

        $title = trim($this->request->getPost('title'));
        $title = preg_replace('/  +/', ' ', $title);
        $title = preg_replace('/(?:\r?\n|\r){2,}/', ' ', $title);
        $title = preg_replace('/[ \t]+/', ' ', $title);
        $title = htmlspecialchars($title);

        $tags = json_decode(mb_strtolower($this->request->getPost('tags')), true);

        if (!$this->tagModel->validateTags($tags)) {
            return $this->response->setJSON(['error' => 'problem with tags data']);
        }

        $tagsText = implode(',', $tags);
        if (strlen($tagsText) > 65535) {
            return $this->response->setJSON(['error' => 'way too many tags']);
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
            'is_public' => 'required'
        ];
        $this->crosswordModel->setValidationRules($rules);
        $userId = intval($this->session->get('userData.id'));
        $currentTime = date('Y-m-d H:i:s', time());
        $crossword = [
            'title' => $title,
            'width' => $crosswordData['size'][CrosswordModel::WIDTH],
            'height' => $crosswordData['size'][CrosswordModel::HEIGHT],
            'questions' => sizeof($crosswordData['questions'][CrosswordModel::HORIZONTAL]) +
                           sizeof($crosswordData['questions'][CrosswordModel::VERTICAL]),
            'data' => json_encode($crosswordData),
            'user_id' => $userId,
            'is_public' => $this->request->getPost('is_public') ? 1 : 0,
            'tags' => $tagsText
        ];

        $crosswordId = $this->request->getPost('id');
        if ($crosswordId) {
            $oldCrossword = $this->crosswordModel->find($crosswordId);
            if ($oldCrossword && $oldCrossword['user_id'] == $userId) {
                $crossword['id'] = $crosswordId;

                if (is_null($oldCrossword['published_at']) && $crossword['is_public']) {
                    $crossword['published_at'] = $currentTime;
                } else {
                    $crossword['updated_at'] = $currentTime;
                }

                $needsUpdate = false;
                $oldCrosswordData = json_decode($oldCrossword['data'], true);
                $newCrosswordData = json_decode($crossword['data'], true);
                $oldCrosswordQuestions = $oldCrosswordData['questions'];
                $newCrosswordQuestions = $newCrosswordData['questions'];

                if (array_diff_key($oldCrosswordQuestions[CrosswordModel::HORIZONTAL], $newCrosswordQuestions[CrosswordModel::HORIZONTAL])
                    || array_diff_key($oldCrosswordQuestions[CrosswordModel::VERTICAL], $newCrosswordQuestions[CrosswordModel::VERTICAL])
                    || array_diff_key($newCrosswordQuestions[CrosswordModel::HORIZONTAL], $oldCrosswordQuestions[CrosswordModel::HORIZONTAL])
                    || array_diff_key($newCrosswordQuestions[CrosswordModel::VERTICAL], $oldCrosswordQuestions[CrosswordModel::VERTICAL])) {
                    $needsUpdate = true;
                } else {
                    foreach ($oldCrosswordQuestions[CrosswordModel::HORIZONTAL] as $oldKey => $oldValue) {
                        if ($oldValue[CrosswordModel::ANSWER] != $newCrosswordQuestions[CrosswordModel::HORIZONTAL][$oldKey][CrosswordModel::ANSWER]) {
                            $needsUpdate = true;
                            break;
                        }
                    }
                    if (!$needsUpdate) {
                        foreach ($oldCrosswordQuestions[CrosswordModel::VERTICAL] as $oldKey => $oldValue) {
                            if ($oldValue[CrosswordModel::ANSWER] != $newCrosswordQuestions[CrosswordModel::VERTICAL][$oldKey][CrosswordModel::ANSWER]) {
                                $needsUpdate = true;
                                break;
                            }
                        }
                    }
                }

                if ($needsUpdate) {
                    $this->saveModel->setNeedsUpdateFor($crosswordId);
                }
            }
        } else {
            if ($crossword['is_public']) {
                $crossword['published_at'] = $currentTime;
            }
        }

        if (!$this->crosswordModel->save($crossword)) {
            return $this->response->setJSON(['error' => $this->crosswordModel->getErrors()]);
        }
        $crosswordId = $crosswordId ? $crosswordId : $this->crosswordModel->getInsertID();
        $this->tagModel->updateTags($crosswordId);
        $this->userModel->updateCreatedCount($userId);

        return $this->response->setJSON(['crossword_id' => $crosswordId]);
    }

    public function delete($id = null) {
        if (!$this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => 'please log in']);
        }
        if ($id) {
            $crossword = $this->crosswordModel->find($id);
            if ($crossword && $crossword['user_id'] == $this->session->get('userData.id')) {
                $usersFavorited = $this->crosswordModel->getUsersFavorited($id);
                $this->crosswordModel->deleteById($id);
                $this->userModel->updateCreatedCount($this->session->get('userData.id'));
                $this->userModel->updateFavoritedCountMultiple($usersFavorited);
                return $this->response->setJSON(['success' => 'deleted']);
            }
            return $this->response->setJSON(['error' => 'no such crossword for current user']);
        }
        return $this->response->setJSON(['error' => 'no id specified']);

    }

    public function listCreated($username = null) {
        $userId = $this->userModel->getIdByUsername($username);
        if ($userId) {
            $itemsCount = $this->crosswordModel->getCrosswordListByUserCount($userId, $this->crosswordModel::CREATED);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
            if ($currentPage > $pages) {
                $currentPage = $pages;
            }

            return view('crossword/list',
                [
                    'crosswords' => $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::CREATED,
                        self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
                    'listTitle' => lang('Crossword.listByUserCreated', [$username]),
                    'pages' => $pages,
                    'currentPage' => $currentPage
                ]
            );
        }

        return view('not_found');
    }

    public function listFavorited($username = null) {
        $userId = $this->userModel->getIdByUsername($username);
        if ($userId) {
            $itemsCount = $this->crosswordModel->getCrosswordListByUserCount($userId, $this->crosswordModel::FAVORITED);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
            if ($currentPage > $pages) {
                $currentPage = $pages;
            }

            return view('crossword/list',
                [
                    'crosswords' => $this->crosswordModel->getCrosswordListByUser($userId, $this->crosswordModel::FAVORITED,
                        self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
                    'listTitle' => lang('Crossword.listByUserFavorited', [$username]),
                    'pages' => $pages,
                    'currentPage' => $currentPage
                ]
            );
        }

        return view('not_found');
    }

    public function listByTag($tag = null) {
        if ($this->tagModel->checkIfTagExists($tag)) {
            $itemsCount = $this->crosswordModel->getCrosswordListByTagCount($tag);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
            if ($currentPage > $pages) {
                $currentPage = $pages;
            }

            return view('crossword/list',
                [
                    'crosswords' => $this->crosswordModel->getCrosswordListByTag($tag, self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
                    'listTitle' => lang('Crossword.listByTag', [$tag]),
                    'pages' => $pages,
                    'currentPage' => $currentPage
                ]
            );
        }

        return view('not_found');
    }

    public function search($searchQuery = null) {
        $searchQuery = trim($searchQuery);
        $searchQuery = preg_replace('/  +/', ' ', $searchQuery);
        $searchQuery = preg_replace('/(?:\r?\n|\r){2,}/', "\n", $searchQuery);
        $searchQuery = preg_replace('/[ \t]+/', ' ', $searchQuery);
        $searchQuery = htmlspecialchars($searchQuery);

        if (!strlen($searchQuery)) {
            return view('not_found');
        }

        $itemsCount = $this->crosswordModel->getCrosswordListBySearchQueryCount($searchQuery);
        $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
        $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
        if ($currentPage > $pages) {
            $currentPage = $pages;
        }

        return view('crossword/list',
            [
                'crosswords' => $this->crosswordModel->getCrosswordListBySearchQuery($searchQuery, self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
                'listTitle' => lang('Crossword.searchResults', [$searchQuery]),
                'pages' => $pages,
                'currentPage' => $currentPage
            ]
        );
    }

    public function listPrivates() {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        $userId = $this->session->get('userData.id');

        $itemsCount = $this->crosswordModel->getCrosswordListPrivatesCount($userId);
        $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
        $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
        if ($currentPage > $pages) {
            $currentPage = $pages;
        }

        return view('crossword/list',
            [
                'crosswords' => $this->crosswordModel->getCrosswordListPrivates($userId, self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
                'listTitle' => lang('Crossword.listPrivates'),
                'pages' => $pages,
                'currentPage' => $currentPage
            ]
        );
    }

    public function listAll() {
        $itemsCount = $this->crosswordModel->getCrosswordListCount();
        $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
        $currentPage = is_numeric($this->request->getGet('p')) ? floor($this->request->getGet('p')) : 1;
        if ($currentPage > $pages) {
            $currentPage = $pages;
        }

        return view('crossword/list', [
            'crosswords' => $this->crosswordModel->getCrosswordList(self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
            'listTitle' => lang('Crossword.allCrosswords'),
            'pages' => $pages,
            'currentPage' => $currentPage
        ]);
    }
}