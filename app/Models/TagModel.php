<?php
/*
 * Taga modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class TagModel extends Model {
    protected $table = 'tags';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'tag'
    ];

    public function getAllPublicTags() {
        $builder = $this->db->table($this->table);
        $builder->distinct();
        $builder->select(['tag']);
        $builder->join('crosswords_tags', 'tags.id = crosswords_tags.tag_id');
        $builder->join('crosswords', 'crosswords.id = crosswords_tags.crossword_id');
        $builder->where('crosswords.is_public', true);
        $builder->orderBy('tag', 'ASC');
        return $builder->get()->getResultArray();
    }

    public function checkIfTagExists(string $tag) {
        $builder = $this->db->table($this->table);
        $result = $builder->select()->where('tags.tag', $tag)->countAllResults();
        return boolval($result);
    }

    public function validateTags(array &$tags) {
        foreach ($tags as $i => $tag) {
            $tags[$i] = trim($tag);
            if (!preg_match('/^[abcdefghijklmnopqrstuvwxyzабвгдеёжзийклмнопрстуфхцчшщьыъэюяāčēģīķļņšūž]+$/', $tag)) {
                return false;
            }
        }
        return true;
    }

    public function updateTags(int $crosswordId) {
        $crosswords = $this->db->table('crosswords');
        $tagsString = $crosswords->where('id', $crosswordId)->get()->getResultArray()[0]['tags'];
        $tagsArray = explode(',', $tagsString);
        $crosswordTags = $this->db->table('crosswords_tags');
        $crosswordTags->where('crossword_id', $crosswordId)->delete();
        $insertArray = [];
        foreach ($tagsArray as $tag) {
            $tagRow = $this->where('tag', $tag)->findColumn('id');
            $tagId = $tagRow ? $tagRow[0] : null;
            if (!$tagId) {
                $this->save(['tag' => $tag]);
                $tagId = $this->getInsertID();
            }
            $insertArray[] = ['crossword_id' => $crosswordId, 'tag_id' => $tagId];
        }
        if ($insertArray) {
            $crosswordTags->insertBatch($insertArray);
        }
    }
}
