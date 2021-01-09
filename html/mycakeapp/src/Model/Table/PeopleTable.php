<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class PeopleTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->hasMany('Messages');
    }

    public function findMe(Query $query, array $options)
    {
        $me = $options['me'];
        return $query->where(['name like' => '%' . $me . '%'])
            ->orWhere(['mail like' => '%' . $me . '%'])
            ->order(['age' => 'asc']);
    }

    public function findByAge(Query $query, array $options)
    {
        return $query->order(['age' => 'asc'])
            ->order(['name' => 'asc']);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id', 'idは整数でご入力ください。')
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name', 'テキストをご入力ください。')
            ->requirePresence('name', 'create')
            ->notEmpty('name', '名前は必ず入力してください。');

        $validator
            ->scalar('mail', 'テキストをご入力ください。')
            ->allowEmpty('mail') //●
            ->email('mail', false, 'メールアドレスを記入してください。');

        $validator
            ->integer('age', '整数をご入力ください。')
            ->requirePresence('age', 'create')
            ->notEmpty('age', '必ず値をご入力ください。') //●
            ->greaterThan('age', -1, 'ゼロ以上の値をご入力ください。');

        return $validator;
    }
}
