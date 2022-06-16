<?php
/*
 * Skripts, kurš ļauj izveidot nepiciešamas tabulas datubazē, kura ir nodefinēta konfiguracijā.
 * Palaižas ar: php spark migrate
 */


namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Setup extends Migration
{
    public function up()
    {
        // users table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
            ],
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'image' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'password_hash' => [
                'type' => 'VARCHAR',
                'constraint' => 255
            ],
            'created_count' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'default' => 0
            ],
            'favorited_count' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'default' => 0
            ],
            'registered_on' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'role' => [
                'type' => 'INT',
                'constraint' => 1,
                'default' => 1
            ],
            'new_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true
            ],
            'auth_code' => [
                'type' => 'VARCHAR', 
                'constraint' => 16, 
                'null' => true
            ],
            'code_expires' => [
                'type' => 'DATETIME', 
                'null' => true
            ],
            'email_confirmed' => [
                'type' => 'BOOLEAN', 
                'null' => false, 
                'default' => 0
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('users', true);

        // crosswords table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'width' => [
                'type' => 'INT',
                'constraint' => 3,
                'unsigned' => true
            ],
            'height' => [
                'type' => 'INT',
                'constraint' => 3,
                'unsigned' => true
            ],
            'questions' => [
                'type' => 'INT',
                'constraint' => 5,
                'unsigned' => true
            ],
            'favorites' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'is_public' => [
                'type' => 'BOOLEAN',
                'default' => 0
            ],
            'data' => [
                'type' => 'JSON'
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'tags' => [
                'type' => 'TEXT'
            ],
            'language' => [
                'type' => 'CHAR',
                'constraint' => 2
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('crosswords', true);

        // tags table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'tag' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('tags', true);

        // crosswords_tags table
        $this->forge->addField([
            'crossword_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'tag_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
        ]);
        $this->forge->addPrimaryKey(['crossword_id', 'tag_id']);
        $this->forge->addForeignKey('crossword_id', 'crosswords', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('tag_id', 'tags', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('crosswords_tags', true);
        
        // comments table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'crossword_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'posted_at' => [
                'type' => 'DATETIME'
            ],
            'edited_at' => [
                'type' => 'DATETIME'
            ],
            'text' => [
                'type' => 'TEXT'
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('crossword_id', 'crosswords', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('comments', true);
        
        // users_favs table
        $this->forge->addField([
            'user_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'crossword_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ]
        ]);
        $this->forge->addPrimaryKey(['user_id', 'crossword_id']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('crossword_id', 'crosswords', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users_favs', true);
        
        // users_saves table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'crossword_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'save_data' => [
                'type' => 'JSON'
            ],
            'needs_update' => [
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => 0
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('user_id', 'users', 'id','CASCADE', 'CASCADE');
        $this->forge->addForeignKey('crossword_id', 'crosswords', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users_saves', true);

        // crosswords reports table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'crossword_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ],
            'report' => [
                'type' => 'TEXT'
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('crossword_id', 'crosswords', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('crosswords_reports', true);

        // comments reports table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'comment_id' => [
                'type' => 'INT',
                'constraint' => 9,
                'unsigned' => true
            ]
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('comment_id', 'comments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('comments_reports', true);
    }

    public function down()
    {
        $this->forge->dropTable('users');
        $this->forge->dropTable('crosswords');
        $this->forge->dropTable('tags');
        $this->forge->dropTable('crosswords_tags');
        $this->forge->dropTable('comments');
        $this->forge->dropTable('users_favs');
        $this->forge->dropTable('users_saves');
    }
}
