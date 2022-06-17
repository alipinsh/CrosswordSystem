<?php
/*
 * Komentāra problēmas paziņojuma modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class CommentReportModel extends Model {
    protected $table = 'comments_reports';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'comment_id'
    ];

    public function getReportList() {
        $builder = $this->db->table($this->table);
        $builder->distinct()->select([
            'comments_reports.comment_id',
            'users.username',
            'comments.crossword_id',
            'comments.text'
        ]);
        $builder->join('comments', 'comments.id = comments_reports.comment_id');
        $builder->join('users', 'users.id = comments.user_id');
        $builder->orderBy('comments_reports.comment_id', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function deleteReportsFor(int $commentId) {
        $builder = $this->db->table($this->table);
        $builder->where('comment_id', $commentId)->delete();
    }
}
