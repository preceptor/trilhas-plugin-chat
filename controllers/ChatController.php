<?php
class Chat_ChatController extends Controller 
{
	//public $uses = array( "Chat" , "Logged" , "ChatMessage" , "ChatRoomMessage" , "Person" , "File" );
	
	public function indexAction()
	{
		$user = new Zend_Session_NameSpace( 'user' );
		
		$messages['chat'] = $this->ChatMessage->fetchMessage( false );
		$messages['room'] = $this->ChatRoomMessage->fetchMessage( false );

		$this->view->rs  	  = $this->Chat->listUsers();
		$this->view->messages = addslashes( Zend_Json::encode( $messages ) );
		
		$this->view->user	  = $user;

		$data['person_id'] = $user->person_id;
		$data['group_id']  = $user->group_id;
		
		//$this->Logged->save( $data );
		
		$this->render( null , "ajax" );
	}
	
	public function findAction()
	{
		$user = new Zend_Session_NameSpace( 'user' );
		
		$messages['chat'] = $this->ChatMessage->fetchMessage( true );
		$messages['room'] = $this->ChatRoomMessage->fetchMessage( true );

		$this->view->messages = addslashes( Zend_Json::encode( $messages ) );
		$this->view->user	  = $user;
		
		$this->render( null , "ajax" );
	}
	
	public function inputAction()
	{
		$id = Zend_Filter::filterStatic( $this->_getParam( "id" ) , "int" );
		
		$this->view->rs = $this->Person->find( $id )->current();
		  
		$this->render( null , "ajax" );
	}
	
	public function saveAction()
	{
		$user = new Zend_Session_NameSpace( 'user' );
		
		$data['ds'] = $_POST['ds'];
		$id = $this->Chat->save( $data );
		
		if ( $id ) 
		{
			$data = null;
			$data['chat_id'] 			= $id;
			$data['person_sender_id'] 	= $user->person_id;
			$data['person_receiver_id'] = $_POST['person_id'];
			
			$this->ChatMessage->save( $data );
			
			$data = null;
			$data['person_id'] = $user->person_id;
			$data['group_id']  = $user->group_id;
		
			$this->Logged->save( $data );
			
			$this->_redirect( "/chat/chat/find" );
		}
		else
		{
			$this->postSave( false , $input );
		}
	}
	
	public function messageAction()
	{
		$user = new Zend_Session_NameSpace( 'user' );
		$this->view->rs  	  = $this->Chat->listUsers();
		
		$this->render( "index" , "ajax" );
	}
}