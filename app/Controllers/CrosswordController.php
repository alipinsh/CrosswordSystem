<?php
/*
 * Kontrollera klase, kura nodarbojas ar krustvārdu mīklu ņemšanu, veidošanu, rediģēšanu, dzēšanu.
 */

namespace App\Controllers;

use App\Models\CrosswordReportModel;
use Config\Services;
use App\Models\CrosswordModel;
use App\Models\UserModel;
use App\Models\CommentModel;
use App\Models\TagModel;
use App\Models\SaveModel;

class CrosswordController extends BaseController {
    private const ITEMS_PER_PAGE = 50;

    protected $tagModel;
    protected $crosswordReportModel;
    protected $saveModel;

    public function __construct() {
        $this->tagModel = new TagModel();
        $this->crosswordReportModel = new CrosswordReportModel();
        $this->saveModel = new SaveModel();
        helper(['text']);
    }

    public function view($id = 0) {
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
                    $this->saveModel->cleanSaveData($saveData, $crossword['data'], $crossword['language']);
                }
            }
        }

        $isModerator = $this->session->get('userData.role') >= UserModel::BIG_MOD_ROLE;
        $hasReports = $isModerator ? boolval(count($this->crosswordReportModel->getReportList())) : false;

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

    public function edit($id = 0) {
        if (!$this->session->get('userData.id')) {
            return redirect()->to('/login');
        }
        if ($id) {
            $crossword = $this->crosswordModel->find($id);
            if ($crossword && $crossword['user_id'] == $this->session->get('userData.id')) {
                return view('crossword/editor', ['crossword' => $crossword]);
            }
        }
        return view('crossword/editor');
    }

    public function save() {
        if (!$this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => lang('Account.userUnauthorized')]);
        }

        $language = $this->request->getPost('language');
        if (!in_array($language, ['en', 'ru', 'lv'])) {
            return $this->response->setJSON(['error' => lang('Crossword.invalidLanguage')]);
        }

        $crosswordData = json_decode($this->request->getPost('crossword_data'), true);
        if (!$this->crosswordModel->validateCrosswordData($crosswordData, $language)) {
            return $this->response->setJSON(['error' => lang('Crossword.crosswordDataError')]);
        }

        $title = trim($this->request->getPost('title'));
        $title = clean_text($title);

        $tags = json_decode(mb_strtolower($this->request->getPost('tags')), true);

        if (!$tags) {
            $tags = [];
        }

        if (!$this->tagModel->validateTags($tags)) {
            return $this->response->setJSON(['error' => lang('Crossword.tagsDataError')]);
        }

        $tagsText = implode(',', $tags);
        if (strlen($tagsText) > 65535) {
            return $this->response->setJSON(['error' => lang('Crossword.tooManyTags')]);
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
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
            'tags' => $tagsText,
            'language' => $language
        ];

        $crosswordId = $this->request->getPost('id');
        if ($crosswordId) {
            $oldCrossword = $this->crosswordModel->find($crosswordId);
            if ($oldCrossword && $oldCrossword['user_id'] == $userId) {
                $crossword['id'] = $crosswordId;

                if (!is_null($oldCrossword['published_at'])) {
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
                        if (isset($newCrosswordQuestions[CrosswordModel::HORIZONTAL][$oldKey]) &&
                            $oldValue[CrosswordModel::ANSWER] != $newCrosswordQuestions[CrosswordModel::HORIZONTAL][$oldKey][CrosswordModel::ANSWER]) {
                            $needsUpdate = true;
                            break;
                        }
                    }
                    if (!$needsUpdate) {
                        foreach ($oldCrosswordQuestions[CrosswordModel::VERTICAL] as $oldKey => $oldValue) {
                            if (isset($newCrosswordQuestions[CrosswordModel::VERTICAL][$oldKey]) &&
                                $oldValue[CrosswordModel::ANSWER] != $newCrosswordQuestions[CrosswordModel::VERTICAL][$oldKey][CrosswordModel::ANSWER]) {
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
        }

        if ($crossword['is_public'] && is_null($crossword['published_at'])) {
            $crossword['published_at'] = $currentTime;
        }

        if (!$this->crosswordModel->save($crossword)) {
            return $this->response->setJSON(['error' => $this->crosswordModel->getErrors()]);
        }
        $crosswordId = $crosswordId ?: $this->crosswordModel->getInsertID();
        $this->tagModel->updateTags($crosswordId);
        $this->userModel->updateCreatedCount($userId);

        return $this->response->setJSON(['crossword_id' => $crosswordId]);
    }

    public function delete($id = 0) {
        if (!$this->session->get('userData.id')) {
            return $this->response->setJSON(['error' => lang('Account.userUnauthorized')]);
        }

        $crossword = $this->crosswordModel->find($id);
        if ($crossword && $crossword['user_id'] == $this->session->get('userData.id')) {
            $usersFavorited = $this->crosswordModel->getUsersFavorited($id);
            $this->crosswordModel->deleteById($id);
            $this->userModel->updateCreatedCount($this->session->get('userData.id'));
            $this->userModel->updateFavoritedCountMultiple($usersFavorited);
            return $this->response->setJSON(['success' => 'deleted']);
        }

        return $this->response->setJSON(['error' => lang('Crossword.noSuchCrosswordForUser')]);

    }

    public function listCreated($username) {
        $userId = $this->userModel->getIdByUsername($username);
        if ($userId) {
            $itemsCount = $this->crosswordModel->getCrosswordListByUserCount($userId, $this->crosswordModel::CREATED);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = intval($this->request->getGet('p'));
            if ($currentPage > $pages) {
                $currentPage = $pages;
            } else if ($currentPage < 1) {
                $currentPage = 1;
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

    public function listFavorited($username) {
        $userId = $this->userModel->getIdByUsername($username);
        if ($userId) {
            $itemsCount = $this->crosswordModel->getCrosswordListByUserCount($userId, $this->crosswordModel::FAVORITED);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = intval($this->request->getGet('p'));
            if ($currentPage > $pages) {
                $currentPage = $pages;
            } else if ($currentPage < 1) {
                $currentPage = 1;
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

    public function listByTag($tag) {
        if ($this->tagModel->checkIfTagExists($tag)) {
            $itemsCount = $this->crosswordModel->getCrosswordListByTagCount($tag);
            $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
            $currentPage = intval($this->request->getGet('p'));
            if ($currentPage > $pages) {
                $currentPage = $pages;
            } else if ($currentPage < 1) {
                $currentPage = 1;
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

    public function search($searchQuery) {
        $searchQuery = clean_text($searchQuery);

        if (!mb_strlen($searchQuery)) {
            return view('not_found');
        }

        $itemsCount = $this->crosswordModel->getCrosswordListBySearchQueryCount($searchQuery);
        $pages = ceil($itemsCount / self::ITEMS_PER_PAGE) ?: 1;
        $currentPage = intval($this->request->getGet('p'));
        if ($currentPage > $pages) {
            $currentPage = $pages;
        } else if ($currentPage < 1) {
            $currentPage = 1;
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
        $currentPage = intval($this->request->getGet('p'));
        if ($currentPage > $pages) {
            $currentPage = $pages;
        } else if ($currentPage < 1) {
            $currentPage = 1;
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
        $currentPage = intval($this->request->getGet('p'));
        if ($currentPage > $pages) {
            $currentPage = $pages;
        } else if ($currentPage < 1) {
            $currentPage = 1;
        }

        return view('crossword/list', [
            'crosswords' => $this->crosswordModel->getCrosswordList(self::ITEMS_PER_PAGE, self::ITEMS_PER_PAGE * ($currentPage - 1)),
            'listTitle' => lang('Crossword.allCrosswords'),
            'pages' => $pages,
            'currentPage' => $currentPage
        ]);
    }
}
