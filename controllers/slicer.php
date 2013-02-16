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
	    
	    $this->setTitle('Create Slice Engine');
	    
	    try
	    {
	      if (!User::isAdmin())
	        throw new Exception("You must be an admin to create slice engines.");
	      
	      //setup some objects
  	    $engine = new SliceEngine();
  	    $form = $this->_createSliceEngineForm($engine);
  	    $form->action = "/slicer/create";
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
  			  $file = $form->data('config_file');
  			  $config->set('config_data', file_get_contents($file['tmp_name']));
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

	  public function edit()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      if (!User::isAdmin())
	        throw new Exception("You must be an admin to create slice engines.");
	        
	      //load the data and check for errors.
        $engine = new SliceEngine($this->args('id'));
        if (!$engine->isHydrated())
          throw new Exception("That slice engine does not exist.");	

	      //setup some objects
  	    $form = $this->_createSliceEngineForm($engine);
  	    $form->action = $engine->getUrl() . "/edit";
  			$this->set('form', $form);

        $this->setTitle("Edit Slice Engine - " . $engine->getName());
			
        //check our form
  			if ($form->checkSubmitAndValidate($this->args()))
  			{
  			  //first create our engine object
  			  $engine->set('engine_name', $form->data('engine_name'));
  			  $engine->set('engine_path', $form->data('engine_path'));
  			  $engine->set('engine_description', $form->data('engine_description'));
  			  $engine->set('is_featured', $form->data('is_featured'));
  			  $engine->set('is_public', $form->data('is_public'));
  			  $engine->save();
			  
  			  //now we make it a default config object
  			  $file = $form->data('config_file');
  			  if (!empty($file))
  			  {
    			  $config = $engine->getDefaultConfig();
    			  $config->set('config_data', file_get_contents($file['tmp_name']));
    			  $config->set('edit_date', date("Y-m-d H:i:s"));
    			  $config->save();  			    
  			  }
			  
  			  //send us to view the engine.
  			  $this->forwardToUrl($engine->getUrl());
  			}
	    }
	    catch (Exception $e)
	    {
	      $this->setTitle("Edit Slice Engine - Error");
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
  		 'name' => 'is_featured',
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

      $form->add(new UploadField(array(
        'name' => 'config_file',
        'label' => 'Default Configuration',
        'help' => 'Upload the default configuration text for this engine (.ini for Slic3r)',
        'required' => !(bool)$engine->id
      )));

	    return $form;
	  }
	  
	  public function view()
	  {
	    try
	    {
	      //load the data and check for errors.
        $engine = new SliceEngine($this->args('id'));
        if (!$engine->isHydrated())
          throw new Exception("That slice engine does not exist.");
        if (!$engine->get('is_public') && !User::isAdmin())
          throw new Exception("You do not have access to view this slice engine.");
        
        //save our engine
        $this->set('engine', $engine);
        
        $this->setTitle("Slice Engine - " . $engine->getLink());

        //pull in our configs
  	    if (User::isAdmin())
  	      $this->set('configs', $engine->getAllConfigs()->getAll());
  	    else
  	      $this->set('configs', $engine->getMyConfigs()->getAll());
      }
      catch (Exception $e)
      {
        $this->setTitle("Slice Engine - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }
	 
	  public function delete()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      //load the data and check for errors.
        $engine = new SliceEngine($this->args('id'));
        if (!$engine->isHydrated())
          throw new Exception("That slice engine does not exist.");
        if (!User::isAdmin())
          throw new Exception("You do not have access to delete this slice engine.");
        
        $this->setTitle("Delete Slice Engine - " . $engine->getName());
        
        //create our form
  	    $form = new Form();
        $form->action = $engine->getUrl() . "/delete";
  			$form->add(new WarningField(array(
  				'value' => "<strong>Warning</strong>: deleting the " . $engine->getLink() . " slice engine will delete all slice configs associated with it, and likely break a ton of stuff.  Are you really sure you want to do this?"
  			)));
  			
  			$this->set('form', $form);

        //check our form
  			if ($form->checkSubmitAndValidate($this->args()))
  			{
  			  $engine->delete();
  			  $this->forwardToUrl("/slicers");
  			}
      }
      catch (Exception $e)
      {
        $this->setTitle("Delete Slice Engine - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }

	  public function config_create()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      //load the data and check for errors.
        $engine = new SliceEngine($this->args('id'));
        if (!$engine->isHydrated())
          throw new Exception("That slice engine does not exist.");
        if (!$engine->get('is_public') && !User::isAdmin())
          throw new Exception("You do not have access to view this slice engine.");

	      $this->setTitle("Create Slice Config - " . $engine->getName());
	      
	      //setup some objects
	      $config = new SliceConfig();
  	    $form = $this->_createSliceConfigUploadForm($config);
  	    $form->action = $engine->getUrl() . "/createconfig";
  			$this->set('form', $form);
			
        //check our form
  			if ($form->checkSubmitAndValidate($this->args()))
  			{
  			  //now we make it a config object
  			  $config->set('config_name', $form->data('config_name'));
  			  $file = $form->data('config_file');
  			  $config->set('config_data', file_get_contents($file['tmp_name']));
  			  $config->set('engine_id', $engine->id);
  			  $config->set('user_id', User::$me->id);
  			  $config->set('add_date', date("Y-m-d H:i:s"));
  			  $config->set('edit_date', date("Y-m-d H:i:s"));
  			  $config->save();

  			  //send us to view the new engine.
  			  $this->forwardToUrl($config->getUrl());
  			}
	    }
	    catch (Exception $e)
	    {
	      $this->setTitle("Create Slice Config - Error");
	      $this->set('megaerror', $e->getMessage());
	    }
	  }

	  public function _createSliceConfigRawForm($config)
	  {
	    $form = new Form('raw');

			$form->add(new TextField(array(
				'name' => 'config_name',
				'label' => 'Config Name',
				'help' => 'What is the name of this slicing configuration.',
				'required' => true,
				'value' => $config->get('config_name')
			)));
			
			if ($config->isHydrated())
  			$form->add(new CheckBoxField(array(
  				'name' => 'expire_slicejobs',
  				'label' => 'Expire Old Slice Jobs',
  				'help' => 'If checked, old slice jobs will be expired and never re-used.',
  				'value' => 1
  			)));

      $form->add(new TextareaField(array(
        'name' => 'default_config',
        'label' => 'Raw Configuration Text',
        'help' => 'Edit the raw configuration text for this engine.',
        'required' => true,
        'width' => '60%',
        'rows' => '20',
        'value' => $config->get('config_data')
      )));

	    return $form;
	  }
	  
	  public function _createSliceConfigUploadForm($config)
	  {
	    $form = new Form('upload');

			$form->add(new TextField(array(
				'name' => 'config_name',
				'label' => 'Config Name',
				'help' => 'What is the name of this slicing configuration.',
				'required' => true,
				'value' => $config->get('config_name')
			)));
			
			if ($config->isHydrated())
  			$form->add(new CheckBoxField(array(
  				'name' => 'expire_slicejobs',
  				'label' => 'Expire Old Slice Jobs',
  				'help' => 'If checked, old slice jobs will be expired and never re-used.',
  				'value' => 1
  			)));

      $form->add(new UploadField(array(
        'name' => 'config_file',
        'label' => 'Configuration File',
        'help' => 'The configuration file to use (.ini for Slic3r)',
        'required' => true,
      )));

	    return $form;
	  }
	  
	  public function config_edit()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      //load the data and check for errors.
        $config = new SliceConfig($this->args('id'));
        if (!$config->isHydrated())
          throw new Exception("That slice config does not exist.");	

	      if (User::$me->id != $config->get('user_id') || !User::isAdmin())
	        throw new Exception("You cannot edit this slice config.");

	      $this->setTitle("Edit Slice Config - " . $config->getName());

	      //setup some objects
  	    $rawform = $this->_createSliceConfigRawForm($config);
  	    $rawform->action = $config->getUrl() . "/edit";
  			$this->set('rawform', $rawform);

	      //setup some objects
  	    $uploadform = $this->_createSliceConfigUploadForm($config);
  	    $uploadform->action = $config->getUrl() . "/edit";
  			$this->set('uploadform', $uploadform);
  						
        //check our form
  			if ($rawform->checkSubmitAndValidate($this->args()))
  			{
  			  //edit the config object
  			  $config->set('config_name', $rawform->data('config_name'));
  			  $config->set('config_data', $rawform->data('default_config'));
  			  $config->set('edit_date', date("Y-m-d H:i:s"));
  			  $config->save();
			  
			    //are we expiring the old slice jobs?
			    if ($rawform->data('expire_slicejobs'))
			      $config->expireSliceJobs();
			  
  			  //send us to view the engine.
  			  $this->forwardToUrl($config->getUrl());
  			}
  			else if ($uploadform->checkSubmitAndValidate($this->args()))
  			{
  			  //edit the config object
  			  $config->set('config_name', $uploadform->data('config_name'));
  			  
  			  $file = $uploadform->data('config_file');
  			  $config->set('config_data', file_get_contents($file['tmp_name']));
  			  $config->set('edit_date', date("Y-m-d H:i:s"));
  			  $config->save();
			  
			    //are we expiring the old slice jobs?
			    if ($uploadform->data('expire_slicejobs'))
			      $config->expireSliceJobs();
			      
  			  //send us to view the engine.
  			  $this->forwardToUrl($config->getUrl());  			  
  			}
	    }
	    catch (Exception $e)
	    {
	      $this->setTitle("Edit Slice Config - Error");
	      $this->set('megaerror', $e->getMessage());
	    }
	  }

	  public function config_view()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      //load the data and check for errors.
        $config = new SliceConfig($this->args('id'));
        if (!$config->isHydrated())
          throw new Exception("That slice config does not exist.");
        if ($config->get('user_id') != User::$me->id && !User::isAdmin())
          throw new Exception("You do not have access to view this config.");

        //pull in all our data.
        $this->set('config', $config);
        $this->set('engine', $config->getEngine());
        $this->set('user', $config->getUser());
        $this->set('jobs', $config->getSliceJobs()->getAll());
        $this->set('bots', $config->getBots()->getAll());
        
        $this->setTitle("Slice Config - " . $config->getLink());

      }
      catch (Exception $e)
      {
        $this->setTitle("Slice Config - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }
	  
	  public function config_delete()
	  {
	    $this->assertLoggedIn();
	    
	    try
	    {
	      //load the data and check for errors.
        $config = new SliceConfig($this->args('id'));
        if (!$config->isHydrated())
          throw new Exception("That slice config does not exist.");
        if ($config->get('user_id') != User::$me->id && !User::isAdmin())
          throw new Exception("You do not have access to view this config.");

	      $this->setTitle("Delete Slice Config - " . $config->getName());
        
        //create our form
  	    $form = new Form();
        $form->action = $config->getUrl() . "/delete";
  			$form->add(new WarningField(array(
  				'value' => "<strong>Warning</strong>: deleting the " . $config->getLink() . " slice config is permanent.  Are you really sure you want to do this?"
  			)));
  			
  			$this->set('form', $form);

        //check our form
  			if ($form->checkSubmitAndValidate($this->args()))
  			{
  			  $config->delete();
  			  $this->forwardToUrl("/slicers");
  			}
      }
      catch (Exception $e)
      {
        $this->setTitle("Delete Slice Config - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }
	  
	  public function draw_jobs()
	  {
	    $this->setArg('jobs');
	  }
	  
	  public function draw_jobs_small()
	  {
	    $this->setArg('jobs');
	  }

	  public function job_view()
	  {
	    try
	    {
	      //load the data and check for errors.
        $job = new SliceJob($this->args('id'));
        if (!$job->isHydrated())
          throw new Exception("That slice job does not exist.");
        if ($job->get('user_id') != User::$me->id)
          throw new Exception("You do not have access to view this slice job.");
        
        //save our engine
        $this->set('job', $job);
        $this->set('inputfile', $job->getInputFile());
        $this->set('outputfile', $job->getOutputFile());
        $this->set('config', $job->getSliceConfig());
        $this->set('engine', $this->get('config')->getEngine());
        
        $this->setTitle("Slice Job - " . $job->getLink());
      }
      catch (Exception $e)
      {
        $this->setTitle("Slice Job - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }

	  public function job_update()
	  {
	    try
	    {
	      //load the data and check for errors.
        $job = new SliceJob($this->args('id'));
        if (!$job->isHydrated())
          throw new Exception("That slice job does not exist.");
        if ($job->get('user_id') != User::$me->id)
          throw new Exception("You do not have access to view this slice job.");
        if ($job->get('status') != 'pending')
          throw new Exception("This slice job is not in a pending state.");
          
        if ($this->args('pass'))
          $job->pass();
        if ($this->args('fail'))
          $job->fail();
          
        $this->forwardToUrl($job->getUrl());
      }
      catch (Exception $e)
      {
        $this->setTitle("Slice Job - Error");
        $this->set('megaerror', $e->getMessage());
      }
	  }
	}
?>