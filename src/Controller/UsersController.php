<?php
namespace App\Controller;

use App\Controller\AppController;

use Cake\Event\Event;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method \App\Model\Entity\User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class UsersController extends AppController
{

    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow(['add', 'token']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $users = $this->paginate($this->Users);

        // $this->set(compact('users'));

        $this->set([
            'res' => $users,
            '_serialize' => 'res'
        ]);

    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);

        // $this->set('user', $user);
        $this->set([
            'res' => $user,
            '_serialize' => 'res'
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEntity();
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());

            
            if($u = $this->Users->save($user)){
                $data = [
                    'code'=>200,
                    'data'=>[
                        'id' => $u->id,
                        'token' => JWT::encode(
                            [
                                'sub' => $u->id,
                                'exp' =>  time() + 604800
                            ],
                        Security::salt())
                    ]
                ];
            }else{
                $data = [
                    'code'=>406,
                    'msg'=>'user can not register'
                ];
            }
        }
        // $this->set(compact('user'));

        $this->set([
            'res' => $data,
            '_serialize' => 'res'
        ]);

    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function token(){

        $user = $this->Auth->identify();

        if($user){
            $res = [
                    'code'=>200,
                    'data'=>[
                        'token' => JWT::encode(
                            [
                                'sub' => $user['id'],
                                'exp' =>  time() + 604800
                            ],
                        Security::salt())
                    ]
            ];
        }else{
            $res = [
                'code'=>401,
                'msg'=>'Invalid username or password'
            ];
        }

        $this->set([
            'res' => $res,
            '_serialize' => 'res'
        ]);

    }
}
