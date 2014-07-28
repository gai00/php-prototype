<?php
namespace {
    class Prototype {
        public $prototype;
        protected $parent;
        protected $content = array();
        protected $component = array();
        protected $schemaEnabled = false;
        protected $schema = array();
        protected $func = array();
        
        protected function setSchemaEnabled($enabled) {
            $this->schemaEnabled = $enabled;
        }
        
        protected function getSchemaEnabled() {
            return $this->schemaEnabled;
        }
        
        protected function getSchema($field = null) {
            if(is_array($field)) {
                $varSchema = array();
                foreach($field as $varField) {
                    $varValue = $this->getSchema($varField);
                    if(isset($varValue)) {
                        $this->schema[$varField] = $varValue;
                    }
                }
                return $varSchema;
            } elseif(is_string($field) && isset($this->schema[$field])) {
                return $this->schema[$field];
            } elseif(!isset($field)) {
                return array_merge(array(), $this->schema);
            }
        }
        
        protected function setSchema($field, $value = null) {
            if(is_array($field)) {
                foreach($field as $varField => $varValue) {
                    $this->setSchema($varField, $varValue);
                }
            } else {
                $this->schema[$field] = $value;
            }
        }
        
        protected function removeSchema($field = false) {
            if(is_array($field)) {
                foreach($field as $varField) {
                    $this->removeSchema($varField);
                }
            } elseif(is_string($field)) {
                unset($this->schema[$field]);
            } elseif($field === true) {
                $this->schema = array();
            }
        }
        
        protected function get($name = null) {
            if(is_array($name)) {
                $varContent = array();
                foreach($name as $varName) {
                    $tempValue = $this->get($varName);
                    if(isset($tempValue)) {
                        $varContent[$varName] = $tempValue;
                    }
                }
                return $varContent;
            } elseif(is_string($name) && isset($this->content[$name])) {
                return $this->content[$name];
            } elseif($name == null) {
                return array_merge(array(), $this->content);
            }
        }
        
        protected function set($name, $value = null) {
            if($this->schemaEnabled && is_string($name)) {
                if(!isset($this->schema[$name])) {
                    throw new \Exception('This variable(\'' . $name . '\') is not defined in schema.');
                }
                if(!isset($value)) {
                    // nothing
                } elseif(gettype($value) != $this->schema[$name]) {
                    throw new Exception('Variable(\'' . $name . '\'):' . gettype($value) . ' format is wrong.');
                }
            }
            
            if(is_array($name)) {
                foreach($name as $varName => $varValue) {
                    $this->set($varName, $varValue);
                }
            } elseif(is_string($name)) {
                $this->content[$name] = $value;
            } else {
                throw new Exception('Argument 1 must be a string or array pair.');
            }
        }
        
        protected function getFunc($name) {
            if(isset($this->func[$name])) {
                return $this->func[$name];
            } else {
                return null;
            }
        }
        
        protected function setFunc($name, $func) {
            $this->func[$name] = $func;
        }
        
        protected function removeFunc($name) {
            $this->func[$name] = null;
            unset($this->func[$name]);
        }
        
        protected function getCall($name) {
            $varCall = false;
            $varThat = false;
            
            switch(true) {
                case isset($this->func[$name]):
                    $varCall = $this->func[$name];
                    break;
                case method_exists($this, $name):
                    $varCall = array($this, $name);
                    break;
                case isset($this->func['_' . $name]):
                    $varCall = $this->func['_' . $name];
                    $varThat = true;
                    break;
                case method_exists($this, '_' . $name):
                    $varCall = array($this, '_' . $name);
                    $varThat = true;
                    break;
                case isset($this->prototype):
                    $varCall = array($this->prototype, $name);
                    if(method_exists($this->prototype, 'getCall')) {
                        return $this->prototype->getCall($name);
                    }
            }
            return array($varCall, $varThat);
        }
        
        public function __call($name, $args) {
            $varCalls = $this->getCall($name);
            if($varCalls[0] != false) {
                if($varCalls[1]) {
                    array_unshift($args, $this);
                }
                $varCall = $varCalls[0];
            } else {
                throw new Exception('Cannot call function: ' . $name);
            }
            return call_user_func_array($varCall, $args);
        }
        
        public function __get($name) {
            return $this->getComponent($name);
        }
        
        protected function getComponent($name = null) {
            if(is_string($name) && isset($this->component[$name])) {
                return $this->component[$name];
            } elseif($name === true) {
                return $this->component;
            } else {
                return null;
            }
        }
        
        protected function setComponent($name, $component) {
            if(is_array($name)) {
                foreach($name as $varName => $varComponent) {
                    $this->setComponent($varName, $varComponent);
                }
            } elseif(is_string($name) && isset($component)) {
                $this->component[$name] = $component;
            } else {
                throw new Exception('Argument 1 must be a string or array pair.');
            }
        }
        
        protected function removeComponent($name) {
            if(is_array($name)) {
                foreach($name as $varName) {
                    $this->removeComponent($varName);
                }
            } elseif(is_string($name)) {
                $this->component[$name] = null;
                unset($this->component[$name]);
            } else {
                throw new Exception('Argument 1 must be a string or array.');
            }
        }
        
        // move from module
        protected function moduleInit(Prototype $prototype, $name) {
            $this->setParent($prototype);
            $prototype->setComponent($name, $this);
        }
        
        protected function getParent() {
            return $this->parent;
        }
        
        protected function setParent($parent) {
            $this->parent = $parent;
        }
        
        protected function setModule($name, $module = null) {
            if(is_array($name)) {
                foreach($name as $varName => $varMod) {
                    $varMod->moduleInit($this, $varName);
                }
            } elseif(is_string($name)) {
                $module->moduleInit($this, $name);
            } else {
                throw new Exception('Argument 1 must be a string or array pair.');
            }
        }
        
        protected function listModule() {
            return array_keys($this->getComponent(true));
        }
        
        protected function hoist($moduleName, $funcName) {
            $varModule = $this;
            if(is_array($moduleName)) {
                foreach($moduleName as $varName) {
                    $varModule = $varModule->$varName;
                }
            } elseif(is_object($moduleName)) {
                $varModule = $moduleName;
            } elseif(is_string($moduleName)) {
                $varModule = $varModule->$moduleName;
            }
            $this->setFunc($funcName, array($varModule, $funcName));
        }
    }
}
?>
