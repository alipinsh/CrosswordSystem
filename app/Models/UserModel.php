<?php
/*
 * Lietotāja modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model {
	protected $table = 'users';
	protected $primaryKey = 'id';

    protected $returnType = 'array';
    
	protected $allowedFields = [
		'username', 'email', 'image', 'created_count', 'favorited_count', 'registered_on',
        'password', 'password_confirm',
		'new_email', 'auth_code', 'code_expires', 'email_confirmed'
	];

	protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    
    protected $validationRules = [];

	protected $validationMessages = [];

	protected $skipValidation = false;

	protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
	protected function hashPassword(array $data)
	{
		if (! isset($data['data']['password'])) return $data;

		$data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
		unset($data['data']['password']);
		unset($data['data']['password_confirm']);

		return $data;
	}

	public function getUsernameById(int $id) {
	    $user = $this->find($id);
	    if ($user) {
	        return $user['username'];
        }
	    return null;
    }

    public function getIdByUsername(string $username) {
	    $user = $this->where('username', $username)->find();
	    if ($user) {
	        return $user[0]['id'];
        }
	    return null;
    }

	public function updateCreatedCount(int $userId) {
	    $builder = $this->db->table('crosswords');
	    $count = $builder->where('user_id', $userId)->where('is_public', 1)->countAllResults();
	    $this->save(['id' => $userId, 'created_count' => $count]);
    }

    public function updateFavoritedCount(int $userId) {
        $builder = $this->db->table('users_favs');
        $count = $builder->where('user_id', $userId)->countAllResults();
        $this->save(['id' => $userId, 'favorited_count' => $count]);
    }

    public function updateFavoritedCountMultiple(array $userIds) {
        $builder = $this->db->table('users_favs');
        $builder->select('users_favs.user_id')->selectCount('users_favs.crossword_id', 'favorited_count');
        $builder->groupBy('users_favs.crossword_id')->havingIn('users_favs.user_id', $userIds);
        $counts = $builder->get()->getResultArray();
        $counts = array_map(function($c) {
            return [
                'id' => $c['user_id'],
                'favorited_count' => $c['favorited_count']
            ];
        }, $counts);

        $userBuilder = $this->db->table($this->table);
        $userBuilder->updateBatch($counts);
    }

}