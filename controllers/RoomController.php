<?php
class Chat_RoomController extends Tri_Controller_Action
{
    public function init()
    {
        parent::init();
        $this->view->title = "Chat";
    }

    public function indexAction()
    {
        $session  = new Zend_Session_Namespace('data');
        $table    = new Tri_Db_Table('chat_room');
        $page     = Zend_Filter::filterStatic($this->_getParam('page'), 'int');
        $query    = Zend_Filter::filterStatic($this->_getParam("q"), 'stripTags');
        $select   = $table->select(true)
                          ->columns("(SELECT COUNT(DISTINCT user_id) FROM chat_room_message
                                      WHERE chat_room_id = chat_room.id
                                      AND `status` = 'logged'
                                      AND created > SYSDATE() - INTERVAL 5 MINUTE) as total")
                          ->order(array('status DESC', 'id DESC'));

        $select->where('classroom_id = ?', $session->classroom_id);

        if ($query) {
            $select->where('UPPER(title) LIKE UPPER(?)', "%$query%");
        }

        $paginator = new Tri_Paginator($select, $page);
        $this->view->data = $paginator->getResult();
        $this->view->q = $query;
    }

    public function formAction()
    {
        $id   = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $form = new Chat_Form_Room();

        if ($id) {
            $table = new Tri_Db_Table('chat_room');
            $row   = $table->find($id)->current();

            if ($row) {
                $form->populate($row->toArray());
            }
        }
        
        $this->view->form = $form;
    }

    public function saveAction()
    {
        $form  = new Chat_Form_Room();
        $table = new Tri_Db_Table('chat_room');
        $session = new Zend_Session_Namespace('data');
        $data  = $this->_getAllParams();

        if ($form->isValid($data)) {
            $data = $form->getValues();
            $data['user_id']      = Zend_Auth::getInstance()->getIdentity()->id;
            $data['classroom_id'] = $session->classroom_id;

            if (isset($data['id']) && $data['id']) {
                $row = $table->find($data['id'])->current();
                $row->setFromArray($data);
                $id = $row->save();
            } else {
                unset($data['id']);
                $row = $table->createRow($data);
                $id = $row->save();
                Application_Model_Timeline::save('created a new chat room', $data['title']);
            }

            $this->_helper->_flashMessenger->addMessage('Success');
            $this->_redirect('chat/room');
        }
        
        $this->_response->prepend('messages', $this->view->translate('Error'));
        $this->view->form = $form;
        $this->render('form');
    }

    public function deleteAction()
    {
        $table   = new Tri_Db_Table('chat_room');
        $block   = new Tri_Db_Table('chat_room_blocked');
        $message = new Tri_Db_Table('chat_room_message');
        $id      = Zend_Filter::filterStatic($this->_getParam('id'), 'int');

        if ($id) {
            $block->delete(array('chat_room_id = ?' => $id));
            $message->delete(array('chat_room_id = ?' => $id));
            $table->delete(array('id = ?' => $id));
            $this->_helper->_flashMessenger->addMessage('Success');
        }

        $this->_redirect('chat/room/index/');
    }

    public function liveAction()
    {
        $id = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $chat = new Tri_Db_Table('chat_room');
        $table = new Tri_Db_Table('chat_room_message');

        $data = array('user_id' => Zend_Auth::getInstance()->getIdentity()->id,
                      'chat_room_id' => $id,
                      'status' => 'logged');
        $table->createRow($data)->save();

        $this->view->data = $chat->find($id)->current();

        $select = $table->select(true)->setIntegrityCheck(false)
                        ->join('user', 'user.id = user_id', array('name','role','image'))
                        ->where('chat_room_id = ?', $id)
                        ->where('chat_room_message.status = ?', 'message')
                        ->order('id');
        $this->view->stream = $table->fetchAll($select, 'id DESC');

        $select = $table->select(true)->setIntegrityCheck(false)
                        ->join('user', 'user.id = user_id', array('name','role','image'))
                        ->joinLeft('chat_room_blocked', 'user.id = chat_room_blocked.user_id AND chat_room_blocked.chat_room_id = chat_room_message.chat_room_id', array('blocked' => 'chat_room_blocked.chat_room_id'))
                        ->where('chat_room_message.chat_room_id = ?', $id)
                        ->where('chat_room_message.created > SYSDATE() - INTERVAL 5 MINUTE')
                        ->order(array('blocked','name'))
                        ->group('user.id');
        $this->view->users = $table->fetchAll($select);

        $this->view->id = $id;
        if ($this->_hasParam('blocked')) {
            $this->view->blocked = $this->_hasParam('blocked');
        }
        
        if ($this->_hasParam('interval')) {
            $this->_helper->layout->disableLayout();
            $this->view->interval = $this->_hasParam('interval');
            $this->render('stream');
        }
    }

    public function liveSaveAction()
    {
        $id = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $description = Zend_Filter::filterStatic($this->_getParam('description'), 'stripTags');

        $table   = new Tri_Db_Table('chat_room_message');
        $blocked = new Tri_Db_Table('chat_room_blocked');
        $userId  = Zend_Auth::getInstance()->getIdentity()->id;
        
        if ($blocked->fetchRow(array('user_id = ?' => $userId, 
                                     'chat_room_id = ?' => $id))) {
            $this->_redirect('chat/room/live/interval/true/blocked/true/id/'.$id);
        }

        $data = array('user_id' => $userId,
                      'chat_room_id' => $id,
                      'description' => $description,
                      'status' => 'message');

        $table->createRow($data)->save();

        $this->_redirect('chat/room/live/interval/true/id/'.$id);
    }
    
    public function blockAction() 
    {
        $id     = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $blocks = $this->_getParam('block');
        $table  = new Tri_Db_Table('chat_room_blocked');
        
        try {
            foreach ($blocks as $userId) {
                $table->createRow(array('user_id' => $userId, 
                                        'chat_room_id' => $id))->save();
            }
        } catch(Exception $e) {}
        $this->_redirect('chat/room/live/interval/true/id/' . $id);
    }
    
    public function unblockAction() 
    {
        $id     = Zend_Filter::filterStatic($this->_getParam('id'), 'int');
        $blocks = $this->_getParam('block');
        $table  = new Tri_Db_Table('chat_room_blocked');
        
        foreach ($blocks as $userId) {
            $table->delete(array('user_id = ?' => $userId, 
                                 'chat_room_id = ?' => $id));
        }
        
        $this->_redirect('chat/room/live/interval/true/id/' . $id);
    }
}