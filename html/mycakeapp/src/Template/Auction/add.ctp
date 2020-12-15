<h2>商品を出品する</h2>
<?= $this->Form->create($biditem, ['type' => 'file']) ?>
<fieldset>
    <legend>※商品名と終了日時を入力：</legend>
    <?php
    echo $this->Form->hidden(
        'user_id',
        ['value' => $authuser['id']]
    );
    echo '<p><strong>USER: ' . $authuser['username'] . '</strong></p>';
    echo $this->Form->control('name', ['label' => '商品名']);
    // echo $this->Form->hidden('finished', ['value' => 0]);
    echo $this->Form->control('endtime', ['label' => '終了時間']);
    echo $this->Form->control('description', ['type' => 'textarea', 'label' => '詳細説明', 'cols' => '100', 'rows' => '10']);
    echo $this->Form->control('image_path', ['type' => 'file', 'label' => '商品画像']); //imageに変える
    ?>
</fieldset>
<?= $this->Form->button(__('Submit')) ?>
<?= $this->Form->end() ?>