<?php
/**
 * iroha Board Project
 *
 * @author        Kotaro Miura
 * @copyright     2015-2016 iroha Soft, Inc. (http://irohasoft.jp)
 * @link          http://irohaboard.irohasoft.jp
 * @license       http://www.gnu.org/licenses/gpl-3.0.en.html GPL License
 */

App::uses('Controller', 'Controller');
App::import('Vendor', 'Utils');

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @package app.Controller
 * @link http://book.cakephp.org/2.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
	public $components = [
			'DebugKit.Toolbar',
			'Session',
			'Flash',
			'Auth' => [
					'loginRedirect' => [
							'controller' => 'users_courses',
							'action' => 'index'
					],
					'logoutRedirect' => [
							'controller' => 'users',
							'action' => 'login',
							'home'
					],
					'authError' => false
			]
	];
	
	//public $helpers = array('Session');
	public $helpers = [
		'Session',
		'Html' => ['className' => 'BoostCake.BoostCakeHtml'],
		'Form' => ['className' => 'BoostCake.BoostCakeForm'],
		'Paginator' => ['className' => 'BoostCake.BoostCakePaginator'],
	];
	
	public $uses = ['Setting'];
	
	public function beforeFilter()
	{
		$this->set('loginedUser', $this->readAuthUser());
		
		// 他のサイトの設定が存在する場合、設定情報及びログイン情報をクリア
		if($this->hasSession('Setting'))
		{
			if($this->readSession('Setting.app_dir')!=APP_DIR)
			{
				// セッション内の設定情報を削除
				$this->Session->delete('Setting');
				
				// 他のサイトとのログイン情報の混合を避けるため、強制ログアウト
				if($this->readAuthUser())
				{
					//$this->Cookie->delete('Auth');
					$this->redirect($this->Auth->logout());
					return;
				}
			}
		}
		
		// データベース内に格納された設定情報をセッションに格納
		if(!$this->hasSession('Setting'))
		{
			$settings = $this->Setting->getSettings();
			
			$this->writeSession('Setting.app_dir', APP_DIR);
			
			foreach ($settings as $key => $value)
			{
				$this->writeSession('Setting.'.$key, $value);
			}
		}
		
		if ($this->getParam('admin'))
		{
			// role が admin, manager, editor, teacher以外の場合、強制ログアウトする
			if($this->readAuthUser())
			{
				if(
					($this->readAuthUser('role')!='admin')&&
					($this->readAuthUser('role')!='manager')&&
					($this->readAuthUser('role')!='editor')&&
					($this->readAuthUser('role')!='teacher')
				)
				{
					if($this->Cookie)
						$this->Cookie->delete('Auth');
					
					$this->Flash->error(__('管理画面へのアクセス権限がありません'));
					$this->redirect($this->Auth->logout());
					return;
				}
			}
			
			$this->Auth->loginAction = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => true
			];
			$this->Auth->loginRedirect = [
					'controller' => 'users',
					'action' => 'index',
					'admin' => true
			];
			$this->Auth->logoutRedirect = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => true
			];
			
			// グループモデルを共通で保持する
			$this->loadModel('Group');
		}
		else
		{
			$this->Auth->loginAction = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => false
			];
			$this->Auth->loginRedirect = [
					'controller' => 'users',
					'action' => 'index',
					'admin' => false
			];
			$this->Auth->logoutRedirect = [
					'controller' => 'users',
					'action' => 'login',
					'admin' => false
			];
		}
	}

	public function beforeRender()
	{
		//header("X-XSS-Protection: 1; mode=block")
		
		// 他のドメインからのiframeへの埋め込みの禁止
		header("X-Frame-Options: SAMEORIGIN");
	}

	/**
	 * セッションの取得
	 */
	protected function readSession($key)
	{
		return $this->Session->read($key);
	}

	/**
	 * セッションの削除
	 */
	protected function deleteSession($key)
	{
		$this->Session->delete($key);
	}

	/**
	 * セッションの存在確認
	 */
	protected function hasSession($key)
	{
		return $this->Session->check($key);
	}

	/**
	 * セッションの保存
	 */
	protected function writeSession($key, $value)
	{
		$this->Session->write($key, $value);
	}

	/**
	 * ログインユーザ情報の取得
	 */
	protected function readAuthUser($key = null)
	{
		if(!$key)
			return $this->Auth->user();
		
		return $this->Auth->user($key);
	}

	/**
	 * クエリストリングの取得
	 */
	protected function getQuery($key = null)
	{
		if(!isset($this->request->query[$key]))
			return '';
		
		$val = $this->request->query[$key];
		
		if($val=='')
			return null;
		
		return $val;
	}

	/**
	 * クエリストリングの存在確認
	 */
	protected function hasQuery($key)
	{
		return isset($this->request->query[$key]);
	}


	/**
	 * リクエストパラメータの取得
	 */
	protected function getParam($key)
	{
		if(!isset($this->request->params[$key]))
			return '';
		
		$val = $this->request->params[$key];
		
		if($val=='')
			return null;
		
		return $val;
	}

	/**
	 * POSTデータの取得
	 */
	protected function getData($key = null)
	{
		$val = $this->request->data;
		
		if(!$val)
			return null;
		
		if($key)
			$val = empty($val[$key]) ? null :$val[$key];
		
		return $val;
	}

	/**
	 * POSTデータの上書き
	 */
	protected function setData($key, $value)
	{
		if($key)
		{
			$this->request->data[$key] = $value;
		}
		else
		{
			$this->request->data = $value;
		}
	}

	/**
	 * ログの保存
	 */
	function writeLog($log_type, $log_content)
	{
		$data = [
			'log_type'    => $log_type,
			'log_content' => $log_content,
			'user_id'     => $this->readAuthUser('id'),
			'user_ip'     => $_SERVER['REMOTE_ADDR'],
			'user_agent'  => $_SERVER['HTTP_USER_AGENT']
		];
		
		
		$this->loadModel('Log');
		$this->Log->create();
		$this->Log->save($data);
	}
}
