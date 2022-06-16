<?php
/*
 * Mīklas problēmas paziņojuma modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class CrosswordReportModel extends Model {
    protected $table = 'crosswords_reports';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'crossword_id', 'report'
    ];

    public function getReportList() {
        $builder = $this->db->table($this->table);
        $builder->select([
            'crosswords_reports.crossword_id',
            'crosswords.title',
            'crosswords.is_public',
            'crosswords_reports.report'
        ]);
        $builder->join('crosswords', 'crosswords.id = crosswords_reports.crossword_id');
        $builder->orderBy('crosswords_reports.id', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function deleteReportsFor(int $crosswordId) {
        $builder = $this->db->table($this->table);
        $builder->where('crossword_id', $crosswordId)->delete();
    }
}
