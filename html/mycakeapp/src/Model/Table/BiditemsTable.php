<?php

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Biditems Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\BidinfoTable&\Cake\ORM\Association\HasMany $Bidinfo
 * @property \App\Model\Table\BidrequestsTable&\Cake\ORM\Association\HasMany $Bidrequests
 *
 * @method \App\Model\Entity\Biditem get($primaryKey, $options = [])
 * @method \App\Model\Entity\Biditem newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\Biditem[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Biditem|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Biditem saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Biditem patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Biditem[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\Biditem findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class BiditemsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('biditems');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);
        $this->hasOne('Bidinfo', [
            'foreignKey' => 'biditem_id',
        ]);
        $this->hasMany('Bidrequests', [
            'foreignKey' => 'biditem_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 100)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        $validator
            ->boolean('finished')
            ->requirePresence('finished', 'create')
            ->notEmptyString('finished');

        $validator
            ->dateTime('endtime')
            ->requirePresence('endtime', 'create')
            ->notEmptyDateTime('endtime');


        //詳細説明
        $validator
            ->requirePresence('create')
            ->maxLength('description', 1000, '1000文字以内でご入力下さい。')
            ->notEmptyString('description', '商品説明をご入力下さい。');


        //商品画像
        $validator
            ->requirePresence('image_path')
            ->notEmptyFile('image_path', '商品画像をご用意下さい。')

            //ファイルアップロード
            ->add('image_path', 'uploadError', [
                'rule' => ['uploadError'],
                'message' => 'ファイルのアップロードができませんでした。もう一度お試し下さい。',
                'last' => true
            ])

            //ファイルサイズは2MB以下
            ->add('image_path', 'fileSize', [
                'rule' => ['fileSize', '<=', '2000'],
                'message' => '2MB以下のファイルをご用意下さい。'
            ])

            //拡張子のチェック
            ->add('image_path', 'extension', [
                'rule' => ['extension', ['jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG', 'gif', 'GIF']],
                'message' => 'ファイル形式は.jpg/.jpeg/.gif/.pngいずれかでご投稿下さい'
            ])

            //mimeTypeチェック
            ->add('image_path', 'mimeType', [
                'rule' => ['mimeType', ['image/jpeg', 'image/png', 'image/gif']],
                'message' => 'ファイル形式は.jpg/.jpeg/.gif/.pngいずれかでご投稿下さい',
            ]);


        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
