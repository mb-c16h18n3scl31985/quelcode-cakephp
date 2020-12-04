<?php
use Migrations\AbstractMigration;

class AddColumnsToBiditems extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     * @return void
     */
    public function change()
    {
        $table = $this->table('biditems');
        $table->addColumn('description', 'string', [
            'default' => '',
            'limit' => 1000,
            'null' => false,
        ]);
        $table->addColumn('image_path', 'string', [
            'default' => '',
            'limit' => 255,
            'null' => false,
        ]);
        $table->update();
    }
}
