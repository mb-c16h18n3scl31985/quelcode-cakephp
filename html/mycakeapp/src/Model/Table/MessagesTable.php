<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class MessagesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('message');
        $this->belongsTo('People');
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->allowEmpty('id', 'create');

        $validator->integer('person_id', 'person idは整数でご入力ください。')
            ->notEmpty('person_id', 'person idは必ずご記入ください。');

        $validator->scalar('message', 'テキストをご入力ください。')
            ->requirePresence('message', 'create')
            ->notEmpty('message', 'メッセージは必ず入力してください。');

        return $validator;
    }
}
