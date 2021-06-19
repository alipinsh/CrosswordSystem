<?php
/*
 * Problēmas paziņojuma modelis.
 */

namespace App\Models;

use CodeIgniter\Model;

class ReportModel extends Model {
    protected $table = 'reports';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'crossword_id', 'report'
    ];

    public function getReportList() {
        $builder = $this->db->table($this->table);
        $builder->select([
            'reports.crossword_id',
            'crosswords.title',
            'crosswords.is_public',
            'reports.report'
        ]);
        $builder->join('crosswords', 'crosswords.id = reports.crossword_id');
        $builder->orderBy('reports.id', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function deleteReportsFor(int $crosswordId) {
        $builder = $this->db->table($this->table);
        $builder->where('crossword_id', $crosswordId)->delete();
    }
}