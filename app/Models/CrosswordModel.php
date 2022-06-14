<?php
/*
 * Krustvārdu mīklas modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class CrosswordModel extends Model {
    const HORIZONTAL = 0;
    const VERTICAL = 1;
    const WIDTH = 0;
    const HEIGHT = 1;
    const X = 0;
    const Y = 1;
    const QUESTION = 0;
    const ANSWER = 1;
    const MIN_SIZE = 1;
    const MAX_SIZE = 100;
    const CREATED = 0;
    const FAVORITED = 1;

    protected $table = 'crosswords';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    
    protected $allowedFields = [
        'published_at', 'updated_at',
        'title', 'width', 'height',
        'questions', 'favorites', 'is_public', 'data', 'user_id', 'tags'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    
    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    public function getCrosswordList($limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords.id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.favorites'
        ]);
        $builder->where('is_public', true);
        $builder->orderBy('id', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getCrosswordListCount() {
        $builder = $this->db->table($this->table);
        $builder->where('is_public', true);

        return $builder->countAllResults();
    }

    public function getCrosswordListByUser($userId = 0, $type = self::CREATED, $limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords.id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.favorites'
        ]);

        $builder->where('crosswords.is_public', true);
        $builder->orderBy('crosswords.id', 'DESC');

        switch ($type) {
            case self::CREATED:
                $builder->where('crosswords.user_id', $userId);
                break;
            case self::FAVORITED:
                $builder->join('users_favs', 'crosswords.id = users_favs.crossword_id');
                $builder->where('users_favs.user_id', $userId);
                break;
        }
        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getCrosswordListByUserCount($userId = 0, $type = self::CREATED) {
        $builder = $this->db->table($this->table);

        $builder->where('crosswords.is_public', true);

        switch ($type) {
            case self::CREATED:
                $builder->where('crosswords.user_id', $userId);
                break;
            case self::FAVORITED:
                $builder->join('users_favs', 'crosswords.id = users_favs.crossword_id');
                $builder->where('users_favs.user_id', $userId);
                break;
        }
        return $builder->countAllResults();
    }

    public function getCrosswordListByTag($tag, $limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords.id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.favorites'
        ]);

        $builder->where('crosswords.is_public', true);
        $builder->join('crosswords_tags', 'crosswords_tags.crossword_id = crosswords.id');
        $builder->join('tags', 'tags.id = crosswords_tags.tag_id');
        $builder->where('tags.tag', $tag);

        $builder->orderBy('crosswords.id', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getCrosswordListByTagCount($tag) {
        $builder = $this->db->table($this->table);

        $builder->where('crosswords.is_public', true);
        $builder->join('crosswords_tags', 'crosswords_tags.crossword_id = crosswords.id');
        $builder->join('tags', 'tags.id = crosswords_tags.tag_id');
        $builder->where('tags.tag', $tag);

        return $builder->countAllResults();
    }

    public function getCrosswordListBySearchQuery($searchQuery, $limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords.id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.favorites'
        ]);

        $builder->where('crosswords.is_public', 1);

        $builder->groupStart();
        $builder->like('crosswords.title', $searchQuery, 'both', null, true);
        $builder->orLike('crosswords.tags', $searchQuery, 'both', null, true);
        $builder->orLike('crosswords.data', $searchQuery, 'both', null, true);
        $builder->groupEnd();

        $builder->orderBy('crosswords.id', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getCrosswordListBySearchQueryCount($searchQuery) {
        $builder = $this->db->table($this->table);

        $builder->where('crosswords.is_public', 1);

        $builder->groupStart();
        $builder->like('crosswords.title', $searchQuery, 'both', null, true);
        $builder->orLike('crosswords.tags', $searchQuery, 'both', null, true);
        $builder->orLike('crosswords.data', $searchQuery, 'both', null, true);
        $builder->groupEnd();

        return $builder->countAllResults();
    }

    public function getCrosswordListPrivates($userId = 0, $limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords.id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.favorites'
        ]);

        $builder->where('crosswords.is_public', false);
        $builder->where('crosswords.user_id', $userId);
        $builder->orderBy('crosswords.id', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function getCrosswordListPrivatesCount($userId = 0) {
        $builder = $this->db->table($this->table);

        $builder->where('crosswords.is_public', false);
        $builder->where('crosswords.user_id', $userId);

        return $builder->countAllResults();
    }

    public function getFavorited(int $crosswordId, int $userId) {
        $favsTable = $this->db->table('users_favs');
        $r = $favsTable->where('crossword_id', $crosswordId)->where('user_id', $userId)->countAllResults();
        if (!$r) {
            $favsTable->insert(['crossword_id' => $crosswordId, 'user_id' => $userId]);
            $favCount = $favsTable->where('crossword_id', $crosswordId)->countAllResults();
            $builder = $this->db->table($this->table);
            $builder->where('id', $crosswordId);
            $builder->update(['favorites' => $favCount]);
            return true;
        }
        return false;
    }

    public function checkIfUserFavorited(int $userId, int $crosswordId) {
        $favsTable = $this->db->table('users_favs');
        $r = $favsTable->where('crossword_id', $crosswordId)->where('user_id', $userId)->countAllResults();
        return boolval($r);
    }

    public function validateCrosswordData(array &$crosswordData) {
        // size check
        if (!(is_numeric($crosswordData['size'][self::WIDTH])
            && is_numeric($crosswordData['size'][self::HEIGHT]))) {
            return false;
        }
        if (!($crosswordData['size'][self::WIDTH] >= self::MIN_SIZE
            && $crosswordData['size'][self::WIDTH] <= self::MAX_SIZE
            && $crosswordData['size'][self::HEIGHT] >= self::MIN_SIZE
            && $crosswordData['size'][self::HEIGHT] <= self::MAX_SIZE)) {
            return false;
        }

        // positions check
        if (!count($crosswordData['positions'])) {
            return false;
        }

        foreach ($crosswordData['positions'] as $pos) {
            if (!(is_numeric($pos[self::X]) && is_numeric($pos[self::Y]))) {
                return false;
            }
            if (!($pos[self::X] >= 0 && $pos[self::X] <= $crosswordData['size'][self::WIDTH]
                && $pos[self::Y] >= 0 && $pos[self::Y] <= $crosswordData['size'][self::HEIGHT])) {
                return false;
            }
        }

        // questions and answers check
        $largestId = 0;
        foreach ([self::HORIZONTAL, self::VERTICAL] as $o) {
            foreach ($crosswordData['questions'][$o] as $key => $value) {
                if (empty($value[self::QUESTION]) || empty($value[self::ANSWER])) {
                    return false;
                }
                if (strlen($value[self::QUESTION]) > 2000) {
                    return false;
                }
                if (!preg_match('/^[a-z]+$/i', $value[self::ANSWER])) {
                    return false;
                }
                $question = trim($value[self::QUESTION]);
                $question = preg_replace('/  +/', ' ', $question);
                $question = preg_replace('/(?:\r?\n|\r){2,}/', "\n", $question);
                $question = preg_replace('/[ \t]+/', ' ', $question);
                $question = htmlspecialchars($question);
                $crosswordData['questions'][$o][$key][self::QUESTION] = $question;
                $crosswordData['questions'][$o][$key][self::ANSWER] = mb_strtolower($value[self::ANSWER]);
                if ($largestId < $key) {
                    $largestId = $key;
                }
            }
        }
        if ($largestId !== sizeof($crosswordData['positions'])) {
            return false;
        }
        foreach ($crosswordData['questions'][self::HORIZONTAL] as $key => $value) {
            $position = $crosswordData['positions'][$key - 1];
            $answerLength = strlen($value[self::ANSWER]);

            for ($wx = $position[self::X]+1; $wx < $position[self::X] + $answerLength; ++$wx) {
                foreach ($crosswordData['positions'] as $i => $p) {
                    if ($wx == $p[self::X]
                        && $position[self::Y] == $p[self::Y]
                        && isset($crosswordData['questions'][self::HORIZONTAL][$i+1])) {
                        return false;
                    }
                }
            }
        }
        foreach ($crosswordData['questions'][self::VERTICAL] as $key => $value) {
            $position = $crosswordData['positions'][$key - 1];
            $answerLength = strlen($value[self::ANSWER]);

            for ($wy = $position[self::Y]+1; $wy < $position[self::Y] + $answerLength; ++$wy) {
                foreach ($crosswordData['positions'] as $i => $p) {
                    if ($position[self::X] == $p[self::X]
                        && $wy == $p[self::Y]
                        && isset($crosswordData['questions'][self::VERTICAL][$i+1])) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function getUsersFavorited(int $crosswordId) {
        $builder = $this->db->table('users_favs');
        $builder->select([
            'users_favs.user_id'
        ]);
        $builder->where('users_favs.crossword_id', $crosswordId);
        $builder = $builder->get();
        $builder = $builder->getResultArray();

        return $builder;
    }

    public function deleteById(int $id) {
        $this->delete($id);
    }
}
