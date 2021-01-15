## Multi Translate Behavior

* Author:
  <pre>
    Saleh Souzanchi <http://soozanchi.ir>
    Lars  https://github.com/func0der
  </pre>
* Version: 1.0.0
* License: MIT
* CakePHP: 2.1

### Features

- support multi language in form ( Model.fieldName.locale )
- save and edit record , is very easy
- validate all language for a field


### Changelog

* 1.0.0 first release.




### Install

Clone the MultiTranslateBehavior.php into your `app/Model/Behavior` directory:


### Setup

1-in model :
<pre><code>
class Post extends AppModel{
	public $actsAs = array(
        'MultiTranslate' => array(
            'title','body'
        )
    );
    public $validate = array(
        'title' => array(
            'rule' => 'notEmpty',
            'message' => ' your message '
        ),
        'body' => array(
            'rule' => 'notEmpty',
            'message' => 'message'
        ),    
    );
}</code></pre>

2- in controller :
<pre><code>
classPostsControllerextendsAppController{

	public function admin_index(){
		$this->Post->setLocale(array('fa','en'));
		$Results=$this->Paginator->paginate('Post');
		$this->set('Results',$Results);
	}


	public function admin_add(){
		$this->Post->setLocale(array('fa','en'));
		$this->Post->multiTranslateOptions(array('validate'=>true));

		if($this->request->is('post')){
			$this->Post->create();
			if($this->Post->save($this->request->data)){
				$this->flash(__('save..'),array('action'=>'index'));
			}
		}
	}

	public function admin_edit($id=null){
		$this->Post->setLocale(array('fa','en'));
		$this->Post->id=$id;
		if(!$this->Post->exists()){
			thrownewNotFoundException(__('InvalidPost'));
		}
		$this->Post->multiTranslateOptions(array('validate'=>true,'find'=>true));
		if($this->request->is('post')||$this->request->is('put')){
			if($this->Post->save($this->request->data)){
				$this->flash(__('save...'),array('action'=>'index'));
			}
			else{
				$this->Session->setFlash(__('cannotsave'));
			}
		} else {
			$this->request->data=$this->Post->read(null,$id);
		}
	}

}
</code></pre>
	
3- in view/forms : 
<pre><code>
    echo $this->Form->create('Post');
    echo $this->Form->input('Post.title.eng');
    echo $this->Form->input('Post.title.per');
    echo $this->Form->input('Post.title.pol');
    echo $this->Form->input('Post.body.eng');
    echo $this->Form->input('Post.body.per');
    echo $this->Form->input('Post.body.pol');
    echo $this->Form->end('save');
</code></pre>
