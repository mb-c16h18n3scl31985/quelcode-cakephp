<!DOCTYPE html>
<html lang="ja">

<head>
    <?=$this->Html->charset(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=$this->fetch('title'); ?></title>
    <?=$this->Html->css('hello2'); ?>
    <?=$this->Html->script('hello2'); ?>
</head>

<body>
    <header class="head row">
        <?=$this->element('header',$header) ?>
    </header>

    <div class="content row">
        <?=$this->fetch('content'); ?>
    </div>

    <footer class="foot row">
        <?=$this->element('footer',$footer) ?>
    </footer>
</body>
</html>