<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Saleh Souzanchi (http://cakephp.ir) <saleh.souzanchi@gmail.com>
 * @link          http://ritaco.net RitaCo. Web Development
 * @package       Cake.Model.Behavior
 * @since         CakePHP(tm) v 2.1.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('I18n', 'I18n');
App::uses('I18nModel', 'Model');
App::uses('TranslateBehavior', 'Model/Behavior');
/**
 * Translate behavior
 *
 * @package       Cake.Model.Behavior
 * @link http://book.cakephp.org/2.0/en/core-libraries/behaviors/translate.html
 */
class MultiTranslateBehavior extends TranslateBehavior {


    protected $_options = array(
        'validate' => false,
        'find' => false
    );  

    public $multiOptions = array();
    
    
    
    
    /**
     * MultiTranslateBehavior::setup()
     * 
     * @param mixed $Model
     * @param mixed $config
     * @return void
     */
    public function setup(Model $Model, $config = array()) {
        parent::setup($Model,$config);
        
        if ( !isset( $this->multiOptions[$Model->alias] ) )
            $this->multiOptions[$Model->alias] = $this->_options;  
    }  


    /**
     * MultiTranslateBehavior::enableMultiValidate()
     * 
     * @param mixed $model
     * @return void
     */
    public function multiTranslateOptions(Model $model,$options ) {
      
        if ( !is_array( $options ) )
            return false;
            
        foreach( $this->multiOptions[$model->alias] as $key => $value ) {
            if ( isset( $options[$key ] ) &&  is_bool( $options[ $key ] )  ) {
                $this->multiOptions[$model->alias][$key] = $options[ $key ];
            }    
        }
       
      
    }




/**
 * afterFind Callback
 *
 * @param Model $model Model find was run on
 * @param array $results Array of model results.
 * @param boolean $primary Did the find originate on $model.
 * @return array Modified results
 */
	public function afterFind(Model $model, $results, $primary = false) {
		$model->virtualFields = $this->runtime[$model->alias]['virtualFields'];
		$this->runtime[$model->alias]['virtualFields'] = $this->runtime[$model->alias]['fields'] = array();
		$locale = $this->_getLocale($model);

		if (empty($locale) || empty($results) || empty($this->runtime[$model->alias]['beforeFind'])) {
			return $results;
		}
        
		$beforeFind = $this->runtime[$model->alias]['beforeFind'];
        
		foreach ($results as $key => &$row) {
			$results[$key][$model->alias]['locale'] = (is_array($locale)) ? current($locale) : $locale;
			foreach ($beforeFind as $_f => $field) {
				$aliasField = is_numeric($_f) ? $field : $_f;
				if (is_array($locale)) {
					foreach ($locale as $_locale) {
					   if ( $this->multiOptions[$model->alias]['find']) {
                           if (!isset($row[$model->alias][$aliasField][$_locale]) && !empty($row[$model->alias]['i18n_' . $field . '_' . $_locale])) {
							     $row[$model->alias][$aliasField][$_locale] = $row[$model->alias]['i18n_' . $field . '_' . $_locale];
							     $row[$model->alias]['locale'] = $_locale;
						   }
  
                       } else {
						  if (!isset($row[$model->alias][$aliasField]) && !empty($row[$model->alias]['i18n_' . $field . '_' . $_locale])) {
    							$row[$model->alias][$aliasField] = $row[$model->alias]['i18n_' . $field . '_' . $_locale];
	       						$row[$model->alias]['locale'] = $_locale;
            		      }
                       }    
						unset($row[$model->alias]['i18n_' . $field . '_' . $_locale]);
      
					}

					if (!isset($row[$model->alias][$aliasField])) {
						$row[$model->alias][$aliasField] = '';
					}
				} else {
					$value = '';
					if (!empty($row[$model->alias]['i18n_' . $field])) {
						$value = $row[$model->alias]['i18n_' . $field];
					}
					$row[$model->alias][$aliasField] = $value;
					unset($row[$model->alias]['i18n_' . $field]);
				}
			}
		}
		return $results;
	}

/**
 * beforeValidate Callback
 *
 * @param Model $model Model invalidFields was called on.
 * @return boolean
 */
	public function beforeValidate(Model $model, $options = array()) {
		$locale = $this->_getLocale($model);
		if (empty($locale)) {
			return true;
		}
        
        if ( $this->multiOptions[$model->alias]['validate'] ) {
            $valid = $this->_multiValidate($model);
            if ( !$valid )
                return false;
            
        }
        
		$fields = array_merge($this->settings[$model->alias], $this->runtime[$model->alias]['fields']);
		$tempData = array();

		foreach ($fields as $key => $value) {
			$field = (is_numeric($key)) ? $value : $key;

			if (isset($model->data[$model->alias][$field])) {
				$tempData[$field] = $model->data[$model->alias][$field];
				if (is_array($model->data[$model->alias][$field])) {
					if (is_string($locale) && !empty($model->data[$model->alias][$field][$locale])) {
						$model->data[$model->alias][$field] = $model->data[$model->alias][$field][$locale];
					} else {
						$values = array_values($model->data[$model->alias][$field]);
						$model->data[$model->alias][$field] = $values[0];
					}
				}
			}
		}
		$this->runtime[$model->alias]['beforeSave'] = $tempData;
		return true;
	}


/**
 * Set locale's for model
 *
 * @param string|array $local  set locale's' for model.
 * @return mixed string or false
 */

    public function setLocale(Model $model,$locale = null) {
        if ( !$locale ) {
            $locale = Configure::read('Config.language'); 
        } 
        $I18n = I18n::getInstance();
        if ( is_array( $locale ) ){
            foreach($locale as $key =>  $_locale){
                 $I18n->l10n->get($_locale);
              $locale[$key] = $I18n->l10n->locale;
            }
            return $model->locale = $locale;

        } else {
            $I18n->l10n->get($locale);
            return $model->locale = $I18n->l10n->locale;
        }
        
    }




/**
 * beforeValidate Callback
 *
 * In here we validate all translated field by it self to make sure we have valid input everywhere
 *
 * @param Model $model Model invalidFields was called on.
 * @return boolean
 */
	public function _multiValidate(&$model) {
		$cModel = clone $model;
		$valid = true;
		$errorWhileValidation = false;
 	    $fields = array_merge($this->settings[$model->alias], $this->runtime[$model->alias]['fields']);
		if(isset($model->data[$model->alias])){
			$data = $model->data[$model->alias];

			foreach($fields as $field){

				if(isset($data[$field])){
					$values = $data[$field];
					foreach($values as $locale => $value){
						$cModel->data = array($field => $value);
						$valid = $cModel->validates(
							array(
								'fieldList' => array($field)
							)
						);
						if(!$valid){
							$model->validationErrors[$field][$locale] = $cModel->validationErrors[$field];
							unset($cModel->validationErrors[$field]);
							$errorWhileValidation = ($errorWhileValidation === false ? true : false);
						}
					}
				}
			}
		}

		$valid = !$errorWhileValidation;
		return $valid;
	}



}

