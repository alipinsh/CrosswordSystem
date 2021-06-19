<?php
/*
 * Komentāra modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class CommentModel extends Model {
	protected $table = 'comments';
	protected $primaryKey = 'id';

    protected $returnType = 'array';
    
	protected $allowedFields = [
		'user_id', 'crossword_id', 'text'
	];

	protected $useTimestamps = true;
	protected $createdField = 'posted_at';
	protected $updatedField = 'edited_at';
    protected $dateFormat = 'datetime';
    
    protected $validationRules = [];

	protected $validationMessages = [];

	protected $skipValidation = false;

	public function getCommentsFor($crosswordId, $limit = 0, $offset = 0) {
		$builder = $this->db->table($this->table);
		$builder->select(['comments.id', 'comments.user_id', 'comments.text', 'comments.posted_at', 'comments.edited_at',
            'users.username', 'users.image']);
		$builder->join('users', 'users.id = comments.user_id');

		$builder->where('crossword_id', $crosswordId);
		$builder->orderBy('comments.id', 'DESC');

		if ($limit) {
		    $builder->limit($limit);
        }

		if ($offset) {
		    $builder->offset($offset);
        }

		return $builder->get()->getResultArray();
	}

    public function getCommentsForCount($crosswordId) {
        $builder = $this->db->table($this->table);
        $builder->select(['comments.id', 'comments.user_id', 'comments.text', 'comments.posted_at', 'comments.edited_at',
            'users.username', 'users.image']);
        $builder->join('users', 'users.id = comments.user_id');

        $builder->where('crossword_id', $crosswordId);

        return $builder->countAllResults();
    }
}