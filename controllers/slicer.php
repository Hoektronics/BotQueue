<?
  /*
    This file is part of BotQueue.

    BotQueue is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    BotQueue is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with BotQueue.  If not, see <http://www.gnu.org/licenses/>.
  */

	class SlicerController extends Controller
	{
	  public function home()
	  {
	    $this->setTitle("Slicer Engines");
	    
	    if (User::isAdmin())
	      $this->set('slicers', SliceEngine::getAllEngines()->getAll());
	    else
	      $this->set('slicers', SliceEngine::getPublicEngines()->getAll());
	  }
	  
	  public function create()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      if (!User::isAdmin())
	        throw new Exception("You must be an admin to create slice engines.");
	      
	      //setup some objects
  	    $engine = new SliceEngine();
  	    $form = $this->_createSliceEngineForm($engine);
  	    $form->action = "/slicers/create";
  			$this->set('form', $form);
			
        //check our form
  			if ($form->checkSubmitAndValidate($this->args()))
  			{
  			  //first create our engine object
  			  $engine->set('engine_name', $form->data('engine_name'));
  			  $engine->set('engine_path', $form->data('engine_path'));
  			  $engine->set('engine_description', $form->data('engine_description'));
  			  $engine->set('is_featured', $form->data('is_featured'));
  			  $engine->set('is_public', $form->data('is_public'));
  			  $engine->set('add_date', date("Y-m-d H:i:s"));
  			  $engine->save();
			  
  			  //now we make it a default config object
  			  $config = new SliceConfig();
  			  $config->set('config_name', 'Default');
  			  $config->set('config_data', $form->data('default_config'));
  			  $config->set('engine_id', $engine->id);
  			  $config->set('user_id', User::$me->id);
  			  $config->set('add_date', date("Y-m-d H:i:s"));
  			  $config->set('edit_date', date("Y-m-d H:i:s"));
  			  $config->save();
			  
  			  //now record our default id
  			  $engine->set('default_config_id', $config->id);
  			  $engine->save();
			  
  			  //send us to view the new engine.
  			  $this->forwardToUrl($engine->getUrl());
  			}
	    }
	    catch (Exception $e)
	    {
	      $this->set('megaerror', $e->getMessage());
	    }
	  }
	  
	  public function _createSliceEngineForm($engine)
	  {
	    $form = new Form();
	    $config = $engine->getDefaultConfig();

			$form->add(new TextField(array(
				'name' => 'engine_name',
				'label' => 'Engine Name',
				'help' => 'What is the name of this slicing engine.  Include the version number.  Eg: MySlice v3.2.1',
				'required' => true,
				'value' => $engine->get('engine_name')
			)));

  		$form->add(new TextField(array(
  			'name' => 'engine_path',
  			'label' => 'Engine Path',
  			'help' => 'What is the path to the slicing engine from the bumblebee/slicers directory?  Eg: myslice-3.2.1',
  			'required' => true,
  			'value' => $engine->get('engine_path')
  		)));

      $form->add(new CheckboxField(array(
  		 'name' => 'is_public',
  		 'label' => 'Is this slice engine public and available for use?',
  		 'help' => 'Check this box when you are ready to roll out a new slice engine.',
  		 'value' => $engine->get('is_public')
  		)));
		
  		$form->add(new CheckboxField(array(
  		 'name' => 'bot_error',
  		 'label' => 'Is this slice engine featured?',
  		 'help' => 'Featured slice engines will be more prominently featured, and will make it easier to use the latest and greatest slicing tech.',
  		 'value' => $engine->get('is_featured')
  		)));

      $form->add(new TextareaField(array(
        'name' => 'engine_description',
        'label' => 'Engine Description',
        'help' => 'Enter a description for this engine that will help people understand it.',
        'required' => true,
        'width' => '60%',
        'rows' => '4',
        'value' => $engine->get('engine_description')
      )));

      $form->add(new TextareaField(array(
        'name' => 'default_config',
        'label' => 'Default Configuration',
        'help' => 'Enter the default configuration text for this engine.',
        'required' => true,
        'width' => '60%',
        'rows' => '8',
        'value' => $config->get('config_data')
      )));
	    
	    return $form;
	  }
	}
?>