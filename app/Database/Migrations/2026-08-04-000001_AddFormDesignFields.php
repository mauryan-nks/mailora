<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFormDesignFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('forms', [
            'design_style' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'classic'],
            'background_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#ffffff'],
            'accent_color' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => '#44B89D'],
            'parallax' => ['type' => 'TINYINT', 'default' => 0],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('forms', ['design_style', 'background_color', 'accent_color', 'parallax']);
    }
}
