<p>This is sample content.</p>
<p>これは、Hello2レイアウトを利用したサンプルです。</p>
<ul>
    <?= $this->Html->nestedList(
        [
            'first line', 'second line', 'third line' => ['one', 'two', 'three']
        ]
    ) ?>
    <?= $this->Url->build(
        [
            'controller' => 'hello',
            'action' => 'show',
            '?' => ['id' => 'taro', 'password' => 'yamada123']
        ]
    ) ?>
</ul>