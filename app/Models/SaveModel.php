<?php
/*
 * Saglabāta progresa data modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class SaveModel extends Model {
    const HORIZONTAL = 0;
    const VERTICAL = 1;
    const QUESTION = 0;
    const ANSWER = 1;

    protected $table = 'users_saves';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id', 'crossword_id', 'save_data', 'needs_update'
    ];

    protected $validationRules = [];

    protected $validationMessages = [];

    protected $skipValidation = false;

    public function getSavesListByUser($userId = 0, $limit = 0, $offset = 0) {
        $builder = $this->db->table($this->table);
        $builder->select([
            'users_saves.id',
            'users_saves.crossword_id',
            'users_saves.save_data',
            'crosswords.id AS crossword_id',
            'crosswords.title',
            'crosswords.width',
            'crosswords.height',
            'crosswords.questions',
            'crosswords.language'
        ]);

        $builder->join('crosswords', 'crosswords.id = users_saves.crossword_id');
        $builder->where('users_saves.user_id', $userId);
        $builder->orderBy('users_saves.id', 'DESC');

        if ($limit) {
            $builder->limit($limit);
        }
        if ($offset) {
            $builder->offset($offset);
        }

        return $builder->get()->getResultArray();
    }

    public function validateSaveData(&$saveData, $crosswordData) {
        $saveData[self::HORIZONTAL] = array_intersect_key($saveData[self::HORIZONTAL], $crosswordData['questions'][self::HORIZONTAL]);
        $saveData[self::VERTICAL] = array_intersect_key($saveData[self::VERTICAL], $crosswordData['questions'][self::VERTICAL]);

        foreach ($crosswordData['questions'][self::HORIZONTAL] as $key => $value) {
            if (!isset($saveData[self::HORIZONTAL][$key])
                || strlen($saveData[self::HORIZONTAL][$key]) != strlen($value[self::ANSWER])
                || !preg_match('/^[a-z\*]+$/', $saveData[self::HORIZONTAL][$key])) {
                $saveData[self::HORIZONTAL][$key] = str_repeat('*', strlen($value[self::ANSWER]));
            }
        }
        foreach ($crosswordData['questions'][self::VERTICAL] as $key => $value) {
            if (!isset($saveData[self::VERTICAL][$key])
                || strlen($saveData[self::VERTICAL][$key]) != strlen($value[self::ANSWER])
                || !preg_match('/^[a-z\*]+$/', $saveData[self::VERTICAL][$key])) {
                $saveData[self::VERTICAL][$key] = str_repeat('*', strlen($value[self::ANSWER]));
            }
        }
    }

    public function setNeedsUpdateFor(int $crosswordId) {
        $builder = $this->db->table($this->table);
        $builder->where('crossword_id', $crosswordId)->update(['needs_update' => 1]);
    }
}
