<?php
    class Module extends Prototype {
        protected $uber = null;
        protected $parent = null;
        
        public static $funcSetModule;
        public static $funcHoist;
        public static function enhance($prototype) {
            $prototype->setFunc('_setModule', self::$funcSetModule);
            $prototype->setFunc('_hoist', self::$funcHoist);
        }
        
        protected function moduleInit(Prototype $prototype, $name) {
            $this->setParent($prototype);
            $this->setUber($prototype);
            $prototype->setComponent($name, $this);
        }
        
        protected function getUber() {
            return $this->uber;
        }
        
        protected function setUber(Prototype $uber) {
            if(property_exists($uber, 'uber')) {
                $varUber = $uber->getUber();
                if(is_object($varUber)) {
                    $this->uber = $varUber;
                }
            } elseif(is_object($uber)) {
                $this->uber = $uber;
            }
        }
        
        protected function getParent() {
            return $this->parent;
        }
        
        protected function setParent($parent) {
            $this->parent = $parent;
        }
    }
    
    Module::$funcSetModule = function($that, $name, $module) {
        if(is_string($name)) {
            $module->moduleInit($that, $name);
        } else {
            throw new Exception('Module name must be a string.');
        }
    };
    
    Module::$funcHoist = function($that, $moduleName, $funcName) {
        $varModule = $that;
        if(is_array($moduleName)) {
            foreach($moduleName as $varName) {
                $varModule = $varModule->$varName;
            }
        } elseif(is_object($moduleName)) {
            $varModule = $moduleName;
        } elseif(is_string($moduleName)) {
            $varModule = $varModule->$moduleName;
        }
        $that->setFunc($funcName, array($varModule, $funcName));
    };
?>
