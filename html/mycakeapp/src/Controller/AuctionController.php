<?php

namespace App\Controller;

use App\Controller\AppController;
use Cake\Event\Event;


//トランザクション用
use Cake\Datasource\ConnectionManager;
use Cake\Core\Configure;

class AuctionController extends AuctionBaseController
{
    //デフォルトテーブルを使わない
    public $useTable = false;


    public function initialize()
    {
        parent::initialize();
        $this->loadComponent('Paginator');

        $this->loadModel('Users');
        $this->loadModel('Biditems');
        $this->loadModel('Bidrequests');
        $this->loadModel('Bidinfo');
        $this->loadModel('Bidmessages');

        //ログインしているユーザー情報をauthuserに設定
        $this->set('authuser', $this->Auth->user());

        //レイアウトをauctionに変更
        $this->viewBuilder()->setLayout('auction');
    }


    //トップページ
    public function index()
    {
        //ページネーションでBiditemsを取得
        $auction = $this->paginate('Biditems', [
            'order' => ['endtime' => 'desc'],
            'limit' => 10
        ]);
        $this->set(compact('auction'));
    }


    //商品情報の表示
    public function view($id = null)
    {
        //$idのBiditemを取得
        $biditem = $this->Biditems->get($id, [
            'contain' => ['Users', 'Bidinfo', 'Bidinfo.Users']
        ]);

        //オークション終了時の処理
        if ($biditem->endtime < new \DateTime('now') and $biditem->finished == 0) {
            //まだ終了時間じゃない&まだ終わってない

            //finishedに1を代入
            $biditem->finished = 1;
            $this->Biditems->save($biditem);

            //落札情報bidinfoを作成する
            $bidinfo = $this->Bidinfo->newEntity();

            //落札情報Bidinfoのbiditem_idに$idを設定
            $bidinfo->biditem_id = $id;

            //最高金額の入札情報bidrequestを検索
            $bidrequest = $this->Bidrequests->find('all', [
                'conditions' => ['biditem_id' => $id],
                'contain' => ['Users'],
                'order' => ['price' => 'desc']
            ])->first();

            //入札情報があるとき
            if (!empty($bidrequest)) {
                //落札情報bidinfoの各種プロパティを設定して保存する
                $bidinfo->user_id = $bidrequest->user->id;
                $bidinfo->user = $bidrequest->user;
                $bidinfo->price = $bidrequest->price;
                $this->Bidinfo->save($bidinfo);
            }

            //Biditemのb落札情報idinfoに$bidinfoを設定
            $biditem->bidinfo = $bidinfo;
        }

        //入札情報Bidrequestsからbiditem_idが$idのものを取得
        $bidrequests = $this->Bidrequests->find('all', [
            'conditions' => ['biditem_id' => $id],
            'contain' => ['Users'],
            'order' => ['price' => 'desc']
        ])->toArray();

        //オブジェクト類をテンプレート用に設定
        $this->set(compact('biditem', 'bidrequests'));
    }


    //画像保存前のチェックを行う関数
    //$fileにはフォームから送信されたファイルが入れられる
    public function check_file($file)
    {
        $file_path = $file['tmp_name'];

        //ファイルサイズは2MB以下
        if (filesize($file_path) > 2000000) {
            throw new \Exception('2MB以下のファイルをご用意下さい。');
        }

        //mimetypeはjpg,png,gitのいずれか
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $file_mimetype = $finfo->file($file_path);
        $config['ALLOW_MIME'] = ['image/jpeg', 'image/png', 'image/gif'];

        //ファイルのmimetypeが$config['ALLOW_MIME']のいずれかに該当するかチェック
        if (!in_array($file_mimetype, $config['ALLOW_MIME'])) {
            throw new \Exception('ファイル形式は.jpg/.jpeg/.gif/.pngいずれかでご投稿下さい');
        }
        return true;
    }


    //フォームから送信された値を保存する
    public function add()
    {
        //Biditemインスタンスを用意
        $biditem = $this->Biditems->newEntity();

        //イメージファイルをcakePHP側に保存
        if ($this->request->is('post')) {

            try {
                $connection = ConnectionManager::get('default');

                //------------------------トランザクションここから--------------------------
                $connection->begin();
                //イメージファイル取り出し
                $file = $this->request->data['image_path'];

                //フォームから送られてきたファイルをcheck_file()で保存前にチェックし、
                //通ればwebroot/img/biditem/imageディレクトリに保存
                if ($this->check_file($file)) {
                    $file_name = date('YmdHis') . $file['name'];
                    $file_path = WWW_ROOT . 'img/biditem_image/' . $file_name;
                    move_uploaded_file($file['tmp_name'], $file_path);
                } else {
                    throw new \Exception("ファイル保存に失敗しました。もう一度投稿してください。");
                }

                $data = [
                    'name' => $this->request->getData('name'),
                    'endtime' => $this->request->getData('endtime'),
                    'description' => $this->request->getData('description'),
                    'image_path' => $file_name,
                    'finished' => 0,
                    'user_id' => $this->request->getData('user_id')
                ];

                //Formからの送信内容をデータベースに保存
                $biditem = $this->Biditems->patchEntity($biditem, $data);

                //$biditemを保存する
                if ($this->Biditems->save($biditem)) {
                    $this->Flash->success(__('保存しました。'));

                    //**************トランザクション_コミット****************
                    $connection->commit();
                    return $this->redirect(['action' => 'index']);
                }
            } catch (\Exception $error) {
                $this->Flash->error($error->getMessage());

                //**************トランザクション_ロールバック****************
                $connection->rollback();
            }
        }
        //-------------------------トランザクションここまで-------------------------
        $this->set(compact('biditem'));
    }


    //入札
    public function bid($biditem_id = null)
    {
        //入札用のBidreqtestインスタンスを用意
        $bidrequest = $this->Bidrequests->newEntity();

        //bidrequestにbiditem_idをuser_idを設定
        $bidrequest->biditem_id = $biditem_id;
        $bidrequest->user_id = $this->Auth->user('id');

        if ($this->request->is('post')) {
            //$bidrequestに送信フォームの内容を反映する
            $bidrequest = $this->Bidrequests->patchEntity($bidrequest, $this->request->getData());

            if ($this->Bidrequests->save($bidrequest)) {
                $this->Flash->success(__('入札を送信しました。'));
                return $this->redirect(['action' => 'view', $biditem_id]);
            }
            $this->Flash->error(__('入札に失敗しました。もう一度ご入力ください。'));
        }

        //$biditem_idの$biditemを取得
        $biditem = $this->Biditems->get($biditem_id);
        $this->set(compact('bidrequest', 'biditem'));
    }



    public function msg($bidinfo_id = null)
    {
        //Bidmessageを新たに用意
        $bidmsg = $this->Bidmessages->newEntity();

        if ($this->request->is('post')) {
            //フォームに尊信された値で$bidmsgを更新
            $bidmsg = $this->Bidmessages->patchEntity($bidmsg, $this->request->getData());

            //Bidmessageを保存
            if ($this->Bidmessages->save($bidmsg)) {
                $this->Flash->success(__('保存しました。'));
            } else {
                $this->Flash->error(__('保存に失敗しました。もう一度ご入力ください。'));
            }

            try {
                //bidinfo_idからBidinfoを取得する
                $bidinfo = $this->Bidinfo->get($bidinfo_id, ['contain' => ['Biditems']]);
            } catch (\Exception $e) {
                $bidinfo = null;
            }

            //Bidmessageをbidinfo_idとuser_idで検索
            $bidmsgs = $this->Bidmessages->find('all', [
                'conditions' => ['bidinfo_id' => $bidinfo_id],
                'contain' => ['Users'],
                'order' => ['created' => 'desc']
            ]);
            $this->set(compact('bidmsgs', 'bidinfo', 'bidmsg'));
        }
    }



    //落札情報の表示
    public function home()
    {
        //自分の落札情報Bidinfoをページネーションで取得
        $bidinfo = $this->paginate('Bidinfo', [
            'conditions' => ['Bidinfo.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Biditems'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('bidinfo'));
    }



    //出品情報の表示
    public function home2()
    {
        $biditems = $this->paginate('Biditems', [
            'conditions' => ['Biditems.user_id' => $this->Auth->user('id')],
            'contain' => ['Users', 'Bidinfo'],
            'order' => ['created' => 'desc'],
            'limit' => 10
        ])->toArray();
        $this->set(compact('biditems'));
    }
}
